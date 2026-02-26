<?php

use App\Http\Controllers\CustomEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShortcutController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceSelectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// After login: workspace select, then master password / unlock
Route::middleware('auth')->group(function () {
    Route::get('workspace/select', [WorkspaceSelectController::class, 'index'])->name('workspace.select');
    Route::get('workspace/choose/{workspace}', [WorkspaceSelectController::class, 'choose'])->name('workspace.choose');
    Route::post('workspace/create-from-select', [WorkspaceSelectController::class, 'store'])->name('workspace.create-from-select');

    Route::get('master-password/set', [MasterPasswordController::class, 'showSetForm'])->name('master-password.set');
    Route::post('master-password/set', [MasterPasswordController::class, 'set'])->name('master-password.store');
    Route::get('vault/unlock', [MasterPasswordController::class, 'showUnlockForm'])->name('vault.unlock');
    Route::post('vault/unlock', [MasterPasswordController::class, 'unlock'])->name('vault.unlock.store');
    Route::post('vault/lock', [MasterPasswordController::class, 'lock'])->name('vault.lock');
});

// Vault routes (auth + workspace selected + master password set + unlocked)
Route::middleware(['auth', 'verified', 'vault.unlocked'])->group(function () {
    Route::post('workspace/switch', [WorkspaceSelectController::class, 'switch'])->name('workspace.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/master-password', [SettingsController::class, 'masterPassword'])->name('settings.master-password');
    Route::put('/settings/master-password', [SettingsController::class, 'updateMasterPassword'])->name('settings.master-password.update');
    Route::resource('settings/workspace', WorkspaceController::class)->except(['show'])->names('settings.workspace');

    Route::post('passwords/{password}/reveal', [PasswordController::class, 'reveal'])->name('passwords.reveal');
    Route::resource('passwords', PasswordController::class)->except(['show']);
    Route::resource('shortcuts', ShortcutController::class)->except(['show']);
    Route::patch('todos/{todo}/status', [TodoController::class, 'updateStatus'])->name('todos.update-status');
    Route::resource('todos', TodoController::class)->except(['show']);
    Route::resource('custom-events', CustomEventController::class)->except(['show', 'index']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
