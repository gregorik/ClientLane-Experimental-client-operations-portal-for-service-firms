<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestCommentRequest;
use App\Models\RequestComment;
use App\Models\User;
use App\Models\WorkRequest;
use App\Support\ActivityLogger;
use App\Support\PortalData;
use App\Support\PortalNotifier;
use Illuminate\Http\JsonResponse;

class RequestCommentController extends Controller
{
    public function store(StoreRequestCommentRequest $request, WorkRequest $workRequest): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->canAccessRequest($workRequest), 403);

        $payload = $request->validated();
        $isInternal = $actor->isStaff() ? ($payload['is_internal'] ?? false) : false;

        $comment = RequestComment::create([
            'work_request_id' => $workRequest->id,
            'user_id' => $actor->id,
            'body' => $payload['body'],
            'is_internal' => $isInternal,
        ]);

        if ($actor->isClient() && $workRequest->status === RequestStatus::WaitingOnClient) {
            $workRequest->update(['status' => RequestStatus::WaitingOnStaff->value]);
        }

        ActivityLogger::forRequest(
            $workRequest,
            $actor,
            'comment.created',
            $isInternal ? 'Added an internal note.' : 'Added a reply.',
            ['comment_id' => $comment->id, 'is_internal' => $isInternal]
        );

        PortalNotifier::send(
            PortalNotifier::recipientsFor(
                $workRequest,
                $actor,
                includeStaff: true,
                includeClient: ! $isInternal
            ),
            $isInternal ? 'Internal note added' : 'New request comment',
            "{$actor->name} commented on \"{$workRequest->title}\".",
            PortalNotifier::requestUrl($workRequest),
            'info'
        );

        return response()->json([
            'data' => PortalData::comment($comment->load('user')),
        ], 201);
    }
}
