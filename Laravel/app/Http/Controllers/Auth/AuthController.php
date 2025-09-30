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

    /**
     * Redirect user based on their role
     */
   
private function redirectBasedOnRole(User $user)
{
    // Super Admin và Org Admin -> Dashboard admin
    if ($user->hasAnyRole([1, 2])) {
        return redirect()->route('admin.dashboard');
    }

    // Academic Manager -> Dashboard giáo vụ  
    if ($user->hasRole(3)) {
        return redirect()->route('admin.statistic.teacher');
    }

    // Assessment & Curriculum Planning -> Dashboard ACP
    if ($user->hasRole(4)) {
        return redirect()->route('admin.statistic.student');
    }

    // Teaching -> Dashboard giáo viên
    if ($user->hasRole(5)) {
        return redirect()->route('teacher.dashboard');
    }

    // Student -> Trang học viên (có thể là React app)
    if ($user->hasRole(6)) {
        // Redirect đến React app hoặc student portal
        $frontendUrl = config('app.frontend_url', env('FRONTEND_APP_URL', 'http://localhost:5173'));
        return redirect()->away($frontendUrl . '/student/dashboard');
    }

    // Parent -> Trang phụ huynh
    if ($user->hasRole(7)) {
        return redirect()->route('parent.dashboard');
    }

    // Content Author -> Dashboard biên soạn
    if ($user->hasRole(8)) {
        return redirect()->route('content.dashboard');
    }

    // Finance -> Dashboard kế toán
    if ($user->hasRole(9)) {
        return redirect()->route('finance.dashboard');
    }

    // Marketing -> Dashboard marketing
    if ($user->hasRole(10)) {
        return redirect()->route('marketing.dashboard');
    }

    // Mặc định -> trang chủ hoặc dashboard chung
    return redirect()->route('admin.dashboard');
}

    /**
     * Handle home page redirect - check authentication and redirect based on role
     */
    public function home()
    {
        if (auth()->check()) {
            $user = auth()->user();

            if (!$user->is_active) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
            }

            return $this->redirectBasedOnRole($user);
        }

        return redirect()->route('login');
    }
}
