<x-app-layout>
  <x-slot name="title">{{ $project->name }}</x-slot>

  {{-- Flash message --}}
  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg">
    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <p class="text-sm text-emerald-800">{{ session('success') }}</p>
  </div>
  @endif

  {{-- Project header --}}
  <div class="flex items-start justify-between mb-8">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-base flex-shrink-0"
        style="background-color: {{ $project->color }}">
        {{ $project->initials() }}
      </div>
      <div>
        <div class="flex items-center gap-3">
          <h2 class="text-xl font-semibold text-slate-900">{{ $project->name }}</h2>
          <span class="{{ $project->environment === 'staging' ? 'badge-yellow' : 'badge-blue' }} badge">
            {{ $project->environmentLabel() }}
          </span>
        </div>
        @if($project->description)
        <p class="text-slate-500 text-sm mt-0.5">{{ $project->description }}</p>
        @endif
        <p class="text-slate-400 text-xs mt-1 font-mono">{{ $project->slug }}</p>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('projects.index') }}" class="btn-secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        All projects
      </a>
    </div>
  </div>

  @php
  $walletCount = $project->wallets()->count();
  $txCount = \App\Models\Transaction::where('project_id', $project->id)->count();
  $walletIds = $project->wallets()->pluck('id');
  $ledgerCount = \App\Models\LedgerEntry::whereIn('wallet_id', $walletIds)->count();
  $webhookCount = 0;
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-2">Wallets</p>
      <p class="text-2xl font-bold text-slate-900">{{ $walletCount }}</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-2">Transactions</p>
      <p class="text-2xl font-bold text-slate-900">{{ $txCount }}</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-2">Ledger Entries</p>
      <p class="text-2xl font-bold text-slate-900">{{ $ledgerCount }}</p>
    </div>
    <div class="card p-5">
      <p class="text-xs font-medium text-slate-500 mb-2">Webhook Events</p>
      <p class="text-2xl font-bold text-slate-900">{{ $webhookCount }}</p>
    </div>
  </div>

  {{-- Module panels --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Wallets panel --}}
    <div class="card">
      <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-slate-900 text-sm">Wallets</h3>
        <a href="{{ route('projects.wallets.create', $project) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          + New wallet
        </a>
      </div>
      @php $projectWallets = $project->wallets()->latest()->get(); @endphp
      @if($projectWallets->isEmpty())
      <div class="card-body flex flex-col items-center justify-center py-10 text-center">
        <p class="text-sm text-slate-500 mb-3">No wallets yet.</p>
        <a href="{{ route('projects.wallets.create', $project) }}" class="btn-primary text-xs px-3 py-1.5">
          Create first wallet
        </a>
      </div>
      @else
      <div class="divide-y divide-slate-100">
        @foreach($projectWallets as $w)
        <a href="{{ route('projects.wallets.show', [$project, $w]) }}"
          class="flex items-center justify-between px-6 py-3.5 hover:bg-surface-50 transition-colors group">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-medium text-slate-800 group-hover:text-brand-700 transition-colors">
                {{ $w->name }}
              </p>
              <span class="{{ $w->statusBadgeClass() }} badge capitalize text-xs mt-0.5">
                {{ $w->status }}
              </span>
            </div>
          </div>
          <p class="text-sm font-bold text-slate-900">{{ $w->formattedBalance() }}</p>
        </a>
        @endforeach
      </div>
      <div class="px-6 py-3 border-t border-slate-100">
        <a href="{{ route('projects.wallets.index', $project) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          View all wallets →
        </a>
      </div>
      @endif
    </div>

    {{-- Recent transactions panel --}}
    <div class="card">
      <div class="card-header flex items-center justify-between">
        <h3 class="font-semibold text-slate-900 text-sm">Recent Transactions</h3>
        <a href="{{ route('projects.transactions.index', $project) }}"
          class="text-xs text-brand-600 hover:text-brand-700 font-medium">
          View all →
        </a>
      </div>
      @php
      $recentTx = \App\Models\Transaction::where('project_id', $project->id)
      ->with('wallet')
      ->latest()
      ->take(5)
      ->get();
      @endphp
      @if($recentTx->isEmpty())
      <div class="card-body flex flex-col items-center justify-center py-10 text-center">
        <p class="text-sm text-slate-500 mb-3">No transactions yet.</p>
        <a href="{{ route('projects.scenarios.index', $project) }}" class="btn-primary text-xs px-3 py-1.5">
          Run a scenario
        </a>
      </div>
      @else
      <div class="divide-y divide-slate-100">
        @foreach($recentTx as $tx)
        <a href="{{ route('projects.transactions.show', [$project, $tx]) }}"
          class="flex items-center justify-between px-6 py-3.5 hover:bg-surface-50 transition-colors group">
          <div class="flex items-center gap-3">
            <div class="flex flex-col gap-1">
              <span class="{{ $tx->type->badgeClass() }} badge text-xs">
                {{ $tx->type->label() }}
              </span>
              <span class="font-mono text-xs text-slate-400">
                {{ Str::limit($tx->reference, 18) }}
              </span>
            </div>
          </div>
          <div class="text-right">
            <p class="text-sm font-semibold text-slate-900">
              {{ $tx->wallet->formatAmount($tx->amount) }}
            </p>
            <span class="{{ $tx->status->badgeClass() }} badge text-xs mt-0.5">
              {{ $tx->status->label() }}
            </span>
          </div>
        </a>
        @endforeach
      </div>
      @endif
    </div>

    {{-- Quick actions --}}
    <div class="card lg:col-span-2">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Quick actions</h3>
      </div>
      <div class="card-body flex flex-wrap gap-3">
        <a href="{{ route('projects.scenarios.index', $project) }}" class="btn-primary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Run scenario
        </a>
        <a href="{{ route('projects.wallets.create', $project) }}" class="btn-secondary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add wallet
        </a>
        <a href="{{ route('projects.transactions.index', $project) }}" class="btn-secondary">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
          </svg>
          View transactions
        </a>
      </div>
    </div>

  </div>

  {{-- Danger zone --}}
  <div class="mt-8 card border-red-100">
    <div class="card-header">
      <h3 class="font-semibold text-slate-900 text-sm">Danger zone</h3>
    </div>
    <div class="card-body flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-slate-700">Archive this project</p>
        <p class="text-xs text-slate-400 mt-0.5">The project will be hidden but not permanently deleted.</p>
      </div>
      <form method="POST" action="{{ route('projects.destroy', $project) }}"
        onsubmit="return confirm('Archive {{ addslashes($project->name) }}? This will hide it from your dashboard.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-danger">
          Archive project
        </button>
      </form>
    </div>
  </div>

</x-app-layout>
