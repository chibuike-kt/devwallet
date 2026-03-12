<x-app-layout>
  <x-slot name="title">Overview</x-slot>

  {{-- Header --}}
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Overview</h2>
      <p class="text-slate-400 text-sm mt-0.5">{{ $project->name }} — sandbox environment</p>
    </div>
    <a href="{{ route('projects.paystack.transactions', $project) }}"
      class="btn-primary text-sm">
      View transactions
    </a>
  </div>

  {{-- Metric cards --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <div class="card p-5">
      <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">
        Total Volume
      </p>
      <p class="text-2xl font-bold text-slate-900">
        ₦{{ number_format($totalVolume / 100, 2) }}
      </p>
      <p class="text-xs text-slate-400 mt-1">Successful payments</p>
    </div>

    <div class="card p-5">
      <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">
        Transactions
      </p>
      <p class="text-2xl font-bold text-slate-900">
        {{ number_format($totalTransactions) }}
      </p>
      <div class="flex items-center gap-2 mt-1">
        <span class="text-xs text-emerald-600">{{ $successCount }} success</span>
        <span class="text-slate-300">·</span>
        <span class="text-xs text-red-500">{{ $failedCount }} failed</span>
      </div>
    </div>

    <div class="card p-5">
      <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">
        Success Rate
      </p>
      <p class="text-2xl font-bold {{ $successRate >= 80 ? 'text-emerald-600' : 'text-red-500' }}">
        {{ $successRate }}%
      </p>
      <p class="text-xs text-slate-400 mt-1">Of all transactions</p>
    </div>

    <div class="card p-5">
      <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-3">
        Customers
      </p>
      <p class="text-2xl font-bold text-slate-900">
        {{ number_format($customerCount) }}
      </p>
      <p class="text-xs text-slate-400 mt-1">Unique emails</p>
    </div>

  </div>

  {{-- Recent transactions --}}
  <div class="card overflow-hidden">
    <div class="card-header flex items-center justify-between">
      <h3 class="font-semibold text-slate-900 text-sm">Recent transactions</h3>
      <a href="{{ route('projects.paystack.transactions', $project) }}"
        class="text-xs text-brand-600 hover:text-brand-700 font-medium">
        View all →
      </a>
    </div>

    @if($recentTransactions->isEmpty())
    <div class="card-body py-16 text-center">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <p class="text-sm font-medium text-slate-500 mb-1">No transactions yet</p>
      <p class="text-xs text-slate-400 mb-5">
        Initialize a transaction via the API to get started.
      </p>
      <a href="{{ route('projects.api-keys.index', $project) }}" class="btn-primary text-sm">
        Get your API key →
      </a>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/50">
          <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reference</th>
          <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Customer</th>
          <th class="text-right px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
          <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
          <th class="text-right px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($recentTransactions as $tx)
        <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer"
          onclick="window.location='{{ route('projects.paystack.transactions.show', [$project, $tx->reference]) }}'">
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
              {{ Str::limit($tx->reference, 20) }}
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600">
            {{ $tx->customer?->email ?? '—' }}
          </td>
          <td class="px-6 py-4 text-right font-semibold text-slate-900">
            ₦{{ number_format($tx->amount / 100, 2) }}
          </td>
          <td class="px-6 py-4 text-center">
            <span class="{{ $tx->statusBadgeClass() }} badge capitalize">
              {{ $tx->status }}
            </span>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $tx->created_at->diffForHumans() }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif
  </div>

</x-app-layout>
