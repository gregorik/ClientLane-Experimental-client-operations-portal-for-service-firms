<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkRequestRequest;
use App\Http\Requests\UpdateWorkRequestRequest;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkRequest;
use App\Support\ActivityLogger;
use App\Support\PortalData;
use App\Support\PortalNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = WorkRequest::query()
            ->visibleTo($user)
            ->with(['client.user', 'submittedBy', 'assignedTo'])
            ->withCount($this->requestCountsFor($user));

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($priority = $request->string('priority')->toString()) {
            $query->where('priority', $priority);
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('request_type', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQuery) use ($search): void {
                        $clientQuery->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $requests = $query
            ->orderByRaw('case when due_at is null then 1 else 0 end')
            ->orderBy('due_at')
            ->latest('updated_at')
            ->get();

        return response()->json([
            'data' => $requests->map(fn (WorkRequest $item) => PortalData::workRequest($item))->all(),
        ]);
    }

    public function show(Request $request, WorkRequest $workRequest): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($user->canAccessRequest($workRequest), 403);

        $workRequest->load($this->threadRelationsFor($user));

        return response()->json([
            'data' => PortalData::workRequest($workRequest, true),
        ]);
    }

    public function store(StoreWorkRequestRequest $request): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user()->load(['firm', 'client']);
        $payload = $request->validated();

        $client = $this->resolveClient($actor, $payload['client_id'] ?? null);
        $assignedUser = $this->resolveAssignee($actor, $payload['assigned_to_user_id'] ?? null);

        $workRequest = WorkRequest::create([
            'firm_id' => $actor->firm_id,
            'client_id' => $client->id,
            'submitted_by_user_id' => $actor->id,
            'assigned_to_user_id' => $assignedUser?->id,
            'title' => $payload['title'],
            'request_type' => $payload['request_type'],
            'summary' => $payload['summary'],
            'priority' => $payload['priority'] ?? RequestPriority::Normal->value,
            'status' => $actor->isClient()
                ? RequestStatus::New->value
                : RequestStatus::WaitingOnStaff->value,
            'due_at' => $payload['due_at'] ?? null,
        ]);

        $workRequest->load(['firm.users', 'client.user']);

        ActivityLogger::forRequest(
            $workRequest,
            $actor,
            'request.created',
            "Created request \"{$workRequest->title}\".",
            ['status' => $workRequest->status->value]
        );

        PortalNotifier::send(
            PortalNotifier::recipientsFor(
                $workRequest,
                $actor,
                includeStaff: $actor->isClient(),
                includeClient: $actor->isStaff()
            ),
            $actor->isClient() ? 'New client request' : 'New staff request',
            "{$actor->name} created \"{$workRequest->title}\".",
            PortalNotifier::requestUrl($workRequest),
            'info'
        );

        $workRequest->load($this->threadRelationsFor($actor));

        return response()->json([
            'data' => PortalData::workRequest($workRequest, true),
        ], 201);
    }

    public function update(UpdateWorkRequestRequest $request, WorkRequest $workRequest): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->canAccessRequest($workRequest), 403);
        abort_unless($actor->isStaff(), 403);

        $payload = $request->validated();

        if (array_key_exists('assigned_to_user_id', $payload)) {
            $payload['assigned_to_user_id'] = $this->resolveAssignee($actor, $payload['assigned_to_user_id'])?->id;
        }

        $oldStatus = $workRequest->status;

        $workRequest->fill($payload);

        if (isset($payload['status']) && $payload['status'] === RequestStatus::Completed->value) {
            $workRequest->completed_at = now();
        } elseif (isset($payload['status']) && $oldStatus === RequestStatus::Completed) {
            $workRequest->completed_at = null;
        }

        $workRequest->save();
        $workRequest->load(['firm.users', 'client.user', 'assignedTo']);

        $changes = [];

        if (isset($payload['status'])) {
            $changes[] = "status to {$workRequest->status->label()}";
        }

        if (array_key_exists('assigned_to_user_id', $payload) && $workRequest->assignedTo) {
            $changes[] = "assignee to {$workRequest->assignedTo->name}";
        }

        if (array_key_exists('due_at', $payload)) {
            $changes[] = 'due date';
        }

        ActivityLogger::forRequest(
            $workRequest,
            $actor,
            'request.updated',
            'Updated '.($changes ? implode(', ', $changes) : 'request details').'.',
            ['changes' => $payload]
        );

        PortalNotifier::send(
            PortalNotifier::recipientsFor($workRequest, $actor, true, true),
            'Request updated',
            "{$actor->name} updated \"{$workRequest->title}\".",
            PortalNotifier::requestUrl($workRequest),
            'info'
        );

        $workRequest->load($this->threadRelationsFor($actor));

        return response()->json([
            'data' => PortalData::workRequest($workRequest, true),
        ]);
    }

    private function requestCountsFor(User $user): array
    {
        return [
            'comments as comments_count' => function ($query) use ($user): void {
                if ($user->isClient()) {
                    $query->where('is_internal', false);
                }
            },
            'files as files_count',
        ];
    }

    private function threadRelationsFor(User $user): array
    {
        return [
            'client.user',
            'submittedBy',
            'assignedTo',
            'files.user',
            'activities.user',
            'comments' => function ($query) use ($user): void {
                if ($user->isClient()) {
                    $query->where('is_internal', false);
                }

                $query->with('user')->latest();
            },
        ];
    }

    private function resolveClient(User $actor, ?int $clientId): Client
    {
        if ($actor->isClient()) {
            return $actor->client;
        }

        $client = Client::query()
            ->where('firm_id', $actor->firm_id)
            ->find($clientId);

        if (! $client) {
            throw ValidationException::withMessages([
                'client_id' => ['Select a valid client in this workspace.'],
            ]);
        }

        return $client;
    }

    private function resolveAssignee(User $actor, mixed $assigneeId): ?User
    {
        if (! $assigneeId) {
            return null;
        }

        $assignee = User::query()
            ->where('firm_id', $actor->firm_id)
            ->where('role', 'staff')
            ->find($assigneeId);

        if (! $assignee) {
            throw ValidationException::withMessages([
                'assigned_to_user_id' => ['Select a valid staff assignee in this workspace.'],
            ]);
        }

        return $assignee;
    }
}
