<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Http\Controllers\Controller;
use App\Models\PaystackTransaction;
use App\Services\PaystackWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
  public function __construct(
    protected PaystackWebhookService $webhooks
  ) {}

  public function show(Request $request, string $reference)
  {
    $tx = PaystackTransaction::where('reference', $reference)
      ->with('customer')
      ->firstOrFail();

    $callbackUrl = $request->query('callback');

    return view('stripe.checkout', compact('tx', 'callbackUrl'));
  }

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
      'gateway_response'   => $shouldFail ? 'Your card was declined.' : 'succeeded',
      'authorization_code' => 'AUTH_' . strtolower(Str::random(8)),
      'card_type'          => 'visa',
      'last4'              => '4242',
      'exp_month'          => '12',
      'exp_year'           => '2030',
      'bank'               => 'TEST BANK',
      'paid_at'            => $shouldFail ? null : now(),
    ]);

    $tx->refresh()->load('customer');

    if (!$shouldFail) {
      $this->webhooks->fireChargeSuccess($tx);
    }

    // Use callback from hidden form field
    $callbackUrl = $request->input('callback_url') ?? $tx->callback_url;

    if (!$callbackUrl) {
      return view('stripe.checkout-result', compact('tx', 'shouldFail'));
    }

    $separator = str_contains($callbackUrl, '?') ? '&' : '?';

    return redirect($callbackUrl . $separator
      . 'payment_intent=' . $reference
      . '&redirect_status=' . ($shouldFail ? 'failed' : 'succeeded'));
  }
}
