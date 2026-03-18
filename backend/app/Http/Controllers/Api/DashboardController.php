<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkRequest;
use App\Support\PortalData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['firm', 'client']);

        $baseRequestQuery = WorkRequest::query()->visibleTo($user);

        $recentRequests = (clone $baseRequestQuery)
            ->with(['client.user', 'submittedBy', 'assignedTo'])
            ->withCount($this->requestCountsFor($user))
            ->latest()
            ->limit(6)
            ->get();

        $activityQuery = ActivityLog::query()
            ->where('firm_id', $user->firm_id)
            ->with('user');

        if ($user->isClient()) {
            $activityQuery->whereHas('workRequest', function ($query) use ($user): void {
                $query->where('client_id', $user->client_id);
            });
        }

        $recentActivity = $activityQuery
            ->latest()
            ->limit(8)
            ->get();

        $notifications = $user->notifications()
            ->latest()
            ->limit(8)
            ->get();

        $requestsByStatus = collect(RequestStatus::cases())->mapWithKeys(
            fn (RequestStatus $status) => [
                $status->value => (clone $baseRequestQuery)->where('status', $status->value)->count(),
            ]
        );

        $stats = [
            'open_requests' => (clone $baseRequestQuery)
                ->whereNotIn('status', [RequestStatus::Completed->value, RequestStatus::Archived->value])
                ->count(),
            'overdue_requests' => (clone $baseRequestQuery)
                ->whereNotIn('status', [RequestStatus::Completed->value, RequestStatus::Archived->value])
                ->whereNotNull('due_at')
                ->where('due_at', '<', now())
                ->count(),
            'due_this_week' => (clone $baseRequestQuery)
                ->whereNotNull('due_at')
                ->whereBetween('due_at', [now(), now()->copy()->addDays(7)])
                ->count(),
            'waiting_on_client' => (clone $baseRequestQuery)
                ->where('status', RequestStatus::WaitingOnClient->value)
                ->count(),
            'waiting_on_staff' => (clone $baseRequestQuery)
                ->where('status', RequestStatus::WaitingOnStaff->value)
                ->count(),
        ];

        if ($user->isStaff()) {
            $stats['active_clients'] = Client::query()
                ->where('firm_id', $user->firm_id)
                ->where('is_active', true)
                ->count();
            $stats['team_members'] = User::query()
                ->where('firm_id', $user->firm_id)
                ->where('role', UserRole::Staff->value)
                ->count();
        } else {
            $stats['completed_this_month'] = (clone $baseRequestQuery)
                ->where('status', RequestStatus::Completed->value)
                ->whereBetween('completed_at', [now()->copy()->startOfMonth(), now()])
                ->count();
        }

        return response()->json([
            'firm' => PortalData::firm($user->firm),
            'user' => PortalData::user($user),
            'stats' => $stats,
            'requests_by_status' => $requestsByStatus,
            'recent_requests' => $recentRequests->map(fn (WorkRequest $item) => PortalData::workRequest($item))->all(),
            'recent_activity' => $recentActivity->map(fn (ActivityLog $item) => PortalData::activity($item))->all(),
            'notifications' => $notifications->map(fn ($item) => PortalData::notification($item))->all(),
            'unread_notifications_count' => $user->unreadNotifications()->count(),
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
}
