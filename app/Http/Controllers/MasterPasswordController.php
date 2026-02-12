<?php

namespace App\Http\Controllers;

use App\Services\VaultCryptoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class MasterPasswordController extends Controller
{
    public function __construct(
        private VaultCryptoService $vaultCrypto
    ) {}

    /**
     * Show form to set master password (first time).
     */
    public function showSetForm(): View|RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasMasterPassword()) {
            return redirect()->route('vault.unlock');
        }

        return view('auth.set-master-password');
    }

    /**
     * Store master password (first time).
     */
    public function set(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasMasterPassword()) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $salt = $this->vaultCrypto->generateSalt();
        $masterPassword = $request->input('password');
        $hash = Hash::make($masterPassword);

        $user->master_password_hash = $hash;
        $user->master_password_salt = $salt;
        $user->save();

        $key = $this->vaultCrypto->deriveKey($masterPassword, $salt);
        $request->session()->put('vault_key', base64_encode($key));

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show unlock form.
     */
    public function showUnlockForm(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user->hasMasterPassword()) {
            return redirect()->route('master-password.set');
        }
        if ($request->session()->has('vault_key')) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.unlock');
    }

    /**
     * Unlock vault with master password.
     */
    public function unlock(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->hasMasterPassword()) {
            return redirect()->route('master-password.set');
        }

        $request->validate([
            'password' => ['required'],
        ]);

        if (! Hash::check($request->input('password'), $user->master_password_hash)) {
            return back()->withErrors(['password' => __('Password salah.')]);
        }

        $key = $this->vaultCrypto->deriveKey($request->input('password'), $user->master_password_salt);
        $request->session()->put('vault_key', base64_encode($key));

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Lock vault (clear key from session).
     */
    public function lock(Request $request): RedirectResponse
    {
        $request->session()->forget('vault_key');

        return redirect()->route('vault.unlock');
    }
}
