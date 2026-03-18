<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\PortalData;
use App\Support\PortalNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->isStaff(), 403);

        $clients = Client::query()
            ->where('firm_id', $user->firm_id)
            ->with('user')
            ->withCount([
                'workRequests as total_requests_count',
                'workRequests as open_requests_count' => function ($query): void {
                    $query->whereNotIn('status', [
                        RequestStatus::Completed->value,
                        RequestStatus::Archived->value,
                    ]);
                },
            ])
            ->orderBy('company_name')
            ->get();

        return response()->json([
            'data' => $clients->map(fn (Client $client) => PortalData::client($client))->all(),
        ]);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user()->load('firm');
        $payload = $request->validated();

        $client = Client::create([
            'firm_id' => $actor->firm_id,
            'company_name' => $payload['company_name'],
            'primary_contact_name' => $payload['primary_contact_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'notes' => $payload['notes'] ?? null,
            'is_active' => $payload['is_active'] ?? true,
        ]);

        if ($payload['create_portal_user'] ?? false) {
            if (empty($payload['password'])) {
                throw ValidationException::withMessages([
                    'password' => ['A password is required when creating portal access.'],
                ]);
            }

            if (User::query()->where('email', $payload['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['This email is already used by another portal user.'],
                ]);
            }

            $portalUser = User::create([
                'firm_id' => $actor->firm_id,
                'client_id' => $client->id,
                'name' => $payload['portal_user_name'] ?: $payload['primary_contact_name'],
                'email' => $payload['email'],
                'title' => $payload['portal_user_title'] ?? 'Client contact',
                'role' => UserRole::Client->value,
                'password' => $payload['password'],
                'email_verified_at' => now(),
            ]);

            PortalNotifier::send(
                [$portalUser],
                'Portal access created',
                "Your {$actor->firm->name} client portal is ready.",
                PortalNotifier::portalHomeUrl(),
                'success'
            );
        }

        ActivityLogger::forFirm(
            $actor->firm,
            $actor,
            'client.created',
            "Added {$client->company_name} to the portal.",
            ['client_id' => $client->id]
        );

        return response()->json([
            'data' => PortalData::client($client->load('user')),
        ], 201);
    }

    public function show(Request $request, Client $client): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canAccessClient($client), 403);

        $client->load([
            'user',
            'workRequests' => function ($query) use ($user): void {
                $query->visibleTo($user)
                    ->with(['submittedBy', 'assignedTo'])
                    ->latest();
            },
        ]);

        return response()->json([
            'data' => PortalData::client($client),
            'requests' => $client->workRequests->map(
                fn ($item) => PortalData::workRequest($item)
            )->all(),
        ]);
    }
}
