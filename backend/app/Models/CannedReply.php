<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedReply extends Model
{
    /** @use HasFactory<\Database\Factories\CannedReplyFactory> */
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'title',
        'category',
        'target_status',
        'content',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }
}
