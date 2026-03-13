<?php

namespace App\Http\Controllers\Api\Paystack;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Services\PaystackResponseService;
use App\Services\PaystackWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
  public function __construct(
    protected PaystackResponseService $response,
    protected PaystackWebhookService  $webhooks,
  ) {}

  /**
   * Show the simulated Paystack checkout page.
   */
  public function show(string $reference)
  {
    $tx = PaystackTransaction::where('reference', $reference)
      ->with('customer')
      ->firstOrFail();

    return view('paystack.checkout', compact('tx'));
  }

  /**
   * Simulate payment — complete the transaction and redirect back.
   */
  public function pay(Request $request, string $reference)
  {
    $tx = PaystackTransaction::where('reference', $reference)
      ->with('customer')
      ->firstOrFail();

    $shouldFail = $request->input('action') === 'fail'
      || $tx->force_fail
      || $tx->project->shouldSimulateFail();

    $tx->update([
      'status'             => $shouldFail ? 'failed' : 'success',
      'gateway_response'   => $shouldFail ? 'Declined' : 'Approved',
      'authorization_code' => 'AUTH_' . strtolower(Str::random(8)),
      'card_type'          => 'visa',
      'last4'              => (string) rand(1000, 9999),
      'exp_month'          => '12',
      'exp_year'           => '2030',
      'bank'               => 'TEST BANK',
      'paid_at'            => $shouldFail ? null : now(),
    ]);

    $tx->refresh()->load('customer');

    if (!$shouldFail) {
      $this->webhooks->fireChargeSuccess($tx);
    }

    // Redirect back to callback URL
    $callbackUrl = $tx->callback_url;

    if (!$callbackUrl) {
      return view('paystack.checkout-result', compact('tx', 'shouldFail'));
    }

    $separator = str_contains($callbackUrl, '?') ? '&' : '?';

    return redirect($callbackUrl . $separator . 'reference=' . $reference
      . '&trxref=' . $reference);
  }
}
