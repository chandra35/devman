<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index()
    {
        return view('admin.login-logs.index');
    }

    public function data(Request $request)
    {
        $query = LoginLog::with('user');

        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('app_name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('device_info', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('app_name')) {
            $query->where('app_name', $request->app_name);
        }

        // Filter rentang tanggal
        if ($request->filled('date_range')) {
            $days = match($request->date_range) {
                '7days'   => 7,
                '1month'  => 30,
                '3months' => 90,
                default   => null,
            };
            if ($days) {
                $query->where('created_at', '>=', now()->subDays($days));
            }
        }

        $totalFiltered = $query->count();
        $totalData = LoginLog::count();

        $orderColumn = $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc');
        $columns = ['username', 'app_name', 'ip_address', 'device_info', 'status', 'created_at'];
        $orderBy = in_array($columns[$orderColumn] ?? '', ['username', 'app_name', 'ip_address', 'device_info', 'status', 'created_at'])
            ? $columns[$orderColumn]
            : 'created_at';
        $query->orderBy($orderBy, $orderDir);

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        if ($length != -1) {
            $query->skip($start)->take($length);
        }

        $logs = $query->get();

        $data = $logs->map(function ($log) {
            return [
                'username'    => e($log->username),
                'user_name'   => $log->user ? e($log->user->name) : '-',
                'app_name'    => '<span class="badge badge-info">' . e($log->app_name) . '</span>',
                'ip_address'  => e($log->ip_address ?? '-'),
                'device_info' => e($log->device_info ?? '-'),
                'status'      => $log->status === 'success'
                    ? '<span class="badge badge-success">Berhasil</span>'
                    : '<span class="badge badge-danger">Gagal</span>',
                'notes'       => e($log->notes ?? '-'),
                'created_at'  => $log->created_at
                    ? $log->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s')
                    : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }
}
