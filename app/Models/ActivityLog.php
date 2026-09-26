<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'description',
        'target_type',
        'target_id',
        'route_name',
        'url',
        'method',
        'status_code',
        'ip',
        'user_agent',
        'properties',
        'before_values',
        'after_values',
        'reason',
        'is_sensitive',
        'succeeded',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'before_values' => 'array',
        'after_values' => 'array',
        'is_sensitive' => 'boolean',
        'succeeded' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
