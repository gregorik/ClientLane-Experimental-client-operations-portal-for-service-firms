<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\CannedReply;
use App\Models\Client;
use App\Models\Firm;
use App\Models\RequestComment;
use App\Models\RequestFile;
use App\Models\User;
use App\Models\WorkRequest;
use Illuminate\Notifications\DatabaseNotification;

class PortalData
{
    public static function firm(Firm $firm): array
    {
        return [
            'id' => $firm->id,
            'name' => $firm->name,
            'slug' => $firm->slug,
            'niche' => $firm->niche,
            'portal_tagline' => $firm->portal_tagline,
            'primary_color' => $firm->primary_color,
        ];
    }

    public static function user(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'title' => $user->title,
            'role' => $user->role->value,
            'role_label' => $user->role->label(),
            'firm_id' => $user->firm_id,
            'client_id' => $user->client_id,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ];
    }

    public static function client(Client $client): array
    {
        return [
            'id' => $client->id,
            'firm_id' => $client->firm_id,
            'company_name' => $client->company_name,
            'primary_contact_name' => $client->primary_contact_name,
            'email' => $client->email,
            'phone' => $client->phone,
            'notes' => $client->notes,
            'is_active' => $client->is_active,
            'portal_user' => $client->relationLoaded('user') && $client->user
                ? self::user($client->user)
                : null,
            'open_requests_count' => $client->open_requests_count ?? null,
            'total_requests_count' => $client->total_requests_count ?? null,
            'created_at' => $client->created_at?->toIso8601String(),
        ];
    }

    public static function workRequest(WorkRequest $workRequest, bool $includeThread = false): array
    {
        return [
            'id' => $workRequest->id,
            'firm_id' => $workRequest->firm_id,
            'client_id' => $workRequest->client_id,
            'title' => $workRequest->title,
            'request_type' => $workRequest->request_type,
            'summary' => $workRequest->summary,
            'status' => $workRequest->status->value,
            'status_label' => $workRequest->status->label(),
            'priority' => $workRequest->priority->value,
            'priority_label' => $workRequest->priority->label(),
            'due_at' => $workRequest->due_at?->toIso8601String(),
            'last_reminded_at' => $workRequest->last_reminded_at?->toIso8601String(),
            'completed_at' => $workRequest->completed_at?->toIso8601String(),
            'created_at' => $workRequest->created_at?->toIso8601String(),
            'updated_at' => $workRequest->updated_at?->toIso8601String(),
            'client' => $workRequest->relationLoaded('client') && $workRequest->client
                ? self::client($workRequest->client)
                : null,
            'submitted_by' => $workRequest->relationLoaded('submittedBy') && $workRequest->submittedBy
                ? self::user($workRequest->submittedBy)
                : null,
            'assigned_to' => $workRequest->relationLoaded('assignedTo') && $workRequest->assignedTo
                ? self::user($workRequest->assignedTo)
                : null,
            'comments_count' => $workRequest->comments_count ?? null,
            'files_count' => $workRequest->files_count ?? null,
            'comments' => $includeThread && $workRequest->relationLoaded('comments')
                ? $workRequest->comments->map(fn (RequestComment $comment) => self::comment($comment))->all()
                : [],
            'files' => $includeThread && $workRequest->relationLoaded('files')
                ? $workRequest->files->map(fn (RequestFile $file) => self::file($file))->all()
                : [],
            'activities' => $includeThread && $workRequest->relationLoaded('activities')
                ? $workRequest->activities->map(fn (ActivityLog $activity) => self::activity($activity))->all()
                : [],
        ];
    }

    public static function comment(RequestComment $comment): array
    {
        return [
            'id' => $comment->id,
            'body' => $comment->body,
            'is_internal' => $comment->is_internal,
            'created_at' => $comment->created_at?->toIso8601String(),
            'author' => $comment->relationLoaded('user') && $comment->user
                ? self::user($comment->user)
                : null,
        ];
    }

    public static function file(RequestFile $file): array
    {
        return [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'size_bytes' => $file->size_bytes,
            'download_url' => rtrim(config('app.url'), '/')."/api/requests/{$file->work_request_id}/files/{$file->id}/download",
            'created_at' => $file->created_at?->toIso8601String(),
            'uploaded_by' => $file->relationLoaded('user') && $file->user
                ? self::user($file->user)
                : null,
        ];
    }

    public static function activity(ActivityLog $activity): array
    {
        return [
            'id' => $activity->id,
            'type' => $activity->type,
            'description' => $activity->description,
            'metadata' => $activity->metadata ?? [],
            'created_at' => $activity->created_at?->toIso8601String(),
            'actor' => $activity->relationLoaded('user') && $activity->user
                ? self::user($activity->user)
                : null,
        ];
    }

    public static function cannedReply(CannedReply $reply): array
    {
        return [
            'id' => $reply->id,
            'title' => $reply->title,
            'category' => $reply->category,
            'target_status' => $reply->target_status,
            'content' => $reply->content,
        ];
    }

    public static function notification(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->data['title'] ?? 'Portal update',
            'message' => $notification->data['message'] ?? '',
            'action_url' => $notification->data['action_url'] ?? null,
            'severity' => $notification->data['severity'] ?? 'info',
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
