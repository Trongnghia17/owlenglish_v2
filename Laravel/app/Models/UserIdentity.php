<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIdentity extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'provider_user_id',
        'email_at_signup', 'phone_at_signup',
        'access_token', 'refresh_token', 'token_expires_at',
        'verified_at', 'is_primary',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'verified_at'      => 'datetime',
        'is_primary'       => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
