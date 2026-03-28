<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KemenagNipService
{
    protected string $apiUrl;
    protected ?string $bearerToken;

    public function __construct()
    {
        $this->apiUrl = config('services.kemenag.api_url', 'https://be-pintar.kemenag.go.id/api/v1');
        $this->bearerToken = config('services.kemenag.bearer_token');
    }

    public function cekNip(string $nip): array
    {
        if (empty($this->bearerToken)) {
            return [
                'success' => false,
                'message' => 'Token API Kemenag belum dikonfigurasi (KEMENAG_BEARER_TOKEN)',
            ];
        }

        if (!preg_match('/^\d{18}$/', $nip)) {
            return [
                'success' => false,
                'message' => 'NIP harus 18 digit angka',
            ];
        }

        try {
            $http = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->bearerToken,
                    'Origin' => 'https://pintar.kemenag.go.id',
                    'Referer' => 'https://pintar.kemenag.go.id/',
                ]);

            if (config('app.env') !== 'production') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post($this->apiUrl . '/cek_nip', [
                'nip' => $nip,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['code']) && $data['code'] == 200 && !empty($data['data'])) {
                    return [
                        'success' => true,
                        'message' => 'Data ditemukan',
                        'data' => $data['data'],
                    ];
                }

                return [
                    'success' => false,
                    'message' => $data['message'] ?? 'NIP tidak ditemukan',
                ];
            }

            if ($response->status() === 401) {
                return [
                    'success' => false,
                    'message' => 'Token API expired atau invalid',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal menghubungi API Kemenag (HTTP ' . $response->status() . ')',
            ];
        } catch (\Exception $e) {
            Log::error('KemenagNipService error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke API Kemenag',
            ];
        }
    }
}
