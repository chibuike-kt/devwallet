<x-app-layout>
    <x-slot name="title">Overview</x-slot>

    {{-- Welcome banner --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-slate-900">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h2>
        <p class="text-slate-500 mt-1">Your simulation environment is ready. Here's an overview.</p>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach([
        ['label' => 'Active Projects', 'value' => '0', 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'color' => 'brand'],
        ['label' => 'Total Wallets', 'value' => '0', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'emerald'],
        ['label' => 'Transactions', 'value' => '0', 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'violet'],
        ['label' => 'Webhook Events', 'value' => '0', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'amber'],
        ] as $stat)
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <div class="w-9 h-9 rounded-lg bg-{{ $stat['color'] }}-50 flex items-center justify-center">
                    <svg class="w-4.5 h-4.5 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Empty state content --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Getting started --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 text-sm">Getting started</h3>
            </div>
            <div class="card-body space-y-4">
                @foreach([
                ['step' => '01', 'title' => 'Create a project', 'desc' => 'Projects are isolated simulation environments.', 'done' => false],
                ['step' => '02', 'title' => 'Add wallets', 'desc' => 'Create NGN, USD, or KES wallets for your test accounts.', 'done' => false],
                ['step' => '03', 'title' => 'Run a scenario', 'desc' => 'Trigger a funding event, transfer, or failure scenario.', 'done' => false],
                ['step' => '04', 'title' => 'Inspect the ledger', 'desc' => 'See every debit and credit created by your simulation.', 'done' => false],
                ] as $item)
                <div class="flex items-start gap-4">
                    <div class="w-7 h-7 rounded-full border-2 {{ $item['done'] ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200' }} flex items-center justify-center flex-shrink-0 mt-0.5">
                        @if($item['done'])
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        @else
                        <span class="text-xs text-slate-400 font-medium">{{ $item['step'] }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $item['title'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $item['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent activity (empty state) --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 text-sm">Recent activity</h3>
            </div>
            <div class="card-body flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">No activity yet</p>
                <p class="text-xs text-slate-400 mt-1">Events will appear here as you run simulations</p>
            </div>
        </div>

    </div>

</x-app-layout>
