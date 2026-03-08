<x-app-layout>
  <x-slot name="title">{{ $project->name }} — Wallets</x-slot>

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
    <span class="text-slate-600">Wallets</span>
  </div>

  {{-- Header --}}
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Wallets</h2>
      <p class="text-slate-500 text-sm mt-1">Manage simulation wallets for <span class="font-medium">{{ $project->name }}</span>.</p>
    </div>
    <a href="{{ route('projects.wallets.create', $project) }}" class="btn-primary">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      New wallet
    </a>
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

  {{-- Wallet list --}}
  @if($wallets->isEmpty())
  <div class="card">
    <div class="card-body flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-5">
        <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-900 mb-2">No wallets yet</h3>
      <p class="text-sm text-slate-500 max-w-sm mb-6">
        Create your first wallet to start simulating credits, debits, and ledger entries.
      </p>
      <a href="{{ route('projects.wallets.create', $project) }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create first wallet
      </a>
    </div>
  </div>
  @else
  {{-- Summary bar --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach(['NGN' => '₦', 'USD' => '$', 'KES' => 'KSh', 'GHS' => 'GH₵'] as $currency => $symbol)
    @php
    $total = $wallets->where('currency', $currency)->sum('balance');
    @endphp
    @if($total > 0 || $wallets->where('currency', $currency)->count() > 0)
    <div class="card p-4">
      <p class="text-xs font-medium text-slate-400 mb-1">{{ $currency }} Balance</p>
      <p class="text-lg font-bold text-slate-900">
        {{ $symbol }}{{ number_format($total / 100, 2) }}
      </p>
      <p class="text-xs text-slate-400 mt-1">
        {{ $wallets->where('currency', $currency)->count() }} wallet(s)
      </p>
    </div>
    @endif
    @endforeach
  </div>

  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-surface-50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Wallet</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Balance</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Available</th>
          <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Created</th>
          <th class="px-6 py-3.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($wallets as $wallet)
        <tr class="hover:bg-surface-50 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div>
                <p class="font-medium text-slate-900">{{ $wallet->name }}</p>
                <p class="text-xs text-slate-400">{{ $wallet->currency }}</p>
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
              {{ $wallet->reference }}
            </span>
          </td>
          <td class="px-6 py-4 text-right font-semibold text-slate-900">
            {{ $wallet->formattedBalance() }}
          </td>
          <td class="px-6 py-4 text-right text-slate-600">
            {{ $wallet->formattedAvailableBalance() }}
          </td>
          <td class="px-6 py-4 text-center">
            <span class="{{ $wallet->statusBadgeClass() }} badge capitalize">
              @if($wallet->status === 'frozen')
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              @elseif($wallet->status === 'active')
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
              @else
              <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
              @endif
              {{ $wallet->status }}
            </span>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $wallet->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            <a href="{{ route('projects.wallets.show', [$project, $wallet]) }}"
              class="text-brand-600 hover:text-brand-700 text-xs font-medium transition-colors">
              View →
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

</x-app-layout>
