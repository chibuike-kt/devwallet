<?php

namespace App\Http\Controllers\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackCustomer;
use App\Models\Project;
use Illuminate\Http\Request;

class CustomerUiController extends Controller
{
  public function index(Request $request, Project $project)
  {
    $this->authorize('view', $project);

    $customers = PaystackCustomer::where('project_id', $project->id)
      ->withCount('transactions')
      ->latest()
      ->paginate(20)
      ->withQueryString();

    return view(
      'paystack.customers.index',
      compact('project', 'customers')
    );
  }

  public function show(Request $request, Project $project, string $code)
  {
    $this->authorize('view', $project);

    $customer = PaystackCustomer::where('project_id', $project->id)
      ->where('customer_code', $code)
      ->with(['transactions' => fn($q) => $q->latest()->take(20)])
      ->firstOrFail();

    return view(
      'paystack.customers.show',
      compact('project', 'customer')
    );
  }
}
