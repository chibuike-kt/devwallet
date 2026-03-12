<x-app-layout>
  <x-slot name="title">Transaction</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.paystack.transactions', $project) }}"
      class="hover:text-slate-600 transition-colors">Transactions</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="font-mono text-xs">{{ Str::limit($transaction->reference, 24) }}</span>
  </div>

  {{-- Header card --}}
  <div class="card p-6 mb-6">
    <div class="flex items-start justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <span class="{{ $transaction->statusBadgeClass() }} badge capitalize text-sm px-3 py-1">
            {{ $transaction->status }}
          </span>
          <span class="badge badge-slate capitalize">{{ $transaction->channel }}</span>
        </div>
        <p class="text-3xl font-bold text-slate-900 mt-2">
          ₦{{ number_format($transaction->amount / 100, 2) }}
        </p>
        <p class="font-mono text-xs text-slate-400 mt-1">{{ $transaction->reference }}</p>
      </div>
      <div class="text-right text-sm text-slate-500">
        <p>{{ $transaction->created_at->format('d M Y, H:i') }}</p>
        @if($transaction->paid_at)
        <p class="text-emerald-600 text-xs mt-1">
          Paid {{ $transaction->paid_at->format('d M Y, H:i') }}
        </p>
        @endif
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Transaction details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Transaction details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['Reference', $transaction->reference, true],
        ['Status', ucfirst($transaction->status), false],
        ['Amount', '₦' . number_format($transaction->amount / 100, 2), false],
        ['Channel', ucfirst($transaction->channel), false],
        ['Currency', $transaction->currency, false],
        ['Gateway response', $transaction->gateway_response ?? '—', false],
        ['Created', $transaction->created_at->format('d M Y, H:i:s'), false],
        ['Paid at', $transaction->paid_at?->format('d M Y, H:i:s') ?? '—', false],
        ] as [$label, $value, $mono])
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
          <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800 font-medium' }}">
            {{ $value }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Customer + authorization --}}
    <div class="space-y-5">

      {{-- Customer --}}
      @if($transaction->customer)
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 text-sm">Customer</h3>
          <a href="{{ route('projects.paystack.customers.show', [$project, $transaction->customer->customer_code]) }}"
            class="text-xs text-brand-600 hover:text-brand-700 font-medium">
            View →
          </a>
        </div>
        <div class="card-body space-y-0">
          @foreach([
          ['Email', $transaction->customer->email, false],
          ['Name', $transaction->customer->fullName(), false],
          ['Code', $transaction->customer->customer_code, true],
          ] as [$label, $value, $mono])
          <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
            <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
            <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800' }}">
              {{ $value }}
            </p>
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- Authorization --}}
      @if($transaction->authorization_code)
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Authorization</h3>
        </div>
        <div class="card-body space-y-0">
          @foreach([
          ['Auth code', $transaction->authorization_code, true],
          ['Card type', ucfirst($transaction->card_type ?? 'visa'), false],
          ['Last 4', $transaction->last4 ?? '—', false],
          ['Expires', ($transaction->exp_month ?? '12') . '/' . ($transaction->exp_year ?? '2030'), false],
          ['Bank', $transaction->bank ?? 'TEST BANK', false],
          ] as [$label, $value, $mono])
          <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
            <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
            <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800' }}">
              {{ $value }}
            </p>
          </div>
          @endforeach
        </div>
      </div>
      @endif

    </div>

  </div>

</x-app-layout>
