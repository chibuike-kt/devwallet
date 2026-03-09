<x-app-layout>
  {{-- Ledger entries panel --}}
  <div class="card">
    <div class="card-header flex items-center justify-between">
      <h3 class="font-semibold text-slate-900 text-sm">Ledger entries</h3>
      <a href="{{ route('projects.wallets.ledger', [$project, $wallet]) }}"
        class="text-xs text-brand-600 hover:text-brand-700 font-medium">
        View full ledger →
      </a>
    </div>
    <div class="card-body flex flex-col items-center justify-center py-10 text-center">
      <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
        <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <p class="text-sm font-medium text-slate-700">No ledger entries yet</p>
      <p class="text-xs text-slate-400 mt-1">Entries appear here after simulations run.</p>
    </div>
  </div>
