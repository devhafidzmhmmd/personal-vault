<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAbsenRequest;
use App\Models\AbsenHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AbsenController extends Controller
{
    private const API_BASE_URL = 'https://dcktrp.jakarta.go.id/proman/api/mobile/submit-absen-manual';

    public function submit(SubmitAbsenRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasAbsenSettingsConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('Pengaturan absensi belum lengkap. Silakan atur ID User dan Token di Settings.'),
            ], 422);
        }

        $validated = $request->validated();
        $tglAbsen = $validated['tgl_absen'];

        $history = AbsenHistory::where('user_id', $user->id)
            ->whereDate('tgl_absen', $tglAbsen)
            ->first();

        if (! $history) {
            $history = new AbsenHistory([
                'user_id' => $user->id,
                'tgl_absen' => $tglAbsen,
                'hit_count' => 0,
            ]);
        }

        if ($history->hit_count >= AbsenHistory::MAX_HITS_PER_DAY) {
            return response()->json([
                'success' => false,
                'message' => __('Absensi untuk tanggal ini sudah mencapai batas maksimal 2 kali sehari.'),
            ], 422);
        }

        $keterangan = $validated['keterangan'] ?? strtolower($validated['type']);

        $url = self::API_BASE_URL.'?'.http_build_query([
            'id_user' => $user->proman_user_id,
            'longitude' => $validated['longitude'],
            'latitude' => $validated['latitude'],
            'type' => $validated['type'],
            'keterangan' => $keterangan,
            'tgl_absen' => $tglAbsen,
        ]);

        $response = Http::withHeaders([
            'token' => $user->proman_token,
        ])->post($url);

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => __('Gagal mengirim absensi.').' '.($response->body() ?: $response->reason()),
            ], 502);
        }

        $history->hit_count = $history->hit_count + 1;
        $history->save();

        return response()->json([
            'success' => true,
            'message' => __('Absensi berhasil dikirim.'),
            'hit_count' => $history->hit_count,
        ]);
    }

    public function storeLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $user = $request->user();
        $locations = $user->saved_locations ?? [];
        $locations[] = [
            'name' => $validated['name'],
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
        ];
        $user->saved_locations = $locations;
        $user->save();

        return response()->json([
            'success' => true,
            'locations' => $user->saved_locations,
        ]);
    }
}
