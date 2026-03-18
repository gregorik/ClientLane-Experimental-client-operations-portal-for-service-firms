<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'firm_id',
        'client_id',
        'name',
        'email',
        'title',
        'role',
        'password',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'role' => UserRole::class,
            'password' => 'hashed',
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

    public function assignedRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class, 'assigned_to_user_id');
    }

    public function submittedRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class, 'submitted_by_user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RequestComment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(RequestFile::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    public function canAccessClient(Client $client): bool
    {
        if ($this->isStaff()) {
            return $this->firm_id === $client->firm_id;
        }

        return $this->client_id === $client->id && $this->firm_id === $client->firm_id;
    }

    public function canAccessRequest(WorkRequest $workRequest): bool
    {
        if ($this->firm_id !== $workRequest->firm_id) {
            return false;
        }

        if ($this->isStaff()) {
            return true;
        }

        return $this->client_id === $workRequest->client_id;
    }
}
