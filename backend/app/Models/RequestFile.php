<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestFile extends Model
{
    /** @use HasFactory<\Database\Factories\RequestFileFactory> */
    use HasFactory;

    protected $fillable = [
        'work_request_id',
        'user_id',
        'disk',
        'path',
        'original_name',
        'stored_name',
        'mime_type',
        'size_bytes',
    ];

    public function workRequest(): BelongsTo
    {
        return $this->belongsTo(WorkRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
