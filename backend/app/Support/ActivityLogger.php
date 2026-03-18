<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Firm;
use App\Models\User;
use App\Models\WorkRequest;

class ActivityLogger
{
    public static function forFirm(
        Firm $firm,
        ?User $actor,
        string $type,
        string $description,
        array $metadata = []
    ): ActivityLog {
        return ActivityLog::create([
            'firm_id' => $firm->id,
            'work_request_id' => null,
            'user_id' => $actor?->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public static function forRequest(
        WorkRequest $workRequest,
        ?User $actor,
        string $type,
        string $description,
        array $metadata = []
    ): ActivityLog {
        return ActivityLog::create([
            'firm_id' => $workRequest->firm_id,
            'work_request_id' => $workRequest->id,
            'user_id' => $actor?->id,
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
