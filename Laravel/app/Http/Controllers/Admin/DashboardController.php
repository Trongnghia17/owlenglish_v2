<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // Get role statistics
        $roleStats = collect(User::ROLE_NAMES)->map(function ($roleName, $roleId) {
            return [
                'role' => $roleName,
                'count' => User::where('role', $roleId)->count(),
                'color' => $this->getRoleColor($roleId)
            ];
        })->values();

        // Role distribution for charts
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        return view('admin.dashboard', compact('roleStats', 'totalUsers', 'activeUsers', 'inactiveUsers'));
    }

    private function getRoleColor($roleId)
    {
        $colors = [
            0 => 'danger',  // Admin
            1 => 'success', // Teacher Teaching
            2 => 'success', // Teacher Grading
            3 => 'success', // Teacher Content
            4 => 'warning', // Student Care
            5 => 'warning', // Assistant Content
            6 => 'primary', // Student Center
            7 => 'info',    // Student Visitor
        ];

        return $colors[$roleId] ?? 'secondary';
    }
}