<?php

namespace App\Http\Controllers\Api\Flutterwave;

use App\Http\Controllers\Controller;
use App\Models\PaystackCustomer;
use App\Models\PaystackTransaction;
use App\Services\PaystackWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
  public function __construct(
    protected PaystackWebhookService $webhooks
  ) {}

  /**
   * POST /api/flutterwave/v3/payments
   * Mirrors Flutterwave's initiate payment endpoint.
   */
  public function store(Request $request): JsonResponse
  {
    $request->validate([
      'tx_ref'       => ['required', 'string'],
      'amount'       => ['required', 'numeric', 'min:1'],
      'currency'     => ['nullable', 'string'],
      'redirect_url' => ['nullable', 'url'],
      'customer'     => ['required', 'array'],
      'customer.email' => ['required', 'email'],
      'customer.name'  => ['nullable', 'string'],
      'customer.phonenumber' => ['nullable', 'string'],
    ]);

    $project  = $request->_api_project;
    $amount   = (int) round($request->amount * 100);
    $currency = $request->currency ?? 'NGN';

    // Find or create customer
    $customer = PaystackCustomer::firstOrCreate(
      ['project_id' => $project->id, 'email' => $request->customer['email']],
      [
        'customer_code' => 'CUS_' . strtolower(Str::random(12)),
        'first_name'    => explode(' ', $request->customer['name'] ?? '')[0] ?? null,
        'last_name'     => explode(' ', $request->customer['name'] ?? '')[1] ?? null,
        'phone'         => $request->customer['phonenumber'] ?? null,
      ]
    );

    $tx = PaystackTransaction::create([
      'project_id'           => $project->id,
      'paystack_customer_id' => $customer->id,
      'reference'            => $request->tx_ref,
      'status'               => 'initialized',
      'amount'               => $amount,
      'currency'             => $currency,
      'channel'              => 'card',
      'callback_url'         => $request->redirect_url,
    ]);

    return response()->json([
      'status'  => 'success',
      'message' => 'Hosted Link',
      'data'    => [
        'link' => url("/api/flutterwave/v3/checkout/{$tx->reference}"),
      ],
    ]);
  }
}
