<x-app-layout>
  <x-slot name="title">Transactions — {{ $project->name }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">Transactions</span>
  </div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Transactions</h2>
      <p class="text-slate-500 text-sm mt-1">All financial events for <span class="font-medium">{{ $project->name }}</span>.</p>
    </div>
  </div>

  {{-- Export + filter bar --}}
  <div class="card p-5 mb-6">
    <form method="GET"
      action="{{ route('projects.transactions.export', $project) }}"
      class="flex flex-wrap items-end gap-3">

      <div class="flex-1 min-w-40">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
          <option value="">All statuses</option>
          <option value="success">Success</option>
          <option value="failed">Failed</option>
          <option value="pending">Pending</option>
          <option value="reversed">Reversed</option>
        </select>
      </div>

      <div class="flex-1 min-w-40">
        <label class="form-label">Wallet</label>
        <select name="wallet_id" class="form-input">
          <option value="">All wallets</option>
          @foreach($project->wallets as $wallet)
          <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
          @endforeach
        </select>
      </div>

      <div class="flex-1 min-w-36">
        <label class="form-label">From</label>
        <input type="date" name="date_from" class="form-input">
      </div>

      <div class="flex-1 min-w-36">
        <label class="form-label">To</label>
        <input type="date" name="date_to" class="form-input">
      </div>

      <button type="submit" class="btn-primary flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Export CSV
      </button>

    </form>
  </div>

  @if($transactions->isEmpty())
  <div class="card">
    <div class="card-body flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-5">
        <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-900 mb-2">No transactions yet</h3>
      <p class="text-sm text-slate-500 max-w-sm">Run a simulation scenario to generate transactions.</p>
    </div>
  </div>
  @else
  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-surface-50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Wallet</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
          <th class="px-6 py-3.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($transactions as $tx)
        <tr class="hover:bg-surface-50 transition-colors">
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
              {{ $tx->reference }}
            </span>
          </td>
          <td class="px-6 py-4">
            <span class="{{ $tx->type->badgeClass() }} badge">
              {{ $tx->type->label() }}
            </span>
          </td>
          <td class="px-6 py-4 text-slate-600 text-xs">
            {{ $tx->wallet->name ?? '—' }}
          </td>
          <td class="px-6 py-4 text-right font-semibold text-slate-900">
            {{ $tx->formattedAmount() }}
          </td>
          <td class="px-6 py-4 text-center">
            <span class="{{ $tx->status->badgeClass() }} badge capitalize">
              {{ $tx->status->label() }}
            </span>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $tx->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            <a href="{{ route('projects.transactions.show', [$project, $tx]) }}"
              class="text-brand-600 hover:text-brand-700 text-xs font-medium">
              View →
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($transactions->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $transactions->links() }}
    </div>
    @endif
  </div>
  @endif

</x-app-layout>
