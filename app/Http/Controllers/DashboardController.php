<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $activeProjectId = session('active_project_id');

    $activeProject = $activeProjectId
      ? Project::where('id', $activeProjectId)
      ->where('user_id', $user->id)
      ->first()
      : null;

    // Fall back to first active project if session is stale or empty
    if (!$activeProject) {
      $activeProject = $user->projects()
        ->where('status', 'active')
        ->first();
    }

    if ($activeProject) {
      session(['active_project_id' => $activeProject->id]);
      return redirect()->route('projects.paystack.overview', $activeProject);
    }

    // No projects yet
    return view('dashboard', [
      'stats' => [
        'projects'     => 0,
        'wallets'      => 0,
        'transactions' => 0,
        'webhooks'     => 0,
      ],
      'recentTransactions' => collect(),
    ]);
  }
}
