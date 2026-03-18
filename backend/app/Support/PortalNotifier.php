<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkRequest;
use App\Notifications\PortalEventNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class PortalNotifier
{
    public static function portalHomeUrl(): string
    {
        return rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/').'/portal';
    }

    public static function requestUrl(WorkRequest $workRequest): string
    {
        return self::portalHomeUrl().'?request='.$workRequest->id;
    }

    public static function recipientsFor(
        WorkRequest $workRequest,
        ?User $actor = null,
        bool $includeStaff = true,
        bool $includeClient = true
    ): Collection {
        $workRequest->loadMissing(['firm.users', 'client.user']);

        $recipients = collect();

        if ($includeStaff) {
            $recipients = $recipients->merge(
                $workRequest->firm->users->where('role', UserRole::Staff)
            );
        }

        if ($includeClient && $workRequest->client?->user) {
            $recipients->push($workRequest->client->user);
        }

        if ($actor) {
            $recipients = $recipients->reject(fn (User $user) => $user->id === $actor->id);
        }

        return $recipients
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();
    }

    public static function send(
        iterable $recipients,
        string $title,
        string $message,
        ?string $actionUrl = null,
        string $severity = 'info',
    ): void {
        $collection = collect($recipients)->filter();

        if ($collection->isEmpty()) {
            return;
        }

        Notification::send(
            $collection,
            new PortalEventNotification($title, $message, $actionUrl, $severity)
        );
    }
}
