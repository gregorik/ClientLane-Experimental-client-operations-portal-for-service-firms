<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Firm extends Model
{
    /** @use HasFactory<\Database\Factories\FirmFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'niche',
        'portal_tagline',
        'primary_color',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function cannedReplies(): HasMany
    {
        return $this->hasMany(CannedReply::class);
    }
}
