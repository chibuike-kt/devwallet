<?php

namespace App\Http\Controllers\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;
use App\Models\Project;
use Illuminate\Http\Request;

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

    $totalTransactions = PaystackTransaction::where('project_id', $project->id)
      ->count();

    $successCount = PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'success')
      ->count();

    $failedCount = PaystackTransaction::where('project_id', $project->id)
      ->where('status', 'failed')
      ->count();

    $totalTransfers = PaystackTransfer::where('project_id', $project->id)
      ->where('status', 'success')
      ->sum('amount');

    $customerCount = \App\Models\PaystackCustomer::where('project_id', $project->id)
      ->count();

    $recentTransactions = PaystackTransaction::where('project_id', $project->id)
      ->with('customer')
      ->latest()
      ->take(10)
      ->get();

    $successRate = $totalTransactions > 0
      ? round(($successCount / $totalTransactions) * 100, 1)
      : 0;

    return view('paystack.overview', compact(
      'project',
      'totalVolume',
      'totalTransactions',
      'successCount',
      'failedCount',
      'totalTransfers',
      'customerCount',
      'recentTransactions',
      'successRate',
    ));
  }
}
