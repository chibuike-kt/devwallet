<x-app-layout>
  <x-slot name="title">Scenarios — {{ $project->name }}</x-slot>

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
    <span class="text-slate-600">Scenarios</span>
  </div>

  {{-- Header --}}
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Simulation Scenarios</h2>
      <p class="text-slate-500 text-sm mt-1">
        Trigger payment events for <span class="font-medium text-slate-700">{{ $project->name }}</span>. Every action updates balances and writes ledger entries in real time.
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('projects.transactions.index', $project) }}" class="btn-secondary">
        View transactions →
      </a>
    </div>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3.5 bg-emerald-50 border border-emerald-200 rounded-xl">
    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
      </svg>
    </div>
    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
  </div>
  @endif

  @if(session('error'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3.5 bg-red-50 border border-red-200 rounded-xl">
    <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
  </div>
  @endif

  {{-- No wallets warning --}}
  @if($wallets->isEmpty())
  <div class="card p-8 text-center">
    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4">
      <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
      </svg>
    </div>
    <h3 class="text-base font-semibold text-slate-900 mb-2">No active wallets</h3>
    <p class="text-sm text-slate-500 mb-5">You need at least one active wallet to run simulation scenarios.</p>
    <a href="{{ route('projects.wallets.create', $project) }}" class="btn-primary">
      Create a wallet first
    </a>
  </div>
  @else

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left column: scenario cards --}}
    <div class="lg:col-span-2 space-y-4">

      {{-- 1. Fund Wallet --}}
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-emerald-50 border-b border-emerald-100">
          <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Fund Wallet</h3>
            <p class="text-xs text-slate-500">Credit a wallet balance. Always succeeds.</p>
          </div>
          <span class="ml-auto badge badge-green">Credit</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="fund_wallet">
            <div class="flex-1 min-w-36">
              <label class="form-label">Wallet</label>
              <select name="wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->formattedBalance() }})</option>
                @endforeach
              </select>
            </div>
            <div class="w-36">
              <label class="form-label">Amount ({{ $wallets->first()->currency }})</label>
              <input type="number" name="amount" class="form-input" placeholder="5000" min="1" step="0.01" required>
            </div>
            <div class="flex-1 min-w-36">
              <label class="form-label">Narration</label>
              <input type="text" name="narration" class="form-input" placeholder="Bank deposit">
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">
              Run scenario
            </button>
          </form>
        </div>
      </div>

      {{-- 2. Debit Wallet --}}
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-red-50 border-b border-red-100">
          <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Debit Wallet</h3>
            <p class="text-xs text-slate-500">Debit a wallet. Fails cleanly on insufficient balance.</p>
          </div>
          <span class="ml-auto badge badge-red">Debit</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="debit_wallet">
            <div class="flex-1 min-w-36">
              <label class="form-label">Wallet</label>
              <select name="wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->formattedBalance() }})</option>
                @endforeach
              </select>
            </div>
            <div class="w-36">
              <label class="form-label">Amount</label>
              <input type="number" name="amount" class="form-input" placeholder="1000" min="1" step="0.01" required>
            </div>
            <div class="flex-1 min-w-36">
              <label class="form-label">Narration</label>
              <input type="text" name="narration" class="form-input" placeholder="Withdrawal">
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">
              Run scenario
            </button>
          </form>
        </div>
      </div>

      {{-- 3. Wallet Transfer --}}
      @if($wallets->count() >= 2)
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-brand-50 border-b border-brand-100">
          <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Wallet Transfer</h3>
            <p class="text-xs text-slate-500">Move funds between two wallets in this project.</p>
          </div>
          <span class="ml-auto badge badge-blue">Transfer</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="wallet_transfer">
            <div class="flex-1 min-w-36">
              <label class="form-label">From wallet</label>
              <select name="wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->formattedBalance() }})</option>
                @endforeach
              </select>
            </div>
            <div class="flex-1 min-w-36">
              <label class="form-label">To wallet</label>
              <select name="target_wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="w-32">
              <label class="form-label">Amount</label>
              <input type="number" name="amount" class="form-input" placeholder="500" min="1" step="0.01" required>
            </div>
            <button type="submit" class="btn-primary whitespace-nowrap">
              Run scenario
            </button>
          </form>
        </div>
      </div>
      @endif

      {{-- 4. Freeze / Unfreeze --}}
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-amber-50 border-b border-amber-100">
          <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Freeze / Unfreeze Wallet</h3>
            <p class="text-xs text-slate-500">Toggle wallet restriction state.</p>
          </div>
          <span class="ml-auto badge badge-yellow">Status</span>
        </div>
        <div class="px-6 py-5">
          <div class="flex flex-wrap gap-4">
            {{-- Freeze --}}
            <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex items-end gap-3">
              @csrf
              <input type="hidden" name="scenario" value="freeze_wallet">
              <div class="flex-1 min-w-48">
                <label class="form-label">Wallet to freeze</label>
                <select name="wallet_id" class="form-input">
                  @foreach($wallets as $wallet)
                  <option value="{{ $wallet->id }}">{{ $wallet->name }} — {{ ucfirst($wallet->status) }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn-secondary whitespace-nowrap border-amber-300 text-amber-700 hover:bg-amber-50">
                Freeze
              </button>
            </form>

            {{-- Unfreeze --}}
            @if($allWallets->where('status', 'frozen')->count() > 0)
            <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex items-end gap-3">
              @csrf
              <input type="hidden" name="scenario" value="unfreeze_wallet">
              <div class="flex-1 min-w-48">
                <label class="form-label">Wallet to unfreeze</label>
                <select name="wallet_id" class="form-input">
                  @foreach($allWallets->where('status', 'frozen') as $wallet)
                  <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                  @endforeach
                </select>
              </div>
              <button type="submit" class="btn-primary whitespace-nowrap">
                Unfreeze
              </button>
            </form>
            @endif
          </div>
        </div>
      </div>

      {{-- 5. Failed Transfer --}}
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-slate-50 border-b border-slate-200">
          <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Simulate Failed Transfer</h3>
            <p class="text-xs text-slate-500">Temporarily freeze wallet, attempt debit, record failure.</p>
          </div>
          <span class="ml-auto badge badge-red">Failure</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="failed_transfer">
            <div class="flex-1 min-w-36">
              <label class="form-label">Wallet</label>
              <select name="wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="w-36">
              <label class="form-label">Amount</label>
              <input type="number" name="amount" class="form-input" placeholder="1000" min="1" step="0.01" required>
            </div>
            <button type="submit" class="btn-secondary border-red-200 text-red-700 hover:bg-red-50 whitespace-nowrap">
              Simulate failure
            </button>
          </form>
        </div>
      </div>

      {{-- 6. Bank Transfer Timeout --}}
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-violet-50 border-b border-violet-100">
          <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-violet-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Bank Transfer — Provider Timeout</h3>
            <p class="text-xs text-slate-500">Initiates a transfer that gets stuck in pending forever.</p>
          </div>
          <span class="ml-auto badge badge-yellow">Pending</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="bank_transfer_timeout">
            <div class="flex-1 min-w-36">
              <label class="form-label">Wallet</label>
              <select name="wallet_id" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->formattedBalance() }})</option>
                @endforeach
              </select>
            </div>
            <div class="w-36">
              <label class="form-label">Amount</label>
              <input type="number" name="amount" class="form-input" placeholder="2000" min="1" step="0.01" required>
            </div>
            <div class="flex-1 min-w-36">
              <label class="form-label">Narration</label>
              <input type="text" name="narration" class="form-input" placeholder="Send to Zenith Bank">
            </div>
            <button type="submit" class="btn-secondary border-violet-200 text-violet-700 hover:bg-violet-50 whitespace-nowrap">
              Simulate timeout
            </button>
          </form>
        </div>
      </div>

      {{-- 7. Reverse Transaction --}}
      @if($reversibleTransaction)
      <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 bg-orange-50 border-b border-orange-100">
          <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-slate-900 text-sm">Reverse a Transaction</h3>
            <p class="text-xs text-slate-500">Reverse a successful transaction and restore the balance.</p>
          </div>
          <span class="ml-auto badge badge-yellow">Reversal</span>
        </div>
        <div class="px-6 py-5">
          <form method="POST" action="{{ route('projects.scenarios.run', $project) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <input type="hidden" name="scenario" value="reverse_transaction">
            <div class="flex-1">
              <label class="form-label">Transaction to reverse</label>
              <select name="transaction_id" class="form-input">
                @foreach($allWallets as $w)
                @php
                $reversible = \App\Models\Transaction::where('project_id', $project->id)
                ->where('wallet_id', $w->id)
                ->where('status', 'success')
                ->whereNotIn('type', ['reversal'])
                ->latest()
                ->take(3)
                ->get();
                @endphp
                @foreach($reversible as $rt)
                <option value="{{ $rt->id }}">
                  {{ $rt->reference }} — {{ $rt->type->label() }} — {{ $w->formatAmount($rt->amount) }}
                </option>
                @endforeach
                @endforeach
              </select>
            </div>
            <button type="submit" class="btn-secondary border-orange-200 text-orange-700 hover:bg-orange-50 whitespace-nowrap">
              Reverse
            </button>
          </form>
        </div>
      </div>
      @endif

    </div>

    {{-- Right column: recent activity --}}
    <div class="space-y-5">

      {{-- Wallet balances --}}
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Wallet balances</h3>
        </div>
        <div class="divide-y divide-slate-100">
          @foreach($allWallets as $wallet)
          <div class="px-6 py-3.5 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-800">{{ $wallet->name }}</p>
              <span class="{{ $wallet->statusBadgeClass() }} badge mt-0.5 capitalize">
                {{ $wallet->status }}
              </span>
            </div>
            <p class="font-bold text-slate-900 text-sm">{{ $wallet->formattedBalance() }}</p>
          </div>
          @endforeach
        </div>
        <div class="px-6 py-3 border-t border-slate-100">
          <a href="{{ route('projects.wallets.index', $project) }}"
            class="text-xs text-brand-600 hover:text-brand-700 font-medium">
            Manage wallets →
          </a>
        </div>
      </div>

      {{-- Recent transactions --}}
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 text-sm">Recent transactions</h3>
          <a href="{{ route('projects.transactions.index', $project) }}"
            class="text-xs text-brand-600 hover:text-brand-700 font-medium">
            All →
          </a>
        </div>
        @if($recentTransactions->isEmpty())
        <div class="card-body text-center py-8">
          <p class="text-sm text-slate-400">No transactions yet. Run a scenario.</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
          @foreach($recentTransactions as $tx)
          <div class="px-6 py-3.5">
            <div class="flex items-center justify-between mb-1">
              <span class="{{ $tx->type->badgeClass() }} badge text-xs">
                {{ $tx->type->label() }}
              </span>
              <span class="{{ $tx->status->badgeClass() }} badge text-xs">
                {{ $tx->status->label() }}
              </span>
            </div>
            <div class="flex items-center justify-between mt-1.5">
              <p class="font-mono text-xs text-slate-400">{{ Str::limit($tx->reference, 20) }}</p>
              <p class="text-sm font-semibold text-slate-800">
                {{ $tx->wallet->formatAmount($tx->amount) }}
              </p>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>

    </div>

  </div>
  @endif

</x-app-layout>
