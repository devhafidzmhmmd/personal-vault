<?php

namespace App\Providers;

use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $currentWorkspace = null;
            $sidebarWorkspaces = collect();

            if (Auth::check()) {
                $workspaceId = session('current_workspace_id');
                if ($workspaceId) {
                    $currentWorkspace = Workspace::where('user_id', Auth::id())->find($workspaceId);
                }
                $sidebarWorkspaces = Auth::user()->workspaces()->orderBy('name')->get();
            }

            $view->with(compact('currentWorkspace', 'sidebarWorkspaces'));
        });
    }
}
