<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin (role 0)
        User::create([
            'name' => 'Admin',
            'phone' => '0338989024',
            'email' => 'admin@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Teaching (role 1)
        User::create([
            'name' => 'Giáo viên Giảng dạy',
            'phone' => '0987654321',
            'email' => 'teacher_teaching@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_TEACHING,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Grading (role 2)
        User::create([
            'name' => 'Giáo viên Chấm bài',
            'phone' => '0987654322',
            'email' => 'teacher_grading@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_GRADING,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Content (role 3)
        User::create([
            'name' => 'Giáo viên Làm đề',
            'phone' => '0987654323',
            'email' => 'teacher_content@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_CONTENT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Care (role 4)
        User::create([
            'name' => 'Chăm sóc Học viên',
            'phone' => '0987654324',
            'email' => 'student_care@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_CARE,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Assistant Content (role 5)
        User::create([
            'name' => 'Trợ lý Nội dung',
            'phone' => '0987654325',
            'email' => 'assistant_content@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ASSISTANT_CONTENT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Center (role 6)
        User::create([
            'name' => 'Học viên Trung tâm',
            'phone' => '0987654326',
            'email' => 'student_center@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_CENTER,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Visitor (role 7)
        User::create([
            'name' => 'Học viên Vãng lai',
            'phone' => '0987654327',
            'email' => 'student_visitor@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_VISITOR,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create additional sample users for each role group
        $this->createSampleUsers();
    }

    private function createSampleUsers(): void
    {
        // Additional Teachers
        for ($i = 1; $i <= 3; $i++) {
            User::create([
                'name' => "Giáo viên Giảng dạy {$i}",
                'phone' => "098765" . str_pad(4000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "gv_gd{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_TEACHER_TEACHING,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => "Giáo viên Chấm bài {$i}",
                'phone' => "098765" . str_pad(5000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "gv_cb{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_TEACHER_GRADING,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => "Giáo viên Làm đề {$i}",
                'phone' => "098765" . str_pad(6000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "gv_ld{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_TEACHER_CONTENT,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        // Additional Assistants
        for ($i = 1; $i <= 2; $i++) {
            User::create([
                'name' => "Chăm sóc Học viên {$i}",
                'phone' => "098765" . str_pad(7000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "cshv{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_STUDENT_CARE,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => "Trợ lý Nội dung {$i}",
                'phone' => "098765" . str_pad(8000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "tlnd{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_ASSISTANT_CONTENT,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }

        // Additional Students
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "Học viên TT {$i}",
                'phone' => "098765" . str_pad(9000 + $i, 4, '0', STR_PAD_LEFT),
                'email' => "hvtt{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_STUDENT_CENTER,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            User::create([
                'name' => "Học viên VL {$i}",
                'phone' => "098766" . str_pad($i, 4, '0', STR_PAD_LEFT),
                'email' => "hvvl{$i}@owlenglish.com",
                'password' => Hash::make('123456'),
                'role' => User::ROLE_STUDENT_VISITOR,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
        }
    }
}