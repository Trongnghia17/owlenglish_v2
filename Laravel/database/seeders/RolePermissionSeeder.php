<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Insert roles
        $roles = [
            ['id' => 1, 'name' => 'super_admin', 'display_name' => 'Super Admin (Chủ hệ thống)', 'description' => 'Toàn quyền hệ thống, bảo mật, thanh toán. Giới hạn 1–2 người.'],
            ['id' => 2, 'name' => 'org_admin', 'display_name' => 'Org Admin (Quản trị hệ thống)', 'description' => 'Quản trị cơ sở, người dùng, tích hợp, cấu hình. Mặc định không động tới payout.'],
            ['id' => 3, 'name' => 'academic_manager', 'display_name' => 'Academic Manager (Giáo vụ)', 'description' => 'Quản lý lớp, ghi danh, lịch học, điểm danh, vận hành kiểm tra.'],
            ['id' => 4, 'name' => 'acp', 'display_name' => 'Assessment & Curriculum Planning', 'description' => 'Chấm & trả bài, quản lý rubric & ngân hàng câu hỏi, quản trị phiên bản giáo trình, soạn lịch chờ duyệt.'],
            ['id' => 5, 'name' => 'teaching', 'display_name' => 'Teaching (Giáo viên)', 'description' => 'Giảng dạy, xem bài tập học viên, chấm điểm, điểm danh các lớp được phân công, chăm sóc học viên - phụ huynh.'],
            ['id' => 6, 'name' => 'student', 'display_name' => 'Student (Học viên)', 'description' => 'Học các khóa được phân, nộp bài, xem kết quả & hóa đơn của bản thân.'],
            ['id' => 7, 'name' => 'parent', 'display_name' => 'Parent/Guardian (Phụ huynh)', 'description' => 'Quyền xem tiến độ, điểm số, điểm danh, hóa đơn của con; nhắn tin GV/Giáo vụ.'],
            ['id' => 8, 'name' => 'content_author', 'display_name' => 'Content Author (Biên soạn nội dung)', 'description' => 'Soạn khóa, bài, ngân hàng câu hỏi; không vận hành lớp.'],
            ['id' => 9, 'name' => 'finance', 'display_name' => 'Finance (Kế toán)', 'description' => 'Hóa đơn, hoàn tiền, đối soát.'],
            ['id' => 10, 'name' => 'marketing', 'display_name' => 'Marketing', 'description' => 'Bài viết review, nội dung, trang đích, mã ưu đãi, thông báo; không thấy dữ liệu cá nhân.'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Insert permissions
        $permissions = [
            // QUYỀN QUẢN LÝ - System Management
            ['name' => 'manage_system', 'display_name' => 'Quản trị hệ thống', 'category' => 'Chủ hệ thống'],
            ['name' => 'view_dashboard', 'display_name' => 'Xem Dashboard', 'category' => 'Chủ hệ thống'],
            ['name' => 'setup_school', 'display_name' => 'Thiết lập trường', 'category' => 'Chủ hệ thống'],

            // QUẢN LÝ LỚP HỌC - Class Management
            ['name' => 'view_class_list', 'display_name' => 'Danh sách lớp', 'category' => 'Quản lý lớp'],
            ['name' => 'create_class', 'display_name' => 'Tạo lớp', 'category' => 'Quản lý lớp'],
            ['name' => 'edit_class', 'display_name' => 'Sửa lớp', 'category' => 'Quản lý lớp'],
            ['name' => 'delete_class', 'display_name' => 'Xóa lớp', 'category' => 'Quản lý lớp'],
            ['name' => 'view_class_details', 'display_name' => 'Xem chi tiết lớp', 'category' => 'Quản lý lớp'],

            // QUẢN LÝ HỌC SINH - Student Management
            ['name' => 'view_student_list', 'display_name' => 'Danh sách học sinh', 'category' => 'Quản lý học viên'],
            ['name' => 'add_student', 'display_name' => 'Thêm học sinh', 'category' => 'Quản lý học viên'],
            ['name' => 'update_student', 'display_name' => 'Cập nhật học sinh', 'category' => 'Quản lý học viên'],
            ['name' => 'delete_student', 'display_name' => 'Xóa học sinh', 'category' => 'Quản lý học viên'],
            ['name' => 'view_student_details', 'display_name' => 'Xem chi tiết', 'category' => 'Quản lý học viên'],

            // QUẢN LÝ BÀI GIẢNG - Lesson Management
            ['name' => 'manage_lessons', 'display_name' => 'Quản lý bài giảng', 'category' => 'Quản lý bài giảng'],

            // Additional permissions for other roles
            ['name' => 'manage_security', 'display_name' => 'Quản lý bảo mật', 'category' => 'Chủ hệ thống'],
            ['name' => 'manage_payment', 'display_name' => 'Quản lý thanh toán', 'category' => 'Chủ hệ thống'],
            ['name' => 'manage_integration', 'display_name' => 'Quản lý tích hợp', 'category' => 'Chủ hệ thống'],
            ['name' => 'manage_users', 'display_name' => 'Quản lý người dùng', 'category' => 'Chủ hệ thống'],
            ['name' => 'manage_config', 'display_name' => 'Quản lý cấu hình', 'category' => 'Chủ hệ thống'],

            // Academic Management
            ['name' => 'manage_classes', 'display_name' => 'Quản lý lớp học', 'category' => 'Giáo vụ'],
            ['name' => 'manage_enrollment', 'display_name' => 'Quản lý ghi danh', 'category' => 'Giáo vụ'],
            ['name' => 'manage_schedule', 'display_name' => 'Quản lý lịch học', 'category' => 'Giáo vụ'],
            ['name' => 'manage_attendance', 'display_name' => 'Điểm danh', 'category' => 'Giáo vụ'],
            ['name' => 'manage_exams', 'display_name' => 'Vận hành kiểm tra', 'category' => 'Giáo vụ'],

            // Assessment & Curriculum
            ['name' => 'grade_assignments', 'display_name' => 'Chấm & trả bài', 'category' => 'Chấm và giáo trình'],
            ['name' => 'manage_rubric', 'display_name' => 'Quản lý rubric', 'category' => 'Chấm và giáo trình'],
            ['name' => 'manage_question_bank', 'display_name' => 'Quản lý ngân hàng câu hỏi', 'category' => 'Chấm và giáo trình'],
            ['name' => 'manage_curriculum', 'display_name' => 'Quản trị phiên bản giáo trình', 'category' => 'Chấm và giáo trình'],
            ['name' => 'create_schedule_draft', 'display_name' => 'Soạn lịch chờ duyệt', 'category' => 'Chấm và giáo trình'],

            // Teaching
            ['name' => 'teach_classes', 'display_name' => 'Giảng dạy', 'category' => 'Giáo viên'],
            ['name' => 'view_student_work', 'display_name' => 'Xem bài tập học viên', 'category' => 'Giáo viên'],
            ['name' => 'grade_student_work', 'display_name' => 'Chấm điểm', 'category' => 'Giáo viên'],
            ['name' => 'take_attendance', 'display_name' => 'Điểm danh lớp được phân công', 'category' => 'Giáo viên'],
            ['name' => 'student_care', 'display_name' => 'Chăm sóc học viên - phụ huynh', 'category' => 'Giáo viên'],
            ['name' => 'respond_comments', 'display_name' => 'Phản hồi bình luận', 'category' => 'Giáo viên'],

            // Student
            ['name' => 'take_courses', 'display_name' => 'Học các khóa được phân', 'category' => 'Học viên'],
            ['name' => 'submit_assignments', 'display_name' => 'Nộp bài', 'category' => 'Học viên'],
            ['name' => 'view_own_results', 'display_name' => 'Xem kết quả của bản thân', 'category' => 'Học viên'],
            ['name' => 'view_own_invoices', 'display_name' => 'Xem hóa đơn của bản thân', 'category' => 'Học viên'],

            // Parent
            ['name' => 'view_child_progress', 'display_name' => 'Xem tiến độ con', 'category' => 'Phụ huynh'],
            ['name' => 'view_child_grades', 'display_name' => 'Xem điểm số con', 'category' => 'Phụ huynh'],
            ['name' => 'view_child_attendance', 'display_name' => 'Xem điểm danh con', 'category' => 'Phụ huynh'],
            ['name' => 'view_child_invoices', 'display_name' => 'Xem hóa đơn con', 'category' => 'Phụ huynh'],
            ['name' => 'message_teachers', 'display_name' => 'Nhắn tin GV/Giáo vụ', 'category' => 'Phụ huynh'],

            // Content Creation
            ['name' => 'create_courses', 'display_name' => 'Soạn khóa học', 'category' => 'Biên soạn nội dung'],
            ['name' => 'create_lessons', 'display_name' => 'Soạn bài học', 'category' => 'Biên soạn nội dung'],
            ['name' => 'create_questions', 'display_name' => 'Soạn ngân hàng câu hỏi', 'category' => 'Biên soạn nội dung'],

            // Finance
            ['name' => 'manage_invoices', 'display_name' => 'Quản lý hóa đơn', 'category' => 'Kế toán'],
            ['name' => 'process_refunds', 'display_name' => 'Xử lý hoàn tiền', 'category' => 'Kế toán'],
            ['name' => 'reconcile_payments', 'display_name' => 'Đối soát thanh toán', 'category' => 'Kế toán'],

            // Marketing
            ['name' => 'create_reviews', 'display_name' => 'Tạo bài viết review', 'category' => 'Marketing'],
            ['name' => 'manage_content', 'display_name' => 'Quản lý nội dung marketing', 'category' => 'Marketing'],
            ['name' => 'manage_landing_pages', 'display_name' => 'Quản lý trang đích', 'category' => 'Marketing'],
            ['name' => 'manage_promotions', 'display_name' => 'Quản lý mã ưu đãi', 'category' => 'Marketing'],
            ['name' => 'manage_notifications', 'display_name' => 'Quản lý thông báo', 'category' => 'Marketing'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $rolePermissions = [
            // Super Admin - All permissions
            1 => 'all',

            // Org Admin
            2 => [
                'manage_system',
                'manage_integration',
                'view_dashboard',
                'manage_users',
                'manage_config',
                'setup_school'
            ],

            // Academic Manager
            3 => [
                'view_dashboard',
                'manage_classes',
                'manage_enrollment',
                'manage_schedule',
                'manage_attendance',
                'manage_exams',
                'view_class_list',
                'create_class',
                'edit_class',
                'delete_class',
                'view_class_details',
                'view_student_list',
                'add_student',
                'update_student',
                'delete_student',
                'view_student_details'
            ],

            // ACP
            4 => [
                'view_dashboard',
                'grade_assignments',
                'manage_rubric',
                'manage_question_bank',
                'manage_curriculum',
                'create_schedule_draft',
                'manage_lessons'
            ],

            // Teaching
            5 => [
                'view_dashboard',
                'teach_classes',
                'view_student_work',
                'grade_student_work',
                'take_attendance',
                'student_care',
                'respond_comments',
                'view_class_details',
                'view_student_list',
                'view_student_details'
            ],

            // Student
            6 => [
                'take_courses',
                'submit_assignments',
                'view_own_results',
                'view_own_invoices'
            ],

            // Parent
            7 => [
                'view_child_progress',
                'view_child_grades',
                'view_child_attendance',
                'view_child_invoices',
                'message_teachers'
            ],

            // Content Author
            8 => [
                'view_dashboard',
                'create_courses',
                'create_lessons',
                'create_questions'
            ],

            // Finance
            9 => [
                'view_dashboard',
                'manage_invoices',
                'process_refunds',
                'reconcile_payments'
            ],

            // Marketing
            10 => [
                'view_dashboard',
                'create_reviews',
                'manage_content',
                'manage_landing_pages',
                'manage_promotions',
                'manage_notifications'
            ],
        ];

        foreach ($rolePermissions as $roleId => $permissions) {
            if ($permissions === 'all') {
                // Super Admin gets all permissions
                $allPermissions = DB::table('permissions')->pluck('id');
                foreach ($allPermissions as $permissionId) {
                    DB::table('role_permissions')->insert([
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                foreach ($permissions as $permissionName) {
                    $permission = DB::table('permissions')->where('name', $permissionName)->first();
                    if ($permission) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $roleId,
                            'permission_id' => $permission->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }
}
