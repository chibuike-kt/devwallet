<x-app-layout>
  <x-slot name="title">{{ $batch->reference }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.settlements.index', $project) }}" class="hover:text-slate-600 transition-colors">Settlements</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600 font-mono text-xs">{{ $batch->reference }}</span>
  </div>

  {{-- Batch header --}}
  <div class="card p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <span class="{{ $batch->statusBadgeClass() }} badge capitalize">
            {{ $batch->status }}
          </span>
          <span class="badge badge-slate">
            {{ $batch->transaction_count }} transactions
          </span>
        </div>
        <p class="font-mono text-sm font-semibold text-slate-800">{{ $batch->reference }}</p>
        @if($batch->notes)
        <p class="text-sm text-slate-500 mt-1">{{ $batch->notes }}</p>
        @endif
      </div>
      <div class="text-right">
        <p class="text-3xl font-bold text-slate-900">{{ $batch->formattedTotal() }}</p>
        <p class="text-xs text-slate-400 mt-1">
          Settled {{ $batch->settled_at?->format('d M Y, H:i') ?? '—' }}
        </p>
      </div>
    </div>
  </div>

  {{-- Batch details + transactions --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Batch details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Batch details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['label' => 'Reference', 'value' => $batch->reference, 'mono' => true],
        ['label' => 'Status', 'value' => ucfirst($batch->status), 'mono' => false],
        ['label' => 'Wallet', 'value' => $batch->wallet->name, 'mono' => false],
        ['label' => 'Currency', 'value' => $batch->currency, 'mono' => false],
        ['label' => 'Total', 'value' => $batch->formattedTotal(), 'mono' => false],
        ['label' => 'Transactions', 'value' => $batch->transaction_count, 'mono' => false],
        ['label' => 'Settled at', 'value' => $batch->settled_at?->format('d M Y, H:i') ?? '—', 'mono' => false],
        ['label' => 'Created', 'value' => $batch->created_at->format('d M Y, H:i'), 'mono' => false],
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

    {{-- Included transactions --}}
    <div class="card lg:col-span-2 overflow-hidden">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Included transactions</h3>
      </div>
      @if($batch->transactions->isEmpty())
      <div class="card-body text-center py-8">
        <p class="text-sm text-slate-400">No transactions linked to this batch.</p>
      </div>
      @else
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-surface-50">
            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Reference</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
            <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($batch->transactions->where('type', '!=', 'settlement') as $tx)
          <tr class="hover:bg-surface-50 transition-colors">
            <td class="px-6 py-3.5">
              <a href="{{ route('projects.transactions.show', [$project, $tx]) }}"
                class="font-mono text-xs text-brand-600 hover:text-brand-700 bg-brand-50 px-2 py-1 rounded">
                {{ Str::limit($tx->reference, 20) }}
              </a>
            </td>
            <td class="px-6 py-3.5">
              <span class="{{ $tx->type->badgeClass() }} badge">
                {{ $tx->type->label() }}
              </span>
            </td>
            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
              {{ $tx->formattedAmount() }}
            </td>
            <td class="px-6 py-3.5 text-center">
              <span class="{{ $tx->status->badgeClass() }} badge">
                {{ $tx->status->label() }}
              </span>
            </td>
            <td class="px-6 py-3.5 text-right text-xs text-slate-400">
              {{ $tx->created_at->diffForHumans() }}
            </td>
          </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="border-t-2 border-slate-200 bg-surface-50">
            <td colspan="2" class="px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase">
              Settlement Total
            </td>
            <td class="px-6 py-3.5 text-right font-bold text-slate-900">
              {{ $batch->formattedTotal() }}
            </td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
      @endif
    </div>

  </div>

</x-app-layout>
