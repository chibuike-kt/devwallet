<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stripe Checkout — DevWallet Sandbox</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f6f9fc] flex items-center justify-center font-sans p-4">

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
      <div class="bg-[#635BFF]/10 border-b border-[#635BFF]/20 px-6 py-5">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-[#635BFF]/20 flex items-center
                            justify-center">
            <svg class="w-5 h-5 text-[#635BFF]" viewBox="0 0 28 28" fill="currentColor">
              <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409
                                 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858
                                 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165
                                 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757
                                 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219
                                 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84
                                 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9
                                 5.555C4.786 23.819 7.373 24.99 11.11 24.99c2.61 0
                                 4.73-.59 6.265-1.754 1.665-1.275 2.507-3.155
                                 2.507-5.496 0-4.049-2.466-5.771-5.906-7.59z" />
            </svg>
          </div>
          <div>
            <p class="text-xs font-medium text-slate-500">Stripe · Sandbox</p>
            <p class="text-sm font-semibold text-slate-900">{{ $tx->project->name }}</p>
          </div>
        </div>
      </div>

      {{-- Amount --}}
      <div class="px-6 pt-6 pb-4 text-center border-b border-slate-100">
        <p class="text-3xl font-bold text-slate-900">
          {{ strtoupper($tx->currency) }}
          {{ number_format($tx->amount / 100, 2) }}
        </p>
        <p class="text-sm text-slate-500 mt-1">{{ $tx->customer?->email }}</p>
        <p class="text-xs text-slate-400 mt-0.5 font-mono">
          {{ Str::limit($tx->reference, 28) }}
        </p>
      </div>

      {{-- Simulated card --}}
      <div class="px-6 py-5 space-y-3">
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">
            Card information
          </label>
          <div class="border border-slate-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2.5 text-sm font-mono text-slate-400 bg-slate-50
                                border-b border-slate-200">
              4242 4242 4242 4242
            </div>
            <div class="grid grid-cols-2">
              <div class="px-3 py-2.5 text-sm font-mono text-slate-400 bg-slate-50
                                    border-r border-slate-200">
                12 / 30
              </div>
              <div class="px-3 py-2.5 text-sm font-mono text-slate-400 bg-slate-50">
                424
              </div>
            </div>
          </div>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-500 mb-1">
            Name on card
          </label>
          <div class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm
                            text-slate-400 bg-slate-50">
            {{ $tx->customer?->fullName() ?? 'Test User' }}
          </div>
        </div>
        <p class="text-xs text-slate-400 text-center">
          This is a simulated card — no real payment will be made.
        </p>
      </div>

      {{-- Actions --}}
      <div class="px-6 pb-6 space-y-2">
        <form method="POST" action="/api/stripe/v1/checkout/{{ $tx->reference }}">
          @csrf
          <input type="hidden" name="action" value="pay">
          <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">
          <button type="submit"
            class="w-full bg-[#635BFF] hover:bg-[#5851ea] text-white font-semibold
                       py-3 rounded-xl transition-colors text-sm">
            Pay {{ strtoupper($tx->currency) }} {{ number_format($tx->amount / 100, 2) }}
          </button>
        </form>

        <form method="POST" action="/api/stripe/v1/checkout/{{ $tx->reference }}">
          @csrf
          <input type="hidden" name="action" value="fail">
          <input type="hidden" name="callback_url" value="{{ $callbackUrl }}">
          <button type="submit"
            class="w-full bg-white hover:bg-red-50 text-red-500 font-semibold
                       py-3 rounded-xl transition-colors text-sm border border-red-200">
            Simulate failure
          </button>
        </form>
      </div>

    </div>

    <p class="text-center text-xs text-slate-400 mt-4">
      Powered by <span class="font-semibold text-[#635BFF]">Stripe</span> · Test mode
    </p>

  </div>

</body>

</html>
