<?php

namespace App\Services;

use App\Models\OauthToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Exception;

class ZaloZNSService
{
    protected string $templateId;

    public function __construct()
    {
        $this->templateId = config('zalo.template_id');
    }

    /**
     * Gửi OTP qua Zalo ZNS
     */
    public function sendOtp(string $phone, string $otp): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->callSendOtpApi($accessToken, $phone, $otp);

        // Access token hết hạn → refresh lại
        if (($response['error'] ?? 0) === -216) {
            $accessToken = $this->refreshToken();
            return $this->callSendOtpApi($accessToken, $phone, $otp);
        }

        return $response;
    }

    /**
     * Call API gửi OTP
     */
    protected function callSendOtpApi(string $token, string $phone, string $otp): array
    {
        return Http::withHeaders([
            'access_token' => $token,
        ])->post('https://business.openapi.zalo.me/message/template', [
            'phone' => $phone,
            'template_id' => $this->templateId,
            'template_data' => [
                'otp' => $otp,
            ],
        ])->json();
    }

    /**
     * Lấy access token (ưu tiên cache → DB)
     */
    protected function getAccessToken(): string
    {
        return Cache::remember('zalo_zns_access_token', 300, function () {
            $token = OauthToken::provider('zalo')->first();

            if (!$token) {
                throw new Exception('Chưa cấu hình Zalo OAuth token');
            }

            if ($token->isAccessTokenValid()) {
                return $token->access_token;
            }

            return $this->refreshToken();
        });
    }

    /**
     * Refresh access token (lock chống race condition)
     */
    protected function refreshToken(): string
    {
        return Cache::lock('zalo_refresh_token_lock', 10)->block(5, function () {

            // Có thể request khác đã refresh
            if ($token = Cache::get('zalo_zns_access_token')) {
                return $token;
            }

            $oauth = OauthToken::provider('zalo')->first();

            if (!$oauth || !$oauth->isRefreshTokenValid()) {
                throw new Exception('Refresh token Zalo không hợp lệ hoặc đã hết hạn');
            }

            $response = Http::asForm()
                ->withHeaders([
                    'secret_key' => config('zalo.app_secret'),
                ])
                ->post('https://oauth.zaloapp.com/v4/oa/access_token', [
                    'app_id'        => config('zalo.app_id'),
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $oauth->refresh_token,
                ]);

            if (!$response->ok()) {
                throw new Exception('Refresh Zalo token thất bại: ' . $response->body());
            }

            $data = $response->json();

            if (
                !isset($data['access_token']) ||
                !isset($data['refresh_token']) ||
                !isset($data['expires_in'])
            ) {
                throw new Exception('Response token Zalo không hợp lệ');
            }

            // Update DB
            $oauth->updateAccessToken(
                $data['access_token'],
                $data['expires_in']
            );

            $oauth->updateRefreshToken(
                $data['refresh_token']
            );

            Cache::put(
                'zalo_zns_access_token',
                $data['access_token'],
                now()->addSeconds($data['expires_in'] - 60)
            );

            return $data['access_token'];
        });
    }
}
