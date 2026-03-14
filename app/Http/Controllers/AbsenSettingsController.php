<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsenSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('settings.absen', [
            'promanUserId' => $user->proman_user_id ?? '',
            'promanToken' => $user->proman_token ?? '',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'proman_user_id' => ['required', 'string', 'max:255'],
            'proman_token' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->update([
            'proman_user_id' => $validated['proman_user_id'],
            'proman_token' => $validated['proman_token'],
        ]);

        return redirect()->route('settings.absen')->with('success', __('Pengaturan absensi berhasil disimpan.'));
    }
}
