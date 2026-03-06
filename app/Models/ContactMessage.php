<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'email',
        'message',
        'read_at',
        'replied_at',
        'reply_subject',
        'reply_message',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'replied_at' => 'datetime',
    ];
}
