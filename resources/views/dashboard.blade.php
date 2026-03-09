<x-app-layout>
    <x-slot name="title">Overview</x-slot>

    <div class="mb-8">
        <h2 class="text-xl font-semibold text-slate-900">
            Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
            {{ explode(' ', auth()->user()->name)[0] }} 👋
        </h2>
        <p class="text-slate-500 mt-1">Your simulation environment is ready. Here's an overview.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach([
        ['label' => 'Active Projects', 'value' => $stats['projects'], 'icon' => 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'color' => 'brand'],
        ['label' => 'Total Wallets', 'value' => $stats['wallets'], 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'color' => 'emerald'],
        ['label' => 'Transactions', 'value' => $stats['transactions'], 'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4', 'color' => 'violet'],
        ['label' => 'Webhook Events', 'value' => $stats['webhooks'], 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'amber'],
        ] as $stat)
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <div class="w-9 h-9 rounded-lg bg-{{ $stat['color'] }}-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-{{ $stat['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}" />
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-slate-900">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent transactions --}}
        <div class="card lg:col-span-2">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 text-sm">Recent transactions</h3>
            </div>
            @if($recentTransactions->isEmpty())
            <div class="card-body flex flex-col items-center justify-center py-12 text-center">
                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-slate-700">No transactions yet</p>
                <p class="text-xs text-slate-400 mt-1">Go to a project and run a simulation scenario.</p>
            </div>
            @else
            <div class="overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-surface-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Project</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
                            <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentTransactions as $tx)
                        <tr class="hover:bg-surface-50 transition-colors">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('projects.transactions.show', [$tx->project, $tx]) }}"
                                    class="font-mono text-xs text-brand-600 hover:text-brand-700 bg-brand-50 px-2 py-1 rounded">
                                    {{ Str::limit($tx->reference, 20) }}
                                </a>
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500">{{ $tx->project->name }}</td>
                            <td class="px-6 py-3.5">
                                <span class="{{ $tx->type->badgeClass() }} badge">{{ $tx->type->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
                                {{ $tx->wallet->formatAmount($tx->amount) }}
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="{{ $tx->status->badgeClass() }} badge">{{ $tx->status->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right text-xs text-slate-400">
                                {{ $tx->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Projects quick list --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-slate-900 text-sm">Your projects</h3>
                <a href="{{ route('projects.create') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">+ New</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse(auth()->user()->projects()->active()->latest()->take(5)->get() as $p)
                <a href="{{ route('projects.show', $p) }}"
                    class="flex items-center gap-3 px-6 py-3.5 hover:bg-surface-50 transition-colors group">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                        style="background-color: {{ $p->color }}">
                        {{ strtoupper(substr($p->name, 0, 1)) }}
                    </div>
                    <p class="text-sm font-medium text-slate-700 group-hover:text-brand-700 transition-colors flex-1 truncate">
                        {{ $p->name }}
                    </p>
                    <span class="{{ $p->environment === 'staging' ? 'badge-yellow' : 'badge-blue' }} badge text-xs">
                        {{ $p->environmentLabel() }}
                    </span>
                </a>
                @empty
                <div class="card-body text-center py-8">
                    <p class="text-sm text-slate-400">No projects yet.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Getting started --}}
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold text-slate-900 text-sm">Getting started</h3>
            </div>
            <div class="card-body space-y-4">
                @php
                $hasProject = $stats['projects'] > 0;
                $hasWallet = $stats['wallets'] > 0;
                $hasTx = $stats['transactions'] > 0;
                @endphp
                @foreach([
                ['title' => 'Create a project', 'desc' => 'Projects are isolated simulation environments.', 'done' => $hasProject],
                ['title' => 'Add a wallet', 'desc' => 'Create NGN, USD, KES, or GHS wallets.', 'done' => $hasWallet],
                ['title' => 'Run a scenario', 'desc' => 'Trigger funding, transfers, or failures.', 'done' => $hasTx],
                ['title' => 'Inspect the ledger', 'desc' => 'See every debit and credit in real time.', 'done' => $hasTx],
                ] as $i => $item)
                <div class="flex items-start gap-4">
                    <div class="w-7 h-7 rounded-full border-2 flex items-center justify-center flex-shrink-0 mt-0.5
                        {{ $item['done'] ? 'bg-emerald-500 border-emerald-500' : 'border-slate-200' }}">
                        @if($item['done'])
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        @else
                        <span class="text-xs text-slate-400 font-medium">0{{ $i + 1 }}</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium {{ $item['done'] ? 'text-slate-400 line-through' : 'text-slate-900' }}">
                            {{ $item['title'] }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $item['desc'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>
