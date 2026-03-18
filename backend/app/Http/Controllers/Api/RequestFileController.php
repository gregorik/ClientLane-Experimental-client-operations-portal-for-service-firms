<?php

namespace App\Http\Controllers\Api;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadRequestFileRequest;
use App\Models\RequestFile;
use App\Models\User;
use App\Models\WorkRequest;
use App\Support\ActivityLogger;
use App\Support\PortalData;
use App\Support\PortalNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RequestFileController extends Controller
{
    public function store(UploadRequestFileRequest $request, WorkRequest $workRequest): JsonResponse
    {
        /** @var User $actor */
        $actor = $request->user();

        abort_unless($actor->canAccessRequest($workRequest), 403);

        $uploadedFile = $request->file('attachment');
        $extension = $uploadedFile->getClientOriginalExtension();
        $basename = Str::slug(pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME));
        $storedName = Str::uuid()->toString().'_'.$basename.($extension ? '.'.$extension : '');
        $path = $uploadedFile->storeAs("work-requests/{$workRequest->id}", $storedName, 'local');

        $file = RequestFile::create([
            'work_request_id' => $workRequest->id,
            'user_id' => $actor->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $storedName,
            'mime_type' => $uploadedFile->getClientMimeType(),
            'size_bytes' => $uploadedFile->getSize(),
        ]);

        if ($actor->isClient() && $workRequest->status === RequestStatus::WaitingOnClient) {
            $workRequest->update(['status' => RequestStatus::WaitingOnStaff->value]);
        }

        ActivityLogger::forRequest(
            $workRequest,
            $actor,
            'file.uploaded',
            "Uploaded file {$file->original_name}.",
            ['file_id' => $file->id]
        );

        PortalNotifier::send(
            PortalNotifier::recipientsFor($workRequest, $actor, true, true),
            'New file uploaded',
            "{$actor->name} uploaded {$file->original_name}.",
            PortalNotifier::requestUrl($workRequest),
            'success'
        );

        return response()->json([
            'data' => PortalData::file($file->load('user')),
        ], 201);
    }

    public function download(WorkRequest $workRequest, RequestFile $requestFile)
    {
        /** @var User $actor */
        $actor = request()->user();

        abort_unless($actor->canAccessRequest($workRequest), 403);
        abort_unless($requestFile->work_request_id === $workRequest->id, 404);

        return Storage::disk($requestFile->disk)->download(
            $requestFile->path,
            $requestFile->original_name
        );
    }
}
