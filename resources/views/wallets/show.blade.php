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

  {{-- Wallet header card --}}
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

      {{-- Actions --}}
      <div class="flex items-center gap-2">
        @if($wallet->isFrozen())
        <form method="POST" action="{{ route('projects.wallets.destroy', [$project, $wallet]) }}">
          @csrf
          @method('DELETE')
          {{-- We'll wire freeze/unfreeze to scenarios in a later phase --}}
        </form>
        @endif
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

  {{-- Content panels --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Wallet details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Wallet details</h3>
      </div>
      <div class="card-body space-y-4">
        @foreach([
        ['label' => 'Name', 'value' => $wallet->name, 'mono' => false],
        ['label' => 'Reference', 'value' => $wallet->reference, 'mono' => true],
        ['label' => 'Currency', 'value' => $wallet->currency, 'mono' => false],
        ['label' => 'Status', 'value' => ucfirst($wallet->status), 'mono' => false],
        ['label' => 'Project', 'value' => $wallet->project->name, 'mono' => false],
        ['label' => 'Created', 'value' => $wallet->created_at->format('d M Y, H:i'), 'mono' => false],
        ] as $row)
        <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-500">{{ $row['label'] }}</p>
          <p class="{{ $row['mono'] ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800 font-medium' }}">
            {{ $row['value'] }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Ledger entries placeholder --}}
    <div class="card">
      <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-slate-900 text-sm">Ledger entries</h3>
        <span class="badge badge-slate">Phase 3</span>
      </div>
      <div class="card-body flex flex-col items-center justify-center py-10 text-center">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
          <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
        </div>
        <p class="text-sm font-medium text-slate-700">No ledger entries yet</p>
        <p class="text-xs text-slate-400 mt-1">Entries appear here after simulation scenarios run.</p>
      </div>
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
