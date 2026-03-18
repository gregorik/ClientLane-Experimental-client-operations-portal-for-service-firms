<?php

namespace App\Models;

use App\Enums\RequestPriority;
use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkRequest extends Model
{
    /** @use HasFactory<\Database\Factories\WorkRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'client_id',
        'submitted_by_user_id',
        'assigned_to_user_id',
        'title',
        'request_type',
        'summary',
        'status',
        'priority',
        'due_at',
        'last_reminded_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RequestStatus::class,
            'priority' => RequestPriority::class,
            'due_at' => 'datetime',
            'last_reminded_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class)->latest();
    }

    public function files(): HasMany
    {
        return $this->hasMany(RequestFile::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $query->where('firm_id', $user->firm_id);

        if ($user->isClient()) {
            $query->where('client_id', $user->client_id);
        }

        return $query;
    }
}
