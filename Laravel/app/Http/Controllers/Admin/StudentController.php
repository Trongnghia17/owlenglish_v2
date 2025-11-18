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
        $messages = [
            'name.required' => 'Tên học sinh không được để trống.',
            'name.max' => 'Tên học sinh không được vượt quá 191 ký tự.',

            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 191 ký tự.',
            'email.unique' => 'Email này đã tồn tại trong hệ thống.',

            'phone.max' => 'Số điện thoại không được vượt quá 30 ký tự.',
            'phone.unique' => 'Số điện thoại này đã tồn tại.',
            'password.min' => 'Mật khẩu phải chứa ít nhất 6 ký tự.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')],
            'password' => ['nullable', 'string', 'min:6'],
        ], $messages);

        if (empty($data['password'])) {
            $data['password'] = 'password123';
        }

        $data['password'] = Hash::make($data['password']);
        $data['role_id'] = 6;

        DB::transaction(function () use ($data) {
            User::create($data);
        });

        return redirect()->route('admin.students.index')->with('success', 'Tạo học sinh thành công.');
    }


    public function edit(User $student)
    {
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

        $messages = [
            'name.required' => 'Tên học sinh không được để trống.',
            'name.max' => 'Tên học sinh không được vượt quá 191 ký tự.',

            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 191 ký tự.',
            'email.unique' => 'Email này đã được sử dụng.',

            'phone.max' => 'Số điện thoại không được vượt quá 30 ký tự.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',

            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191', Rule::unique('users', 'email')->ignore($student->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($student->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ], $messages);

        // Nếu có nhập mật khẩu → hash
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // Không đổi mật khẩu
        }

        DB::transaction(function () use ($student, $data) {
            $student->update($data);
        });

        return redirect()->route('admin.students.index')->with('success', 'Cập nhật học sinh thành công.');
    }


    public function destroy(User $student)
    {
        if ($student->role_id != 6) {
            abort(404);
        }

        DB::transaction(function () use ($student) {
            $student->delete();
        });

        return redirect()->route('admin.students.index')->with('success', 'Xoá học sinh thành công.');
    }
}
