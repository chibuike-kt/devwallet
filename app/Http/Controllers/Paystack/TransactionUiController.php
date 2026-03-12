<?php

namespace App\Http\Controllers\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Models\Project;
use Illuminate\Http\Request;

class TransactionUiController extends Controller
{
  public function index(Request $request, Project $project)
  {
    $this->authorize('view', $project);

    $query = PaystackTransaction::where('project_id', $project->id)
      ->with('customer')
      ->latest();

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    if ($request->filled('search')) {
      $query->where(function ($q) use ($request) {
        $q->where('reference', 'like', "%{$request->search}%")
          ->orWhereHas(
            'customer',
            fn($c) =>
            $c->where('email', 'like', "%{$request->search}%")
          );
      });
    }

    $transactions = $query->paginate(20)->withQueryString();

    return view(
      'paystack.transactions.index',
      compact('project', 'transactions')
    );
  }

  public function show(Request $request, Project $project, string $reference)
  {
    $this->authorize('view', $project);

    $transaction = PaystackTransaction::where('project_id', $project->id)
      ->where('reference', $reference)
      ->with(['customer', 'refunds'])
      ->firstOrFail();

    return view(
      'paystack.transactions.show',
      compact('project', 'transaction')
    );
  }
}
