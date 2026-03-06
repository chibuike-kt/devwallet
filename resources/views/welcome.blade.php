<x-guest-layout>
    {{-- Hero --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24">
        <div class="max-w-3xl mx-auto text-center">

            {{-- Product badge --}}
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-50 border border-brand-100 mb-8">
                <span class="w-2 h-2 rounded-full bg-brand-500"></span>
                <span class="text-brand-700 text-sm font-medium">Built for African fintech developers</span>
            </div>

            <h1 class="text-5xl font-bold text-slate-900 tracking-tight leading-tight mb-6">
                Simulate payment flows.<br>
                <span class="text-brand-600">Ship with confidence.</span>
            </h1>

            <p class="text-xl text-slate-500 leading-relaxed mb-10 max-w-2xl mx-auto">
                DevWallet is a developer sandbox for African payment infrastructure.
                Test wallet logic, ledgers, transfers, webhooks, and failure scenarios
                without touching real money or waiting on providers.
            </p>

            <div class="flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="btn-primary text-base px-6 py-3">
                    Start simulating free
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="btn-secondary text-base px-6 py-3">
                    Sign in
                </a>
            </div>
        </div>

        {{-- Feature grid --}}
        <div class="mt-24 grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
            ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'title' => 'Wallet Engine', 'desc' => 'Multi-currency wallets with ledger-backed balance accounting. Credits, debits, and balance checks done properly.'],
            ['icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'title' => 'Transaction Flows', 'desc' => 'Simulate successful transfers, failures, timeouts, reversals, and settlement batches in real time.'],
            ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'title' => 'Webhook Delivery', 'desc' => 'Inspect payloads, trigger delivery failures, watch retries happen, and verify your integration handles them.'],
            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'title' => 'Ledger Engine', 'desc' => 'Every balance change creates a ledger entry. Inspectable, consistent, and trustworthy financial records.'],
            ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Scenario Library', 'desc' => 'One-click simulation of frozen accounts, insufficient balance, duplicate requests, and provider timeouts.'],
            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'title' => 'Audit Trail', 'desc' => 'Every operation is logged. See exactly what happened, when, and why. Built for accountability.'],
            ] as $feature)
            <div class="card p-6">
                <div class="w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center mb-4">
                    <svg class="w-5 h-5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}" />
                    </svg>
                </div>
                <h3 class="font-semibold text-slate-900 mb-2">{{ $feature['title'] }}</h3>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>

    </div>
</x-guest-layout>
