<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class PromanService
{
    public function login(string $username, string $password): string
    {
        $response = Http::asForm()
            ->withHeaders([
                'app-key' => config('services.proman.app_key'),
            ])
            ->post(config('services.proman.auth_url'), [
                'username' => $username,
                'password' => $password,
            ]);

        $response->throw();

        $data = $response->json();
        if (($data['status'] ?? '') !== 'success' || empty($data['token'])) {
            throw new \RuntimeException($data['msg'] ?? 'Login gagal');
        }

        return $data['token'];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function submitTask(string $apiKey, string $ssoToken, array $payload): array
    {
        $response = Http::withHeaders([
            'token' => $apiKey,
            'sso-cookie' => $ssoToken,
            'Content-Type' => 'application/json',
        ])->post(config('services.proman.submit_url'), $payload);

        $response->throw();

        return $response->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function updateProgress(string $apiKey, string $ssoToken, string $idTask, array $payload = []): array
    {
        $body = array_merge([
            'id_task' => $idTask,
            'progress' => 100,
            'id_status_task' => 4,
        ], $payload);

        $response = Http::withHeaders([
            'token' => $apiKey,
            'sso-cookie' => $ssoToken,
            'Content-Type' => 'application/json',
        ])->post(config('services.proman.update_progress_url'), $body);

        $response->throw();

        return $response->json();
    }
}
