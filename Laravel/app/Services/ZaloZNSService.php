<?php

namespace App\Services;

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
        $token = Cache::get('zalo_zns_access_token');

        if (!$token) {
            $token = $this->refreshToken();
        }

        $response = $this->callSendOtpApi($token, $phone, $otp);

        if (($response['error'] ?? 0) === -216) {
            $token = $this->refreshToken();
            return $this->callSendOtpApi($token, $phone, $otp);
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
     * Refresh access_token (có lock chống race)
     */
    protected function refreshToken(): string
    {
        return Cache::lock('zalo_refresh_token_lock', 10)->block(5, function () {

            // Có thể token đã được refresh bởi request khác
            if ($token = Cache::get('zalo_zns_access_token')) {
                return $token;
            }

            $refreshToken = Cache::get('zalo_zns_refresh_token')
                ?? config('zalo.refresh_token');

            $response = Http::asForm()
                ->withHeaders([
                    'secret_key' => config('zalo.app_secret'),
                ])
                ->post('https://oauth.zaloapp.com/v4/oa/access_token', [
                    'app_id'        => config('zalo.app_id'),
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $refreshToken,
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

            Cache::put(
                'zalo_zns_access_token',
                $data['access_token'],
                now()->addSeconds($data['expires_in'] - 60)
            );

            Cache::forever(
                'zalo_zns_refresh_token',
                $data['refresh_token']
            );

            return $data['access_token'];
        });
    }
}
