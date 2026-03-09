<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
  public function index(Request $request)
  {
    $user = $request->user();

    $projectIds = $user->projects()->pluck('id');
    $walletIds  = Wallet::whereIn('project_id', $projectIds)->pluck('id');

    $webhookCount = \App\Models\WebhookEvent::whereIn('project_id', $projectIds)->count();

    $stats = [
      'projects'     => $user->projects()->active()->count(),
      'wallets'      => $walletIds->count(),
      'transactions' => Transaction::whereIn('project_id', $projectIds)->count(),
      'webhooks'     => $webhookCount,
    ];

    $recentTransactions = Transaction::whereIn('project_id', $projectIds)
      ->with(['wallet', 'project'])
      ->latest()
      ->take(8)
      ->get();

    return view('dashboard', compact('stats', 'recentTransactions'));
  }
}
