<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AbsenSettingsController;
use App\Http\Controllers\CustomEventController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PasswordPrefixController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromanProjectController;
use App\Http\Controllers\PromanSettingsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ShortcutController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\ToolController;
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
    Route::get('/settings/account-password', [SettingsController::class, 'accountPassword'])->name('settings.account-password');
    Route::get('/settings/absen', [AbsenSettingsController::class, 'index'])->name('settings.absen');
    Route::put('/settings/absen', [AbsenSettingsController::class, 'update'])->name('settings.absen.update');
    Route::get('/settings/proman', [PromanSettingsController::class, 'index'])->name('settings.proman');
    Route::put('/settings/proman', [PromanSettingsController::class, 'update'])->name('settings.proman.update');
    Route::resource('settings/workspace', WorkspaceController::class)->except(['show'])->names('settings.workspace');

    Route::middleware('proman.enabled')->group(function () {
        Route::get('settings/workspace/proman', [PromanProjectController::class, 'index'])->name('settings.workspace.proman.index');
        Route::get('settings/workspace/proman/create', [PromanProjectController::class, 'create'])->name('settings.workspace.proman.create');
        Route::post('settings/workspace/proman', [PromanProjectController::class, 'store'])->name('settings.workspace.proman.store');
        Route::get('settings/workspace/proman/{proman_project}/edit', [PromanProjectController::class, 'edit'])->name('settings.workspace.proman.edit');
        Route::put('settings/workspace/proman/{proman_project}', [PromanProjectController::class, 'update'])->name('settings.workspace.proman.update');
        Route::delete('settings/workspace/proman/{proman_project}', [PromanProjectController::class, 'destroy'])->name('settings.workspace.proman.destroy');
        Route::post('todos/import-json', [TodoController::class, 'importFromJson'])->name('todos.import-json');
        Route::post('todos/batch-assign-project', [TodoController::class, 'batchAssignProject'])->name('todos.batch-assign-project');
        Route::post('todos/batch-schedule-submit', [TodoController::class, 'batchScheduleSubmit'])->name('todos.batch-schedule-submit');
    });

    Route::post('/absen/submit', [AbsenController::class, 'submit'])->name('absen.submit');
    Route::post('/absen/locations', [AbsenController::class, 'storeLocation'])->name('absen.locations.store');

    Route::post('passwords/{password}/reveal', [PasswordController::class, 'reveal'])->name('passwords.reveal');
    Route::resource('passwords', PasswordController::class)->except(['show']);
    Route::resource('password-prefixes', PasswordPrefixController::class)->except(['show']);
    Route::resource('shortcuts', ShortcutController::class)->except(['show']);
    Route::patch('todos/{todo}/status', [TodoController::class, 'updateStatus'])->name('todos.update-status');
    Route::resource('todos', TodoController::class)->except(['show']);
    Route::resource('custom-events', CustomEventController::class)->except(['show', 'index']);

    Route::get('/tools', [ToolController::class, 'index'])->name('tools.index');
    Route::get('/tools/json-to-excel', [ToolController::class, 'jsonToExcel'])->name('tools.json-to-excel');
    Route::post('/tools/json-to-excel', [ToolController::class, 'convertJsonToExcel'])->name('tools.json-to-excel.convert');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
