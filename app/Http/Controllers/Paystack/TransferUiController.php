<?php

namespace App\Http\Controllers\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransfer;
use App\Models\Project;
use Illuminate\Http\Request;

class TransferUiController extends Controller
{
  public function index(Request $request, Project $project)
  {
    $this->authorize('view', $project);

    $query = PaystackTransfer::where('project_id', $project->id)
      ->latest();

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $transfers = $query->paginate(20)->withQueryString();

    return view(
      'paystack.transfers.index',
      compact('project', 'transfers')
    );
  }

  public function show(Request $request, Project $project, string $reference)
  {
    $this->authorize('view', $project);

    $transfer = PaystackTransfer::where('project_id', $project->id)
      ->where('reference', $reference)
      ->firstOrFail();

    return view(
      'paystack.transfers.show',
      compact('project', 'transfer')
    );
  }
}
