<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\PusakaUser;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Verify apakah NIP diizinkan menggunakan PusakaV3.
     * Dipanggil dari app Flutter sebelum load WebView.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'nip' => 'required|string',
            'device_info' => 'nullable|string',
        ]);

        $logData = [
            'username' => $request->nip,
            'app_name' => 'pusakav3',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_info' => $request->device_info,
        ];

        $pusakaUser = PusakaUser::where('nip', $request->nip)->first();

        // NIP tidak terdaftar
        if (!$pusakaUser) {
            LoginLog::create(array_merge($logData, [
                'status' => 'failed',
                'notes' => 'NIP tidak terdaftar',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'User tidak diizinkan menggunakan aplikasi ini. Hubungi admin.',
            ], 403);
        }

        // NIP terdaftar tapi nonaktif
        if (!$pusakaUser->is_active) {
            LoginLog::create(array_merge($logData, [
                'status' => 'failed',
                'notes' => 'NIP nonaktif',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Akses Anda telah dinonaktifkan. Hubungi admin.',
            ], 403);
        }

        // NIP diizinkan
        LoginLog::create(array_merge($logData, [
            'status' => 'success',
            'notes' => 'Verified: ' . $pusakaUser->name,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Akses diizinkan',
            'data' => [
                'name' => $pusakaUser->name,
                'nip' => $pusakaUser->nip,
            ],
        ]);
    }

    /**
     * Config untuk app (location spoofing dll).
     */
    public function config()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'location' => [
                    'mode' => 'static',
                    'latitude' => -5.120118,
                    'longitude' => 105.328819,
                ],
            ],
        ]);
    }
}
