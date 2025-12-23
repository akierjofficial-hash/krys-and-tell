<?php

// app/Models/AdminLoginOtp.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLoginOtp extends Model
{
    protected $fillable = [
        'user_id','code_hash','expires_at','attempts','used_at','ip','user_agent'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool {
        return now()->greaterThan($this->expires_at);
    }

    public function isUsed(): bool {
        return !is_null($this->used_at);
    }
}
