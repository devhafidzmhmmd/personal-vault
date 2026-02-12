<?php

namespace App\Http\Controllers;

use App\Models\Password;
use App\Models\Workspace;
use App\Services\VaultCryptoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function __construct(
        private VaultCryptoService $vaultCrypto
    ) {}

    private function getVaultKey(Request $request): string
    {
        $key = $request->session()->get('vault_key');
        if (! $key) {
            abort(403, 'Vault locked');
        }

        return base64_decode($key);
    }

    private function getCurrentWorkspace(Request $request): ?Workspace
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        if (! $workspaceId) {
            return null;
        }

        return Workspace::where('user_id', $request->user()->id)->find($workspaceId);
    }

    public function index(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $query = Password::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $passwords = $query->get();

        return view('passwords.index', compact('passwords'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        return view('passwords.create', compact('workspace'));
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }

        $request->validate([
            'type' => 'required|in:app,db,server,other',
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string',
            'url' => 'nullable|string|url|max:2048',
            'notes' => 'nullable|string|max:5000',
        ]);

        $key = $this->getVaultKey($request);
        $encrypted = $this->vaultCrypto->encrypt($request->input('password'), $key);

        Password::create([
            'workspace_id' => $workspace->id,
            'type' => $request->input('type'),
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'password_encrypted' => $encrypted,
            'url' => $request->input('url'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('passwords.index')->with('success', __('Password ditambah.'));
    }

    public function edit(Request $request, Password $password): View|RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($password->workspace_id !== $workspace->id || $password->workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $key = $this->getVaultKey($request);
        $decrypted = $this->vaultCrypto->decrypt($password->getRawOriginal('password_encrypted'), $key);

        return view('passwords.edit', [
            'password' => $password,
            'passwordPlain' => $decrypted,
        ]);
    }

    public function update(Request $request, Password $password): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($password->workspace_id !== $workspace->id || $password->workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:app,db,server,other',
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string',
            'url' => 'nullable|string|url|max:2048',
            'notes' => 'nullable|string|max:5000',
        ]);

        $key = $this->getVaultKey($request);
        $encrypted = $request->filled('password')
            ? $this->vaultCrypto->encrypt($request->input('password'), $key)
            : $password->getRawOriginal('password_encrypted');

        $password->update([
            'type' => $request->input('type'),
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'password_encrypted' => $encrypted,
            'url' => $request->input('url'),
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('passwords.index')->with('success', __('Password diperbarui.'));
    }

    public function destroy(Request $request, Password $password): RedirectResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            return redirect()->route('workspace.select');
        }
        if ($password->workspace_id !== $workspace->id || $password->workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $password->delete();

        return redirect()->route('passwords.index')->with('success', __('Password dihapus.'));
    }

    /**
     * Reveal decrypted password for copy-to-clipboard (JSON).
     */
    public function reveal(Request $request, Password $password): JsonResponse
    {
        $workspace = $this->getCurrentWorkspace($request);
        if (! $workspace) {
            abort(403);
        }
        if ($password->workspace_id !== $workspace->id || $password->workspace->user_id !== $request->user()->id) {
            abort(403);
        }

        $key = $this->getVaultKey($request);
        $plain = $this->vaultCrypto->decrypt($password->getRawOriginal('password_encrypted'), $key);

        return response()->json(['password' => $plain]);
    }
}
