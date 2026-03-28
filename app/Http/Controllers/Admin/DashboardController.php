<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\PusakaUser;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalUsers' => User::count(),
            'totalRoles' => Role::count(),
            'totalPermissions' => Permission::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'totalPusakaUsers' => PusakaUser::count(),
            'activePusakaUsers' => PusakaUser::where('is_active', true)->count(),
            'totalLogins' => LoginLog::where('app_name', 'pusakav3')->where('status', 'success')->count(),
            'todayLogins' => LoginLog::where('app_name', 'pusakav3')->where('status', 'success')->whereDate('created_at', today())->count(),
        ];

        return view('admin.dashboard', $data);
    }
}
