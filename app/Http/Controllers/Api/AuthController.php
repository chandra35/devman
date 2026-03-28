<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_info' => 'nullable|string',
            'app_name' => 'nullable|string',
        ]);

        $user = User::where('username', $request->username)->first();

        $logData = [
            'username' => $request->username,
            'app_name' => $request->app_name ?? 'pusakav3',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_info' => $request->device_info,
        ];

        if (!$user || !Hash::check($request->password, $user->password)) {
            LoginLog::create(array_merge($logData, [
                'user_id' => $user?->id,
                'status' => 'failed',
                'notes' => 'Invalid credentials',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah',
            ], 401);
        }

        if (!$user->is_active) {
            LoginLog::create(array_merge($logData, [
                'user_id' => $user->id,
                'status' => 'failed',
                'notes' => 'Account inactive',
            ]));

            return response()->json([
                'success' => false,
                'message' => 'Akun tidak aktif',
            ], 403);
        }

        // Revoke old tokens for this app
        $user->tokens()->where('name', $logData['app_name'])->delete();

        // Create new token
        $token = $user->createToken($logData['app_name']);

        // Log successful login
        LoginLog::create(array_merge($logData, [
            'user_id' => $user->id,
            'status' => 'success',
        ]));

        // Build response with kemenag credentials if available
        $response = [
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token->plainTextToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ],
                'kemenag' => null,
            ],
        ];

        if ($user->kemenag_username && $user->kemenag_password) {
            $response['data']['kemenag'] = [
                'username' => $user->kemenag_username,
                'password' => $user->getDecryptedKemenagPassword(),
            ];
        }

        return response()->json($response);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                ],
                'kemenag' => $user->kemenag_username ? [
                    'username' => $user->kemenag_username,
                    'password' => $user->getDecryptedKemenagPassword(),
                ] : null,
            ],
        ]);
    }

    public function config(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'location' => [
                    'mode' => 'static',
                    'latitude' => -5.120118,
                    'longitude' => 105.328819,
                ],
                'kemenag' => $user->kemenag_username ? [
                    'username' => $user->kemenag_username,
                    'password' => $user->getDecryptedKemenagPassword(),
                ] : null,
            ],
        ]);
    }
}
