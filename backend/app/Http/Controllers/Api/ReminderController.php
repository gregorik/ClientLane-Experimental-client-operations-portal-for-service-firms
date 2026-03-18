<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendReminderRequest;
use App\Models\User;
use App\Models\WorkRequest;
use App\Support\ActivityLogger;
use App\Support\PortalNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReminderController extends Controller
{
    public function send(SendReminderRequest $request, WorkRequest $workRequest): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->canAccessRequest($workRequest), 403);
        abort_unless($actor->isStaff(), 403);

        $workRequest->loadMissing('client.user');

        if (! $workRequest->client?->user) {
            throw ValidationException::withMessages([
                'request' => ['This client does not have portal access yet.'],
            ]);
        }

        $message = $request->validated()['message']
            ?? "Please review \"{$workRequest->title}\" and upload any outstanding files.";

        $workRequest->update([
            'last_reminded_at' => now(),
        ]);

        ActivityLogger::forRequest(
            $workRequest,
            $actor,
            'reminder.sent',
            'Sent a reminder to the client.',
            ['message' => $message]
        );

        PortalNotifier::send(
            [$workRequest->client->user],
            'Client reminder',
            $message,
            PortalNotifier::requestUrl($workRequest),
            'warning'
        );

        return response()->json([
            'status' => 'ok',
        ]);
    }
}
