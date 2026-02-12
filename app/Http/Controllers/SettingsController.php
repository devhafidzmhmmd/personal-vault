<?php

namespace App\Http\Controllers;

use App\Services\VaultCryptoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(
        private VaultCryptoService $vaultCrypto
    ) {}

    public function index(): RedirectResponse
    {
        return redirect()->route('settings.master-password');
    }

    public function masterPassword(): View
    {
        return view('settings.master-password');
    }

    public function updateMasterPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();
        if (! Hash::check($request->input('current_password'), $user->master_password_hash)) {
            return back()->withErrors(['current_password' => __('Master password saat ini salah.')]);
        }

        $newSalt = $this->vaultCrypto->generateSalt();
        $newHash = Hash::make($request->input('password'));
        $newKey = $this->vaultCrypto->deriveKey($request->input('password'), $newSalt);

        $oldKey = base64_decode($request->session()->get('vault_key'));
        foreach ($user->workspaces as $workspace) {
            foreach ($workspace->passwords as $password) {
                $plain = $this->vaultCrypto->decrypt($password->getRawOriginal('password_encrypted'), $oldKey);
                $password->password_encrypted = $this->vaultCrypto->encrypt($plain, $newKey);
                $password->save();
            }
        }

        $user->master_password_hash = $newHash;
        $user->master_password_salt = $newSalt;
        $user->save();

        $request->session()->put('vault_key', base64_encode($newKey));

        return back()->with('success', __('Master password berhasil diubah.'));
    }
}
