<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromanSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('settings.proman', [
            'promanUsername' => $user->proman_username ?? '',
            'promanApiKey' => $user->proman_api_key ?? '',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'proman_username' => ['required', 'string', 'max:255'],
            'proman_password' => ['nullable', 'string', 'max:255'],
            'proman_api_key' => ['required', 'string', 'max:255'],
        ]);

        $data = [
            'proman_username' => $validated['proman_username'],
            'proman_api_key' => $validated['proman_api_key'],
        ];
        if (! empty($validated['proman_password'])) {
            $data['proman_password'] = $validated['proman_password'];
        }

        $request->user()->update($data);

        return redirect()->route('settings.proman')->with('success', __('Pengaturan Proman berhasil disimpan.'));
    }
}
