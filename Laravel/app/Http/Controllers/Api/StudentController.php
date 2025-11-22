<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'avatar' => 'nullable|image|max:2048',
            'name' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:32',
        ]);
        DB::transaction(function () use ($request, $user) {

            if ($request->hasFile('avatar')) {
                if ($user->avatar_url) {
                    Storage::disk('public')->delete($user->avatar_url);
                }
                $path = $request->file('avatar')->store('avatars', 'public');
                $user->avatar_url = $path;
            }

            $user->name = $request->input('name', $user->name);
            $user->birthday = $request->input('birthday', $user->birthday);
            $user->save();

            // email
            if ($request->filled('email')) {
                $contact = UserContact::firstOrNew([
                    'user_id' => $user->id,
                    'type' => 'email',
                    'is_primary' => true,
                ]);
                $contact->value = mb_strtolower($request->input('email'));
                $contact->verified_at = $contact->verified_at ?? now();
                $contact->save();
            }

            // phone
            if ($request->filled('phone')) {
                $contact = UserContact::firstOrNew([
                    'user_id' => $user->id,
                    'type' => 'phone',
                    'is_primary' => true,
                ]);
                $contact->value = $request->input('phone');
                $contact->verified_at = $contact->verified_at ?? now();
                $contact->save();
            }
        });

        // lấy lại dữ liệu contacts
        $user = $user->fresh();
        $user->email = UserContact::where('user_id', $user->id)->where('type', 'email')->where('is_primary', true)->value('value');
        $user->phone = UserContact::where('user_id', $user->id)->where('type', 'phone')->where('is_primary', true)->value('value');

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'user' => $user
        ]);
    }
}
