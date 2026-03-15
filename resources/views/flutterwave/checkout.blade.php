<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Flutterwave Checkout — DevWallet Sandbox</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 flex items-center justify-center font-sans p-4">

  <div class="w-full max-w-sm">

    {{-- Sandbox banner --}}
    <div class="flex items-center justify-center gap-2 mb-6">
      <div class="w-5 h-5 rounded bg-brand-500 flex items-center justify-center">
        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
            d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
      </div>
      <span class="text-sm font-semibold text-slate-600">DevWallet Sandbox</span>
      <span class="text-xs bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full font-medium">
        TEST MODE
      </span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

      {{-- Header --}}
      <div class="bg-[#F5A623]/10 border-b border-[#F5A623]/20 px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#F5A623]/20 flex items-center justify-center">
            <span class="text-[#F5A623] font-bold text-lg">f</span>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500">Flutterwave · Sandbox</p>
            <p class="text-sm font-semibold text-slate-900">{{ $tx->project->name }}</p>
          </div>
        </div>
      </div>

      {{-- Amount --}}
      <div class="px-6 pt-6 pb-4 text-center border-b border-slate-100">
        <p class="text-3xl font-bold text-slate-900">
          {{ $tx->currency }} {{ number_format($tx->amount / 100, 2) }}
        </p>
        <p class="text-sm text-slate-500 mt-1">{{ $tx->customer?->email }}</p>
        <p class="text-xs text-slate-400 mt-0.5 font-mono">Ref: {{ $tx->reference }}</p>
      </div>

      {{-- Simulated card --}}
      <div class="px-6 py-5 space-y-3">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">Card number</label>
          <div class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm
                            font-mono text-slate-400 bg-slate-50">
            5531 8866 5214 2950
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Expiry</label>
            <div class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm
                                font-mono text-slate-400 bg-slate-50">09/32</div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">CVV</label>
            <div class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm
                                font-mono text-slate-400 bg-slate-50">564</div>
          </div>
        </div>
        <p class="text-xs text-slate-400 text-center">
          This is a simulated card — no real payment will be made.
        </p>
      </div>

      {{-- Actions --}}
      <div class="px-6 pb-6 space-y-2">
        <form method="POST" action="/api/flutterwave/v3/checkout/{{ $tx->reference }}">
          @csrf
          <input type="hidden" name="action" value="pay">
          <button type="submit"
            class="w-full bg-[#F5A623] hover:bg-[#e09520] text-white font-semibold
                               py-3 rounded-xl transition-colors text-sm">
            Pay {{ $tx->currency }} {{ number_format($tx->amount / 100, 2) }}
          </button>
        </form>

        <form method="POST" action="/api/flutterwave/v3/checkout/{{ $tx->reference }}">
          @csrf
          <input type="hidden" name="action" value="fail">
          <button type="submit"
            class="w-full bg-white hover:bg-red-50 text-red-500 font-semibold
                               py-3 rounded-xl transition-colors text-sm border border-red-200">
            Simulate failure
          </button>
        </form>
      </div>

    </div>

    <p class="text-center text-xs text-slate-400 mt-4">
      Secured by <span class="font-semibold">Flutterwave</span> · Sandbox mode
    </p>

  </div>

</body>

</html>
