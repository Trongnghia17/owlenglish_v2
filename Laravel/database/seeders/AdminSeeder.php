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
            'email' => 'nghia@gmail.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Teaching (role 1)
        User::create([
            'name' => 'Giáo viên Giảng dạy',
            'phone' => '0338989025',
            'email' => '',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_TEACHING,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Grading (role 2)
        User::create([
            'name' => 'Giáo viên Chấm bài',
            'phone' => '0338989026',
            'email' => '',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_GRADING,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher Content (role 3)
        User::create([
            'name' => 'Giáo viên Làm đề',
            'phone' => '0338989027',
            'email' => 'teacher_content@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER_CONTENT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Care (role 4)
        User::create([
            'name' => 'Chăm sóc Học viên',
            'phone' => '0338989028',
            'email' => 'student_care@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_CARE,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Assistant Content (role 5)
        User::create([
            'name' => 'Trợ lý Nội dung',
            'phone' => '0338989029',
            'email' => 'assistant_content@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ASSISTANT_CONTENT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Center (role 6)
        User::create([
            'name' => 'Học viên Trung tâm',
            'phone' => '0338989030',
            'email' => 'student_center@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_CENTER,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student Visitor (role 7)
        User::create([
            'name' => 'Học viên Vãng lai',
            'phone' => '0338989031',
            'email' => 'student_visitor@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT_VISITOR,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

    }

}