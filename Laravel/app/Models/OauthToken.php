<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class OauthToken extends Model
{
    protected $table = 'oauth_tokens';

    protected $fillable = [
        'provider',
        'access_token',
        'refresh_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
    ];

    protected $casts = [
        'access_token_expires_at'  => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    /**
     * Scope lấy token theo provider (zalo, google...)
     */
    public function scopeProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Kiểm tra access token còn hạn hay không
     */
    public function isAccessTokenValid(): bool
    {
        return $this->access_token
            && $this->access_token_expires_at
            && now()->lt($this->access_token_expires_at);
    }

    /**
     * Kiểm tra refresh token còn hạn hay không
     */
    public function isRefreshTokenValid(): bool
    {
        return $this->refresh_token
            && $this->refresh_token_expires_at
            && now()->lt($this->refresh_token_expires_at);
    }

    /**
     * Cập nhật access token
     */
    public function updateAccessToken(
        string $accessToken,
        int $expiresIn
    ): void {
        $this->update([
            'access_token' => $accessToken,
            'access_token_expires_at' => now()->addSeconds($expiresIn - 60),
        ]);
    }

    /**
     * Cập nhật refresh token
     */
    public function updateRefreshToken(
        string $refreshToken,
        int $expiresInHours = 720
    ): void {
        $this->update([
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => now()->addHours($expiresInHours),
        ]);
    }
}
