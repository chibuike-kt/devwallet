<?php

namespace App\Services;

use App\Models\PaystackCustomer;
use App\Models\PaystackRefund;
use App\Models\PaystackTransaction;
use App\Models\PaystackTransfer;

class PaystackResponseService
{
  // ── Transaction responses ─────────────────────────────────────────────────

  public function initializeResponse(PaystackTransaction $tx, string $baseUrl): array
  {
    return [
      'status'  => true,
      'message' => 'Authorization URL created',
      'data'    => [
        'authorization_url' => "{$baseUrl}/api/paystack/checkout/{$tx->reference}",
        'access_code'       => base64_encode($tx->reference),
        'reference'         => $tx->reference,
      ],
    ];
  }

  public function verifyResponse(PaystackTransaction $tx): array
  {
    return [
      'status'  => true,
      'message' => 'Verification successful',
      'data'    => $this->transactionData($tx),
    ];
  }

  public function transactionResponse(PaystackTransaction $tx): array
  {
    return [
      'status'  => true,
      'message' => 'Transaction retrieved',
      'data'    => $this->transactionData($tx),
    ];
  }

  public function transactionListResponse(
    \Illuminate\Pagination\LengthAwarePaginator $transactions
  ): array {
    return [
      'status'  => true,
      'message' => 'Transactions retrieved',
      'data'    => $transactions->map(
        fn($tx) => $this->transactionData($tx)
      ),
      'meta' => [
        'total'        => $transactions->total(),
        'skipped'      => ($transactions->currentPage() - 1) * $transactions->perPage(),
        'perPage'      => $transactions->perPage(),
        'page'         => $transactions->currentPage(),
        'pageCount'    => $transactions->lastPage(),
      ],
    ];
  }

  // ── Transfer responses ────────────────────────────────────────────────────

  public function transferResponse(PaystackTransfer $transfer): array
  {
    return [
      'status'  => true,
      'message' => 'Transfer requires OTP to continue',
      'data'    => $this->transferData($transfer),
    ];
  }

  public function transferVerifyResponse(PaystackTransfer $transfer): array
  {
    return [
      'status'  => true,
      'message' => 'Transfer retrieved',
      'data'    => $this->transferData($transfer),
    ];
  }

  public function transferListResponse(
    \Illuminate\Pagination\LengthAwarePaginator $transfers
  ): array {
    return [
      'status'  => true,
      'message' => 'Transfers retrieved',
      'data'    => $transfers->map(fn($t) => $this->transferData($t)),
      'meta'    => [
        'total'     => $transfers->total(),
        'page'      => $transfers->currentPage(),
        'pageCount' => $transfers->lastPage(),
        'perPage'   => $transfers->perPage(),
      ],
    ];
  }

  // ── Refund responses ──────────────────────────────────────────────────────

  public function refundResponse(PaystackRefund $refund): array
  {
    return [
      'status'  => true,
      'message' => 'Refund has been queued for processing',
      'data'    => $this->refundData($refund),
    ];
  }

  public function refundListResponse(
    \Illuminate\Pagination\LengthAwarePaginator $refunds
  ): array {
    return [
      'status'  => true,
      'message' => 'Refunds retrieved',
      'data'    => $refunds->map(fn($r) => $this->refundData($r)),
      'meta'    => [
        'total'     => $refunds->total(),
        'page'      => $refunds->currentPage(),
        'pageCount' => $refunds->lastPage(),
        'perPage'   => $refunds->perPage(),
      ],
    ];
  }

  // ── Balance responses ─────────────────────────────────────────────────────

  public function balanceResponse(int $balanceInKobo, string $currency = 'NGN'): array
  {
    return [
      'status'  => true,
      'message' => 'Balances retrieved',
      'data'    => [
        [
          'currency' => $currency,
          'balance'  => $balanceInKobo,
        ],
      ],
    ];
  }

  // ── Customer responses ────────────────────────────────────────────────────

  public function customerResponse(PaystackCustomer $customer): array
  {
    return [
      'status'  => true,
      'message' => 'Customer retrieved',
      'data'    => $this->customerData($customer),
    ];
  }

  public function customerListResponse(
    \Illuminate\Pagination\LengthAwarePaginator $customers
  ): array {
    return [
      'status'  => true,
      'message' => 'Customers retrieved',
      'data'    => $customers->map(fn($c) => $this->customerData($c)),
      'meta'    => [
        'total'     => $customers->total(),
        'page'      => $customers->currentPage(),
        'pageCount' => $customers->lastPage(),
        'perPage'   => $customers->perPage(),
      ],
    ];
  }

  // ── Error response ────────────────────────────────────────────────────────

  public function errorResponse(string $message, int $status = 400): \Illuminate\Http\JsonResponse
  {
    return response()->json([
      'status'  => false,
      'message' => $message,
    ], $status);
  }

  // ── Internal formatters ───────────────────────────────────────────────────

  public function transactionData(PaystackTransaction $tx): array
  {
    return [
      'id'               => $tx->id,
      'domain'           => 'test',
      'status'           => $tx->status,
      'reference'        => $tx->reference,
      'amount'           => $tx->amount,
      'message'          => null,
      'gateway_response' => $tx->gateway_response ?? $this->gatewayResponse($tx->status),
      'paid_at'          => $tx->paid_at?->toIso8601String(),
      'created_at'       => $tx->created_at->toIso8601String(),
      'channel'          => $tx->channel,
      'currency'         => $tx->currency,
      'ip_address'       => '41.242.49.37',
      'metadata'         => $tx->metadata ?? '',
      'customer'         => $tx->customer ? $this->customerData($tx->customer) : null,
      'authorization'    => $tx->authorization_code ? [
        'authorization_code' => $tx->authorization_code,
        'bin'                => '408408',
        'last4'              => $tx->last4 ?? '4081',
        'exp_month'          => $tx->exp_month ?? '12',
        'exp_year'           => $tx->exp_year ?? '2030',
        'channel'            => 'card',
        'card_type'          => $tx->card_type ?? 'visa',
        'bank'               => $tx->bank ?? 'TEST BANK',
        'country_code'       => 'NG',
        'brand'              => $tx->card_type ?? 'visa',
        'reusable'           => true,
        'signature'          => 'SIG_' . strtoupper(substr($tx->reference, 0, 8)),
      ] : null,
      'fees'  => (int) round($tx->amount * 0.015),
      'log'   => null,
      'plan'  => null,
    ];
  }

  public function transferData(PaystackTransfer $transfer): array
  {
    return [
      'id'            => $transfer->id,
      'domain'        => 'test',
      'amount'        => $transfer->amount,
      'currency'      => $transfer->currency,
      'reference'     => $transfer->reference,
      'source'        => 'balance',
      'source_details' => null,
      'reason'        => $transfer->narration ?? '',
      'status'        => $transfer->status,
      'failures'      => null,
      'transfer_code' => $transfer->transfer_code,
      'recipient'     => [
        'name'           => $transfer->recipient_name,
        'account_number' => $transfer->recipient_account_number,
        'bank_code'      => $transfer->recipient_bank_code,
        'bank_name'      => $transfer->recipient_bank_name ?? 'Test Bank',
      ],
      'createdAt'     => $transfer->created_at->toIso8601String(),
      'updatedAt'     => $transfer->updated_at->toIso8601String(),
    ];
  }

  public function refundData(PaystackRefund $refund): array
  {
    return [
      'id'              => $refund->id,
      'domain'          => 'test',
      'transaction'     => $refund->paystack_transaction_id,
      'dispute'         => null,
      'amount'          => $refund->amount,
      'deducted_amount' => null,
      'currency'        => $refund->currency,
      'channel'         => 'card',
      'fully_deducted'  => false,
      'refunded_at'     => $refund->processed_at?->toIso8601String(),
      'expected_at'     => now()->addDays(3)->toIso8601String(),
      'customer_note'   => $refund->customer_note,
      'merchant_note'   => $refund->merchant_note,
      'created_at'      => $refund->created_at->toIso8601String(),
      'updated_at'      => $refund->updated_at->toIso8601String(),
      'status'          => $refund->status,
    ];
  }

  public function customerData(PaystackCustomer $customer): array
  {
    return [
      'id'            => $customer->id,
      'first_name'    => $customer->first_name,
      'last_name'     => $customer->last_name,
      'email'         => $customer->email,
      'phone'         => $customer->phone,
      'customer_code' => $customer->customer_code,
      'metadata'      => $customer->metadata,
      'domain'        => 'test',
      'identified'    => false,
      'createdAt'     => $customer->created_at->toIso8601String(),
      'updatedAt'     => $customer->updated_at->toIso8601String(),
    ];
  }

  // ── Webhook payloads ──────────────────────────────────────────────────────

  public function chargeSuccessPayload(PaystackTransaction $tx): array
  {
    return [
      'event' => 'charge.success',
      'data'  => $this->transactionData($tx),
    ];
  }

  public function transferSuccessPayload(PaystackTransfer $transfer): array
  {
    return [
      'event' => 'transfer.success',
      'data'  => $this->transferData($transfer),
    ];
  }

  public function transferFailedPayload(PaystackTransfer $transfer): array
  {
    return [
      'event' => 'transfer.failed',
      'data'  => $this->transferData($transfer),
    ];
  }

  // ── Helpers ───────────────────────────────────────────────────────────────

  private function gatewayResponse(string $status): string
  {
    return match ($status) {
      'success'  => 'Approved',
      'failed'   => 'Declined',
      'abandoned' => 'Transaction abandoned',
      default    => 'Pending',
    };
  }
}
