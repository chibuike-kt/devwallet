<x-app-layout>
  <x-slot name="title">{{ $wallet->name }}</x-slot>

  {{-- Breadcrumb --}}
  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.wallets.index', $project) }}" class="hover:text-slate-600 transition-colors">Wallets</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">{{ $wallet->name }}</span>
  </div>

  {{-- Flash --}}
  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg">
    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <p class="text-sm text-emerald-800">{{ session('success') }}</p>
  </div>
  @endif

  {{-- Wallet header --}}
  <div class="card p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-7 h-7 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <div>
          <div class="flex items-center gap-3">
            <h2 class="text-xl font-semibold text-slate-900">{{ $wallet->name }}</h2>
            <span class="{{ $wallet->statusBadgeClass() }} badge capitalize">
              {{ $wallet->status }}
            </span>
          </div>
          <p class="font-mono text-xs text-slate-400 mt-1">{{ $wallet->reference }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('projects.wallets.index', $project) }}" class="btn-secondary">
          ← All wallets
        </a>
      </div>
    </div>
  </div>

  {{-- Balance cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Total Balance</p>
      <p class="text-2xl font-bold text-slate-900">{{ $wallet->formattedBalance() }}</p>
      <p class="text-xs text-slate-400 mt-1">Book balance</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Available Balance</p>
      <p class="text-2xl font-bold text-emerald-600">{{ $wallet->formattedAvailableBalance() }}</p>
      <p class="text-xs text-slate-400 mt-1">Spendable funds</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-1">Ledger Balance</p>
      <p class="text-2xl font-bold text-slate-700">{{ $wallet->formattedLedgerBalance() }}</p>
      <p class="text-xs text-slate-400 mt-1">Accounting balance</p>
    </div>
  </div>

  {{-- Frozen warning --}}
  @if($wallet->isFrozen())
  <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-amber-50 border border-amber-200 rounded-lg">
    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
    </svg>
    <p class="text-sm text-amber-800">
      This wallet is <strong>frozen</strong>. Transactions are blocked until it is unfrozen via a simulation scenario.
    </p>
  </div>
  @endif

  {{-- Detail panels --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Wallet details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Wallet details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['label' => 'Name', 'value' => $wallet->name, 'mono' => false],
        ['label' => 'Reference', 'value' => $wallet->reference, 'mono' => true],
        ['label' => 'Currency', 'value' => $wallet->currency, 'mono' => false],
        ['label' => 'Status', 'value' => ucfirst($wallet->status), 'mono' => false],
        ['label' => 'Project', 'value' => $wallet->project->name, 'mono' => false],
        ['label' => 'Created', 'value' => $wallet->created_at->format('d M Y, H:i'),'mono' => false],
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

    {{-- Ledger entries panel --}}
    <div class="card">
      <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-slate-900 text-sm">Ledger entries</h3>
        <a href="{{ route('projects.wallets.ledger', [$project, $wallet]) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          View full ledger →
        </a>
      </div>
      @php
      $recentEntries = $wallet->ledgerEntries()->with('transaction')->take(5)->get();
      @endphp
      @if($recentEntries->isEmpty())
      <div class="card-body flex flex-col items-center justify-center py-10 text-center">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <p class="text-sm font-medium text-slate-700">No ledger entries yet</p>
        <p class="text-xs text-slate-400 mt-1">Entries appear here after simulations run.</p>
      </div>
      @else
      <div class="divide-y divide-slate-100">
        @foreach($recentEntries as $entry)
        <div class="px-6 py-3.5 flex items-center justify-between">
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
              <p class="text-xs font-medium text-slate-700">{{ $entry->direction->label() }}</p>
              <p class="text-xs text-slate-400 mt-0.5">Balance: {{ $entry->formattedRunningBalance() }}</p>
            </div>
          </div>
          <p class="font-semibold text-sm {{ $entry->isCredit() ? 'text-emerald-600' : 'text-red-500' }}">
            {{ $entry->isCredit() ? '+' : '-' }}{{ $entry->formattedAmount() }}
          </p>
        </div>
        @endforeach
      </div>
      <div class="px-6 py-3 border-t border-slate-100">
        <a href="{{ route('projects.wallets.ledger', [$project, $wallet]) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          View full ledger →
        </a>
      </div>
      @endif
    </div>

    {{-- Recent transactions --}}
    <div class="card lg:col-span-2">
      <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-slate-900 text-sm">Recent transactions</h3>
        <a href="{{ route('projects.transactions.index', $project) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          View all →
        </a>
      </div>
      @php
      $recentTx = $wallet->transactions()->with('wallet')->take(8)->get();
      @endphp
      @if($recentTx->isEmpty())
      <div class="card-body text-center py-10">
        <p class="text-sm text-slate-400">No transactions yet on this wallet.</p>
        <a href="{{ route('projects.scenarios.index', $project) }}"
          class="btn-primary mt-4 inline-flex text-xs px-3 py-1.5">
          Run a scenario
        </a>
      </div>
      @else
      <div class="overflow-hidden">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 bg-surface-50">
              <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
              <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
              <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($recentTx as $tx)
            <tr class="hover:bg-surface-50 transition-colors">
              <td class="px-6 py-3.5">
                <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
                  {{ Str::limit($tx->reference, 18) }}
                </span>
              </td>
              <td class="px-6 py-3.5">
                <span class="{{ $tx->type->badgeClass() }} badge">{{ $tx->type->label() }}</span>
              </td>
              <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
                {{ $tx->formattedAmount() }}
              </td>
              <td class="px-6 py-3.5 text-center">
                <span class="{{ $tx->status->badgeClass() }} badge">{{ $tx->status->label() }}</span>
              </td>
              <td class="px-6 py-3.5 text-right text-xs text-slate-400">
                {{ $tx->created_at->diffForHumans() }}
              </td>
              <td class="px-6 py-3.5 text-right">
                <a href="{{ route('projects.transactions.show', [$project, $tx]) }}"
                  class="text-brand-600 hover:text-brand-700 text-xs font-medium">
                  View →
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>

  </div>

  {{-- Danger zone --}}
  <div class="mt-6 card border-red-100">
    <div class="card-header">
      <h3 class="font-semibold text-slate-900 text-sm">Danger zone</h3>
    </div>
    <div class="card-body flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-slate-700">Close this wallet</p>
        <p class="text-xs text-slate-400 mt-0.5">Closed wallets cannot transact and cannot be reopened.</p>
      </div>
      <form method="POST" action="{{ route('projects.wallets.destroy', [$project, $wallet]) }}"
        onsubmit="return confirm('Close wallet {{ addslashes($wallet->name) }}? This cannot be undone.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger" {{ $wallet->isClosed() ? 'disabled' : '' }}>
          Close wallet
        </button>
      </form>
    </div>
  </div>

</x-app-layout>
