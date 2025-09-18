<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserContact;
use App\Models\UserIdentity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1) Tạo user mẫu với role Super Admin
            $user = User::firstOrCreate(
                ['email' => 'demo@owl.edu.vn'],
                [
                    'name'               => 'OWL Demo',
                    'avatar_url'         => null,
                    'phone'              => '0987654321',
                    'email_verified_at'  => now(),
                    'phone_verified_at'  => now(),
                    'password'           => Hash::make('123456'), // demo
                    'role_id'            => 1, // Super Admin role
                ]
            );

            // 2) Thêm contacts (email + phone)
            UserContact::updateOrCreate(
                ['type' => 'email', 'value' => 'demo@owl.edu.vn'],
                [
                    'user_id'     => $user->id,
                    'is_primary'  => true,
                    'verified_at' => now(),
                ]
            );

            UserContact::updateOrCreate(
                ['type' => 'phone', 'value' => '0987654321'],
                [
                    'user_id'     => $user->id,
                    'is_primary'  => true,
                    'verified_at' => now(),
                ]
            );

            // 3) Link các phương thức đăng nhập (identities)

            // Local email (Email OTP hoặc dùng mật khẩu)
            UserIdentity::updateOrCreate(
                ['provider' => 'local_email', 'provider_user_id' => 'demo@owl.edu.vn'],
                [
                    'user_id'         => $user->id,
                    'email_at_signup' => 'demo@owl.edu.vn',
                    'verified_at'     => now(),
                    'is_primary'      => true,
                ]
            );

            // Local phone (Phone OTP qua Zalo OA)
            UserIdentity::updateOrCreate(
                ['provider' => 'local_phone', 'provider_user_id' => '0987654321'],
                [
                    'user_id'          => $user->id,
                    'phone_at_signup'  => '0987654321',
                    'verified_at'      => now(),
                    'is_primary'       => false,
                ]
            );

            // Google
            UserIdentity::updateOrCreate(
                ['provider' => 'google', 'provider_user_id' => 'google-sub-1234567890'],
                [
                    'user_id'         => $user->id,
                    'email_at_signup' => 'demo@owl.edu.vn',
                    'verified_at'     => now(),
                    // Demo token (nếu cần): bạn có thể encrypt/để null
                    'access_token'    => encrypt('fake_google_access_token_'.Str::random(10)),
                    'refresh_token'   => encrypt('fake_google_refresh_token_'.Str::random(10)),
                    'token_expires_at'=> Carbon::now()->addDays(7),
                ]
            );

            // Facebook
            UserIdentity::updateOrCreate(
                ['provider' => 'facebook', 'provider_user_id' => 'fb-id-987654321'],
                [
                    'user_id'         => $user->id,
                    'verified_at'     => now(),
                ]
            );

            // Bạn có thể thêm Zalo OA nếu dùng đăng nhập trực tiếp với OA:
            // UserIdentity::updateOrCreate(
            //   ['provider' => 'zalo_oa', 'provider_user_id' => 'zalo-user-id-abc'],
            //   ['user_id' => $user->id, 'verified_at' => now()]
            // );
        });
    }
}
