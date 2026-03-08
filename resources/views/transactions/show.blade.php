<x-app-layout>
  <x-slot name="title">{{ $transaction->reference }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.transactions.index', $project) }}" class="hover:text-slate-600 transition-colors">Transactions</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600 font-mono text-xs">{{ $transaction->reference }}</span>
  </div>

  {{-- Transaction header --}}
  <div class="card p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-1">
          <span class="{{ $transaction->type->badgeClass() }} badge">{{ $transaction->type->label() }}</span>
          <span class="{{ $transaction->status->badgeClass() }} badge">{{ $transaction->status->label() }}</span>
        </div>
        <p class="font-mono text-sm text-slate-700 font-semibold">{{ $transaction->reference }}</p>
        @if($transaction->narration)
        <p class="text-slate-500 text-sm mt-1">{{ $transaction->narration }}</p>
        @endif
      </div>
      <div class="text-right">
        <p class="text-3xl font-bold text-slate-900">{{ $transaction->formattedAmount() }}</p>
        <p class="text-xs text-slate-400 mt-1">{{ $transaction->created_at->format('d M Y, H:i:s') }}</p>
      </div>
    </div>
  </div>

  @if($transaction->isFailed() && $transaction->failure_reason)
  <div class="mb-6 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-lg">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div>
      <p class="text-sm font-medium text-red-800">Failure reason</p>
      <p class="text-sm text-red-700 mt-0.5">{{ $transaction->failure_reason }}</p>
    </div>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Transaction details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Transaction details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['label' => 'Reference', 'value' => $transaction->reference, 'mono' => true],
        ['label' => 'Type', 'value' => $transaction->type->label(), 'mono' => false],
        ['label' => 'Status', 'value' => $transaction->status->label(), 'mono' => false],
        ['label' => 'Amount', 'value' => $transaction->formattedAmount(), 'mono' => false],
        ['label' => 'Currency', 'value' => $transaction->currency, 'mono' => false],
        ['label' => 'Wallet', 'value' => $transaction->wallet->name ?? '—', 'mono' => false],
        ['label' => 'Balance Before','value' => $transaction->wallet->formatAmount($transaction->balance_before), 'mono' => false],
        ['label' => 'Balance After', 'value' => $transaction->wallet->formatAmount($transaction->balance_after), 'mono' => false],
        ['label' => 'Provider', 'value' => $transaction->provider ?? '—', 'mono' => false],
        ['label' => 'Idempotency Key', 'value' => $transaction->idempotency_key ?? '—', 'mono' => true],
        ['label' => 'Completed At', 'value' => $transaction->completed_at?->format('d M Y, H:i:s') ?? '—', 'mono' => false],
        ] as $row)
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-500">{{ $row['label'] }}</p>
          <p class="{{ $row['mono'] ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800 font-medium' }}">
            {{ $row['value'] }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Ledger entries for this transaction --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Ledger entries</h3>
      </div>
      @if($transaction->ledgerEntries->isEmpty())
      <div class="card-body flex flex-col items-center justify-center py-10 text-center">
        <p class="text-sm text-slate-500">No ledger entries for this transaction.</p>
      </div>
      @else
      <div class="divide-y divide-slate-100">
        @foreach($transaction->ledgerEntries as $entry)
        <div class="px-6 py-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $entry->isCredit() ? 'bg-emerald-50' : 'bg-red-50' }}">
              <svg class="w-4 h-4 {{ $entry->isCredit() ? 'text-emerald-600' : 'text-red-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($entry->isCredit())
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                @else
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                @endif
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-slate-900">{{ $entry->direction->label() }}</p>
              <p class="text-xs text-slate-400">Balance after: {{ $entry->formattedRunningBalance() }}</p>
            </div>
          </div>
          <p class="font-semibold {{ $entry->isCredit() ? 'text-emerald-600' : 'text-red-500' }}">
            {{ $entry->isCredit() ? '+' : '-' }}{{ $entry->formattedAmount() }}
          </p>
        </div>
        @endforeach
      </div>
      @endif
    </div>

  </div>

</x-app-layout>
