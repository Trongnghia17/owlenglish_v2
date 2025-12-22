<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDevice;
use Carbon\Carbon;
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

    public function loginHistory(Request $request)
    {
        $user = $request->user();
        // Lấy device theo user (sắp xếp mới nhất)
        $devices = UserDevice::where('user_id', $user->id)
            ->orderBy('logged_in_at', 'desc')
            ->get();

        $result = $devices->map(function ($device) {
            return [
                'id'          => $device->id,
                'device'      => $device->device_name,
                'actionLabel' => $device->status === 'active'
                    ? 'Đăng xuất'
                    : 'Xóa khỏi thiết bị',
                'loginAt'     => $this->formatDate($device->logged_in_at),
                'lastActive'  => $this->formatDate($device->last_activity_at),
                'location'    => $device->location ?? 'Không xác định',
                'status'      => $device->status,
            ];
        });

        return response()->json($result);
    }

    public function deviceAction(Request $request)
    {
        $request->validate([
            'device_id' => 'required|integer',
            'action'    => 'required|string|in:logout,remove'
        ]);

        $user = $request->user();
        $device = UserDevice::where('id', $request->device_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($request->action === 'logout') {
            $device->update([
                'status' => 'logged_out',
                'last_activity_at' => Carbon::now()->addHours(7),
            ]);
        }

        if ($request->action === 'remove') {
            $device->delete();
        }

        return response()->json(['message' => 'Success']);
    }

    private function formatDate($datetime)
    {
        if (!$datetime) return null;

        return Carbon::parse($datetime)->format('H:i · d/m/Y');
    }
}
