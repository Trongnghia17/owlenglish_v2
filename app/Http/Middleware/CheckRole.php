<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
        }

        // Convert string roles to integers
        $intRoles = array_map(function($role) {
            return is_numeric($role) ? (int)$role : $this->convertRoleToInt($role);
        }, $roles);

        if (!$user->hasAnyRole($intRoles)) {
            abort(403, 'Bạn không có quyền truy cập vào trang này.');
        }

        return $next($request);
    }

  private function convertRoleToInt(string $role): int
    {
        $roleMap = [
            'admin' => 0,
            'teacher_teaching' => 1,
            'teacher_grading' => 2,
            'teacher_content' => 3,
            'student_care' => 4,
            'assistant_content' => 5,
            'student_center' => 6,
            'student_visitor' => 7,
        ];

        return $roleMap[$role] ?? 7; // Default to student_visitor (highest number)
    }
}