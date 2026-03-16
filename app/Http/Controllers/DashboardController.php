<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\PaystackTransaction;

class DashboardController extends Controller
{
  public function index(Request $request, Project $project)
  {
    $this->authorize('view', $project);

    session(['active_project_id' => $project->id]);

    // Auto-abandon stale initialized transactions
    PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'initialized')
      ->where('created_at', '<', now()->subMinutes(30))
      ->update(['status' => 'abandoned']);

    $totalVolume = PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'success')
      ->sum('amount');

    $user = $request->user();

    $activeProject = session('active_project_id')
      ? Project::find(session('active_project_id'))
      : $user->projects()->active()->first();

    if ($activeProject) {
      session(['active_project_id' => $activeProject->id]);
      return redirect()->route('projects.paystack.overview', $activeProject);
    }

    // No projects yet — show the old dashboard
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
