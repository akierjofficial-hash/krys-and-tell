<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];
}
