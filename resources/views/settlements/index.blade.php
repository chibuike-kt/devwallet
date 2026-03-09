<x-app-layout>
  <x-slot name="title">Settlements — {{ $project->name }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">Settlements</span>
  </div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Settlement Batches</h2>
      <p class="text-slate-500 text-sm mt-1">
        Group and settle eligible transactions from your wallets.
      </p>
    </div>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3.5 bg-emerald-50 border border-emerald-200 rounded-xl">
    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <p class="text-sm text-emerald-800 font-medium">{{ session('success') }}</p>
  </div>
  @endif

  @if(session('error'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3.5 bg-red-50 border border-red-200 rounded-xl">
    <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: run settlement + history --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Run a settlement --}}
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Run settlement batch</h3>
          <p class="text-xs text-slate-500 mt-0.5">
            Selects all unsettled successful debit transactions for the chosen wallet and batches them.
          </p>
        </div>
        <div class="card-body">
          @if($wallets->isEmpty())
          <p class="text-sm text-slate-400 text-center py-4">
            No active wallets. Create one first.
          </p>
          @else
          <form method="POST"
            action="{{ route('projects.settlements.run', $project) }}"
            class="space-y-4">
            @csrf

            <div>
              <label class="form-label">Wallet to settle</label>
              <select name="wallet_id" id="wallet_select" class="form-input">
                @foreach($wallets as $wallet)
                <option value="{{ $wallet->id }}">
                  {{ $wallet->name }} — {{ $wallet->formattedBalance() }}
                  ({{ $previews[$wallet->id]['count'] }} unsettled txns)
                </option>
                @endforeach
              </select>
            </div>

            {{-- Preview panel per wallet --}}
            @foreach($wallets as $wallet)
            @php $preview = $previews[$wallet->id]; @endphp
            <div class="wallet-preview hidden"
              data-wallet-id="{{ $wallet->id }}">
              <div class="p-4 rounded-xl border
                                        {{ $preview['count'] > 0
                                            ? 'bg-brand-50 border-brand-100'
                                            : 'bg-slate-50 border-slate-200' }}">
                @if($preview['count'] > 0)
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-semibold text-slate-800">
                      {{ $preview['count'] }} transaction(s) eligible
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">
                      These will be included in the batch.
                    </p>
                  </div>
                  <p class="text-lg font-bold text-brand-700">
                    {{ $wallet->formatAmount($preview['total']) }}
                  </p>
                </div>
                @else
                <p class="text-sm text-slate-500 text-center py-2">
                  No unsettled transactions on this wallet.
                  Run some debit scenarios first.
                </p>
                @endif
              </div>
            </div>
            @endforeach

            <div>
              <label class="form-label">Notes <span class="text-slate-400 font-normal">(optional)</span></label>
              <input type="text" name="notes" class="form-input"
                placeholder="e.g. End of day settlement — {{ now()->format('d M Y') }}">
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
              <button type="submit" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Run settlement
              </button>
            </div>
          </form>
          @endif
        </div>
      </div>

      {{-- Settlement history --}}
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Settlement history</h3>
        </div>
        @if($batches->isEmpty())
        <div class="card-body flex flex-col items-center justify-center py-12 text-center">
          <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-slate-700">No settlement batches yet</p>
          <p class="text-xs text-slate-400 mt-1">Run a settlement to see history here.</p>
        </div>
        @else
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 bg-surface-50">
              <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
              <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Wallet</th>
              <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Txns</th>
              <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
              <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Settled</th>
              <th class="px-6 py-3.5"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($batches as $batch)
            <tr class="hover:bg-surface-50 transition-colors">
              <td class="px-6 py-4">
                <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
                  {{ $batch->reference }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-slate-600">
                {{ $batch->wallet->name ?? '—' }}
              </td>
              <td class="px-6 py-4 text-center text-sm text-slate-700 font-medium">
                {{ $batch->transaction_count }}
              </td>
              <td class="px-6 py-4 text-right font-bold text-slate-900">
                {{ $batch->formattedTotal() }}
              </td>
              <td class="px-6 py-4 text-center">
                <span class="{{ $batch->statusBadgeClass() }} badge capitalize">
                  {{ $batch->status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-xs text-slate-400">
                {{ $batch->settled_at?->diffForHumans() ?? '—' }}
              </td>
              <td class="px-6 py-4 text-right">
                <a href="{{ route('projects.settlements.show', [$project, $batch]) }}"
                  class="text-brand-600 hover:text-brand-700 text-xs font-medium">
                  View →
                </a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>

    </div>

    {{-- Right: wallet unsettled summary --}}
    <div class="space-y-4">
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Unsettled by wallet</h3>
        </div>
        @if($wallets->isEmpty())
        <div class="card-body text-center py-8">
          <p class="text-xs text-slate-400">No wallets found.</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
          @foreach($wallets as $wallet)
          @php $preview = $previews[$wallet->id]; @endphp
          <div class="px-6 py-4">
            <div class="flex items-center justify-between mb-1">
              <p class="text-sm font-medium text-slate-800">{{ $wallet->name }}</p>
              <span class="{{ $preview['count'] > 0 ? 'badge-yellow' : 'badge-green' }} badge">
                {{ $preview['count'] > 0 ? $preview['count'] . ' pending' : 'Clear' }}
              </span>
            </div>
            @if($preview['count'] > 0)
            <p class="text-xs text-slate-500">
              Unsettled: <span class="font-semibold text-slate-700">
                {{ $wallet->formatAmount($preview['total']) }}
              </span>
            </p>
            @else
            <p class="text-xs text-slate-400">No unsettled transactions.</p>
            @endif
          </div>
          @endforeach
        </div>
        @endif
      </div>

      {{-- What is settlement callout --}}
      <div class="card p-5 bg-brand-50 border-brand-100">
        <h4 class="text-sm font-semibold text-brand-900 mb-2">How settlement works</h4>
        <ol class="text-xs text-brand-700 space-y-1.5 list-decimal list-inside leading-relaxed">
          <li>Successful debit transactions accumulate in a wallet</li>
          <li>A settlement run groups them into a batch</li>
          <li>The total is debited from the wallet as a single settlement</li>
          <li>A ledger entry records the settlement debit</li>
          <li>Each transaction is tagged with the batch reference</li>
        </ol>
      </div>
    </div>

  </div>

  {{-- JS to show preview panel for selected wallet --}}
  <script>
    const select = document.getElementById('wallet_select');
    const panels = document.querySelectorAll('.wallet-preview');

    function updatePreview() {
      const selectedId = select?.value;
      panels.forEach(panel => {
        panel.classList.toggle('hidden',
          panel.dataset.walletId !== selectedId);
      });
    }

    if (select) {
      select.addEventListener('change', updatePreview);
      updatePreview();
    }
  </script>

</x-app-layout>
