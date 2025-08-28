<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^[0-9]{10,11}$/',
            'password' => 'required|string|min:6',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải có 10-11 chữ số.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        $credentials = $request->only('phone', 'password');
        
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            if (!$user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'phone' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                ]);
            }

            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'phone' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

     private function redirectBasedOnRole(User $user)
    {
        switch ($user->role) {
            case User::ROLE_ADMIN:
                return redirect()->route('admin.dashboard');
            case User::ROLE_TEACHER_TEACHING:
            case User::ROLE_TEACHER_GRADING:
            case User::ROLE_TEACHER_CONTENT:
                return redirect()->route('teacher.dashboard');
            case User::ROLE_STUDENT_CARE:
            case User::ROLE_ASSISTANT_CONTENT:
                return redirect()->route('assistant.dashboard');
            case User::ROLE_STUDENT_CENTER:
            case User::ROLE_STUDENT_VISITOR:
            default:
                return redirect()->route('student.dashboard');
        }
    }
}