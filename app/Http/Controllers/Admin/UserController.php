<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
public function index(Request $request)
{
    $query = User::query();

    // Lọc bỏ admin (role = 0) khỏi danh sách hiển thị
    $query->where('role', '!=', 0);
    // Filter by role group if specified
    if ($request->has('role')) {
        $roleParam = $request->get('role');

        switch ($roleParam) {
            case 'teacher':
                $query->whereIn('role', [1, 2, 3]); // Teacher roles
                break;
            case 'assistant':
                $query->whereIn('role', [4, 5]); // Assistant roles
                break;
            case 'student':
                $query->whereIn('role', [6, 7]); // Student roles
                break;
            case '0': // Admin role
                $query->where('role', 0);
                break;
        }
    }

    // Add search functionality if search term exists
    if ($request->has('search') && $request->search) {
        $searchTerm = $request->search;
        $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('email', 'like', "%{$searchTerm}%");
        });
    }

    $users = $query->orderBy('created_at', 'desc')->paginate(15);

    // Preserve query parameters in pagination links
    $users->appends($request->query());

    return view('admin.users.index', compact('users'));
}

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone|max:20',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|integer|in:1,2,3,4,5,6,7', // Bỏ role 0 (admin)
            'is_active' => 'boolean',
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Tạo tài khoản thành công!');
    }


    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // Kiểm tra quyền sửa vai trò
        if (!auth()->user()->canEditRole()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa vai trò!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|integer|in:1,2,3,4,5,6,7', // Bỏ role 0 (admin)
            'is_active' => 'boolean',
        ]);

        // Không cho phép admin tự thay đổi vai trò của mình
        if ($user->id === auth()->id() && $request->role != $user->role) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể thay đổi vai trò của chính mình!');
        }

        $updateData = [
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Cập nhật tài khoản thành công!');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Xóa tài khoản thành công!');
    }

    public function toggleStatus(User $user)
    {
        // Kiểm tra quyền thay đổi trạng thái
        if (!auth()->user()->canEditRole()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không có quyền thay đổi trạng thái tài khoản!');
        }

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Bạn không thể thay đổi trạng thái tài khoản của chính mình!');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
        return redirect()->route('admin.users.index')
            ->with('success', "Đã {$status} tài khoản thành công!");
    }

}
