<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $workspaceId = $request->session()->get('current_workspace_id');
        $shortcuts = collect();
        if ($workspaceId) {
            $workspace = Workspace::where('user_id', $request->user()->id)->find($workspaceId);
            if ($workspace) {
                $shortcuts = $workspace->shortcuts;
            }
        }

        return view('dashboard.index', compact('shortcuts'));
    }
}
