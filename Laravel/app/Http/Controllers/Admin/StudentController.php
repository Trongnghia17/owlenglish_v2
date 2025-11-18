<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
     public function index(Request $request)
    {
        $q = $request->query('q');
        $perPage = (int) $request->query('per_page', 15);

        $query = User::where('role_id', 6);

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $students = $query->orderBy('name')->paginate($perPage)->withQueryString();
        return view('admin.students.index', compact('students', 'q'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')],
            'birthday' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (empty($data['password'])) {
            $data['password'] = 'password123'; // mặc định, thay đổi nếu cần
        }

        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = 6;

        DB::transaction(function () use ($data) {
            User::create($data);
        });

        return redirect()->route('students.index')->with('success', 'Tạo học sinh thành công.');
    }

    public function edit(User $student)
    {
        // bảo đảm đây là học sinh
        if ($student->role_id != 6) {
            abort(404);
        }

        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        if ($student->role_id != 6) {
            abort(404);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('users', 'email')->ignore($student->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($student->id)],
            'birthday' => ['nullable', 'date'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        DB::transaction(function () use ($student, $data) {
            $student->update($data);
        });

        return redirect()->route('students.index')->with('success', 'Cập nhật học sinh thành công.');
    }

    public function destroy(User $student)
    {
        if ($student->role_id != 6) {
            abort(404);
        }

        DB::transaction(function () use ($student) {
            $student->delete();
        });

        return redirect()->route('students.index')->with('success', 'Xoá học sinh thành công.');
    }
}
