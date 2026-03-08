<x-app-layout>
  <x-slot name="title">Ledger — {{ $wallet->name }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.wallets.show', [$project, $wallet]) }}" class="hover:text-slate-600 transition-colors">{{ $wallet->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">Ledger</span>
  </div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Ledger — {{ $wallet->name }}</h2>
      <p class="text-slate-500 text-sm mt-1">Complete credit and debit history for this wallet.</p>
    </div>
    {{-- Integrity indicator --}}
    <div class="flex items-center gap-2 px-4 py-2 rounded-lg border {{ $integrity['is_balanced'] ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' }}">
      <svg class="w-4 h-4 {{ $integrity['is_balanced'] ? 'text-emerald-600' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        @if($integrity['is_balanced'])
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        @else
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        @endif
      </svg>
      <span class="text-xs font-medium {{ $integrity['is_balanced'] ? 'text-emerald-700' : 'text-red-700' }}">
        {{ $integrity['is_balanced'] ? 'Ledger balanced' : 'Balance mismatch!' }}
      </span>
    </div>
  </div>

  {{-- Summary cards --}}
  <div class="grid grid-cols-3 gap-4 mb-6">
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Total Credits</p>
      <p class="text-xl font-bold text-emerald-600">{{ $wallet->formatAmount($integrity['credits']) }}</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Total Debits</p>
      <p class="text-xl font-bold text-red-500">{{ $wallet->formatAmount($integrity['debits']) }}</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Net Balance</p>
      <p class="text-xl font-bold text-slate-900">{{ $wallet->formattedBalance() }}</p>
    </div>
  </div>

  @if($entries->isEmpty())
  <div class="card">
    <div class="card-body flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-violet-50 flex items-center justify-center mb-5">
        <svg class="w-7 h-7 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-900 mb-2">No ledger entries yet</h3>
      <p class="text-sm text-slate-500 max-w-sm">Every credit and debit will appear here once simulations run.</p>
    </div>
  </div>
  @else
  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-surface-50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Direction</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Narration</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Running Balance</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($entries as $entry)
        <tr class="hover:bg-surface-50 transition-colors">
          <td class="px-6 py-4">
            <span class="{{ $entry->direction->badgeClass() }} badge">
              {{ $entry->direction->label() }}
            </span>
          </td>
          <td class="px-6 py-4 text-slate-600 max-w-xs truncate">
            {{ $entry->narration ?? '—' }}
          </td>
          <td class="px-6 py-4 text-right font-semibold {{ $entry->isCredit() ? 'text-emerald-600' : 'text-red-500' }}">
            {{ $entry->isCredit() ? '+' : '-' }}{{ $entry->formattedAmount() }}
          </td>
          <td class="px-6 py-4 text-right font-mono text-xs text-slate-600">
            {{ $entry->formattedRunningBalance() }}
          </td>
          <td class="px-6 py-4">
            <a href="{{ route('projects.transactions.show', [$project, $entry->transaction_id]) }}"
              class="font-mono text-xs text-brand-600 hover:text-brand-700 bg-brand-50 px-2 py-1 rounded">
              {{ $entry->transaction->reference ?? '—' }}
            </a>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $entry->created_at->diffForHumans() }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($entries->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $entries->links() }}
    </div>
    @endif
  </div>
  @endif

</x-app-layout>
