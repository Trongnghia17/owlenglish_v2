<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'channel', 'destination', 'code_hash', 'purpose',
        'expires_at', 'attempts', 'max_attempts', 'used_at',
        'ip', 'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public $timestamps = true;
}
