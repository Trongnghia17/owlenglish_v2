<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with(['role', 'permissions', 'role.permissions']);
        
        // Tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }
        
        // Lọc theo vai trò
        if ($request->filled('role')) {
            $query->where('role_id', $request->role);
        }
        
        // Sắp xếp theo ngày tạo mới nhất
        $query->orderBy('created_at', 'desc');
        
        $users = $query->paginate(15)->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(6)],
            'role' => 'required|exists:roles,id',
            'is_active' => 'required|boolean'
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => $request->role,
                'is_active' => $request->is_active,
                'phone_verified_at' => now(), // Tự động verify phone khi admin tạo
            ]);

            // Tạo user contacts nếu cần
            if ($request->email) {
                $user->contacts()->create([
                    'type' => 'email',
                    'value' => $request->email,
                    'is_primary' => true,
                    'verified_at' => now()
                ]);
            }

            if ($request->phone) {
                $user->contacts()->create([
                    'type' => 'phone', 
                    'value' => $request->phone,
                    'is_primary' => true,
                    'verified_at' => now()
                ]);
            }
        });

        return redirect()->route('admin.users.index')
                        ->with('success', 'Người dùng đã được tạo thành công!');
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'is_active' => 'required|boolean'
        ];

        // Chỉ cho phép thay đổi role nếu không phải chính mình
        if ($user->id !== auth()->id()) {
            $rules['role'] = 'required|exists:roles,id';
        }

        // Nếu có password thì validate
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(6)];
        }

        $request->validate($rules);

        DB::transaction(function() use ($request, $user) {
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'is_active' => $request->is_active,
            ];

            // Chỉ cập nhật role nếu không phải chính mình
            if ($user->id !== auth()->id()) {
                $updateData['role_id'] = $request->role;
            }

            // Cập nhật password nếu có
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            // Cập nhật contacts
            if ($request->email) {
                $user->contacts()->updateOrCreate(
                    ['type' => 'email'],
                    [
                        'value' => $request->email,
                        'is_primary' => true,
                        'verified_at' => now()
                    ]
                );
            }

            if ($request->phone) {
                $user->contacts()->updateOrCreate(
                    ['type' => 'phone'],
                    [
                        'value' => $request->phone,
                        'is_primary' => true,
                        'verified_at' => now()
                    ]
                );
            }
        });

        return redirect()->route('admin.users.index')
                        ->with('success', 'Thông tin người dùng đã được cập nhật!');
    }

    public function destroy(User $user)
    {
        // Không cho phép xóa chính mình
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        // Không cho phép xóa Super Admin
        if ($user->hasRole(1)) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Không thể xóa tài khoản Super Admin!');
        }

        DB::transaction(function() use ($user) {
            // Xóa các liên kết
            $user->contacts()->delete();
            $user->identities()->delete();
            
            // Xóa user
            $user->delete();
        });

        return redirect()->route('admin.users.index')
                        ->with('success', 'Người dùng đã được xóa thành công!');
    }

 public function toggleStatus(User $user)
{
    // Không cho phép thay đổi trạng thái chính mình
    if ($user->id === auth()->id()) {
        return redirect()->route('admin.users.index')
                        ->with('error', 'Bạn không thể thay đổi trạng thái của chính mình!');
    }

    $user->update([
        'is_active' => !$user->is_active
    ]);

    $status = $user->is_active ? 'kích hoạt' : 'vô hiệu hóa';
    
    return redirect()->route('admin.users.index')
                    ->with('success', "Đã {$status} tài khoản {$user->name}!");
}





    public function updatePermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $permissions = $request->permissions ?? [];
        
        // Sync permissions (sẽ xóa permissions cũ và thêm mới)
        $permissionIds = \App\Models\Permission::whereIn('name', $permissions)->pluck('id');
        $user->permissions()->sync($permissionIds);

        return redirect()->route('admin.users.index')
                        ->with('success', 'Quyền đã được cập nhật thành công cho ' . $user->name . '!');
    }
}