<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment {{ $shouldFail ? 'Failed' : 'Successful' }} — DevWallet</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#f6f9fc] flex items-center justify-center font-sans p-4">
  <div class="w-full max-w-sm bg-white rounded-2xl border border-slate-200 p-8 text-center">
    @if($shouldFail)
    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
      <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M6 18L18 6M6 6l12 12" />
      </svg>
    </div>
    <h2 class="text-lg font-bold text-slate-900 mb-1">Your card was declined</h2>
    <p class="text-sm text-slate-500">The payment could not be processed.</p>
    @else
    <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
      <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M5 13l4 4L19 7" />
      </svg>
    </div>
    <h2 class="text-lg font-bold text-slate-900 mb-1">Payment successful</h2>
    <p class="text-sm text-slate-500">
      {{ strtoupper($tx->currency) }} {{ number_format($tx->amount / 100, 2) }} received.
    </p>
    @endif
    <p class="font-mono text-xs text-slate-400 mt-4">{{ Str::limit($tx->reference, 32) }}</p>
  </div>
</body>

</html>
