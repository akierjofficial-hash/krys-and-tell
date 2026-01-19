<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_set',
        'role',
        'is_active',
        'last_login_at',
        'google_id',
        'notify_24h',
        'notify_1h',
        'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'password_set'      => 'boolean',
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
            'notify_24h'        => 'boolean',
            'notify_1h'         => 'boolean',
        ];
    }
}
