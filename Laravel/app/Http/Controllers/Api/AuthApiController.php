<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\OtpCode;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthApiController extends Controller
{
    /** Step 1: Redirect sang Google */
    public function googleRedirect()
    {
        // stateless cho SPA (không dùng session state)
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    /** Step 2: Callback từ Google */
    public function googleCallback()
    {
        $frontend = config('app.frontend_url', env('FRONTEND_APP_URL', 'http://localhost:5173'));
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            // Dữ liệu từ Google
            // dd($googleUser);
            $googleId = $googleUser->getId();           // sub
            $email    = $googleUser->getEmail();        // có thể null!
            $name     = $googleUser->getName();
            $avatar   = $googleUser->getAvatar();
            // dd($googleId);
            if (!$googleId) {
                return redirect()->away($frontend . '/login?error=google_no_id');
            }

            /** Tìm/ tạo user + link user_identities */
            $user = DB::transaction(function () use ($googleId, $email, $name, $avatar, $googleUser) {
                // 1) Tìm theo user_identities (provider=google)
                $identity = UserIdentity::where('provider', 'google')
                    ->where('provider_user_id', $googleId)
                    ->first();

                if ($identity) {
                    $user = $identity->user;
                } else {
                    // 2) Nếu chưa link identity, thử khớp theo email (nếu có)
                    $user = null;
                    if ($email) {
                        $user = User::where('email', mb_strtolower($email))->first();

                        // Nếu user chưa có, tạo mới
                        if (!$user) {
                            $user = User::create([
                                'name'  => $name ?: 'User ' . Str::random(6),
                                'email' => $email ? mb_strtolower($email) : null,
                                'avatar_url' => $avatar,
                                'email_verified_at' => now(), // OAuth coi như verified
                                'password' => null,            // social only
                            ]);
                        }

                        // đảm bảo có contact email
                        if ($email) {
                            UserContact::firstOrCreate(
                                ['type' => 'email', 'value' => mb_strtolower($email)],
                                ['user_id' => $user->id, 'is_primary' => true, 'verified_at' => now()]
                            );
                        }
                    }

                    // 3) Nếu không có email (trường hợp hiếm) → vẫn tạo user
                    if (!$user) {
                        $user = User::create([
                            'name'  => $name ?: 'Google User',
                            'avatar_url' => $avatar,
                            'password' => null,
                        ]);
                    }

                    // 4) Tạo identity google
                    UserIdentity::create([
                        'user_id'          => $user->id,
                        'provider'         => 'google',
                        'provider_user_id' => $googleId,
                        'email_at_signup'  => $email,
                        'verified_at'      => now(),
                        'access_token'     => encrypt($googleUser->token ?? ''),           // bảo mật
                        'refresh_token'    => encrypt($googleUser->refreshToken ?? ''),    // có thể null
                        'token_expires_at' => isset($googleUser->expiresIn)
                            ? now()->addSeconds(intval($googleUser->expiresIn))
                            : null,
                    ]);

                    // cập nhật avatar nếu trống
                    if (!$user->avatar_url && $avatar) {
                        $user->update(['avatar_url' => $avatar]);
                    }
                }

                return $user;
            });

            // Phát token Sanctum
            $token = $user->createToken('api')->plainTextToken;
            // Redirect về FE kèm token + provider
            $url = $frontend . '/oauth/callback?provider=google'
                . '&token=' . urlencode($token);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            return redirect()->away($frontend . '/login?error=google_callback');
        }
    }

    public function facebookRedirect()
    {
        // Yêu cầu quyền email (có thể user không cấp/FB không trả email nếu chưa verify)
        return Socialite::driver('facebook')
            ->stateless()
            ->scopes(['email'])
            ->redirect();
        return 22;
    }

    public function facebookCallback()
    {
        $frontend = config('app.frontend_url', env('FRONTEND_APP_URL', 'http://localhost:5173'));

        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();

            $fbId   = $fbUser->getId();       // Facebook numeric id
            $email  = $fbUser->getEmail();    // Có thể null!
            $name   = $fbUser->getName();
            $avatar = $fbUser->getAvatar();

            if (!$fbId) {
                return redirect()->away($frontend . '/login?error=facebook_no_id');
            }

            $user = DB::transaction(function () use ($fbId, $email, $name, $avatar, $fbUser) {
                // 1) Tìm theo user_identities
                $identity = UserIdentity::where('provider', 'facebook')
                    ->where('provider_user_id', $fbId)
                    ->first();

                if ($identity) {
                    $user = $identity->user;
                } else {
                    // 2) Nếu chưa link, cố gắng match theo email (nếu có)
                    $user = null;
                    if ($email) {
                        $user = User::where('email', mb_strtolower($email))->first();
                        if (!$user) {
                            $user = User::create([
                                'name'  => $name ?: 'FB User ' . Str::random(6),
                                'email' => mb_strtolower($email),
                                'avatar_url' => $avatar,
                                'email_verified_at' => now(),
                                'password' => null,
                            ]);
                        }

                        UserContact::firstOrCreate(
                            ['type' => 'email', 'value' => mb_strtolower($email)],
                            ['user_id' => $user->id, 'is_primary' => true, 'verified_at' => now()]
                        );
                    }

                    // 3) Nếu không có email, vẫn tạo user
                    if (!$user) {
                        $user = User::create([
                            'name'  => $name ?: 'Facebook User',
                            'avatar_url' => $avatar,
                            'password' => null,
                        ]);
                    }

                    // 4) Tạo identity facebook
                    UserIdentity::create([
                        'user_id'          => $user->id,
                        'provider'         => 'facebook',
                        'provider_user_id' => $fbId,
                        'email_at_signup'  => $email,
                        'verified_at'      => now(),
                        'access_token'     => encrypt($fbUser->token ?? ''),
                        'refresh_token'    => null, // Facebook thường không có refresh token kiểu OAuth2 chuẩn
                        'token_expires_at' => null, // bạn có thể lưu thêm expires nếu SDK trả về
                    ]);

                    if (!$user->avatar_url && $avatar) {
                        $user->update(['avatar_url' => $avatar]);
                    }
                }

                return $user;
            });

            $token = $user->createToken('api')->plainTextToken;

            $url = $frontend . '/oauth/callback?provider=facebook&token=' . urlencode($token);
            return redirect()->away($url);
        } catch (\Throwable $e) {
            dd($e->getMessage(), $e->getTraceAsString());
            return redirect()->away($frontend . '/login?error=facebook_callback');
        }
    }

    // Gửi OTP
    public function sendOtp(Request $request)
    {
        $request->validate([
            'channel' => 'required|in:email,zalo_oa',
            'destination' => 'required|string',
            'purpose' => 'required|in:register,link_contact,reset_password',
        ]);

        $email = $request->email;
        $otp = rand(100000, 999999);
        OtpCode::create([
            'channel' => 'email',
            'destination' => $email,
            'code_hash' => Hash::make($otp),
            'purpose' => $request->purpose,
            'expires_at' => now()->addMinutes(5),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email) {
            $message->to($email)
                ->subject('Mã xác thực OTP');
        });

        return response()->json(['message' => 'OTP đã được gửi'], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string',
            'password' => 'required|min:6',
            'purpose' => 'required|in:register,link_contact,reset_password',
        ]);

        $otpRecord = OtpCode::where('destination', $request->email)
            ->where('purpose', $request->purpose)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->code_hash)) {
            return response()->json(['message' => 'OTP không hợp lệ hoặc đã hết hạn'], 400);
        }
        $otpRecord->update(['used_at' => now()]);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(),
            ]);
        } else {
            $user->update([
                'password' => Hash::make($request->password),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        }

        return response()->json([
            'message' => 'Xác thực thành công',
            'user' => $user,
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;
        $password = $request->password;

        $user = User::where('email', $username)
            ->orWhere('phone', $username)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 401);
        }

        // Nếu muốn check xác thực email/phone
        if (!$user->email_verified_at && !$user->phone_verified_at) {
            return response()->json(['message' => 'Tài khoản chưa được xác thực'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user'    => $user,
            'token'   => $token,
        ]);
    }
}
