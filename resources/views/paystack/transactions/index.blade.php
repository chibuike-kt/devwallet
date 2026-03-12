<x-app-layout>
  <x-slot name="title">Transactions</x-slot>

  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Transactions</h2>
      <p class="text-slate-400 text-sm mt-0.5">All payment transactions for {{ $project->name }}</p>
    </div>
  </div>

  {{-- Filters --}}
  <div class="card p-4 mb-6">
    <form method="GET" class="flex items-end gap-3 flex-wrap">
      <div class="flex-1 min-w-48">
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
          class="form-input" placeholder="Reference or email...">
      </div>
      <div class="w-44">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
          <option value="">All statuses</option>
          <option value="success" {{ request('status') === 'success'     ? 'selected' : '' }}>Success</option>
          <option value="failed" {{ request('status') === 'failed'      ? 'selected' : '' }}>Failed</option>
          <option value="initialized" {{ request('status') === 'initialized' ? 'selected' : '' }}>Initialized</option>
          <option value="abandoned" {{ request('status') === 'abandoned'   ? 'selected' : '' }}>Abandoned</option>
        </select>
      </div>
      <button type="submit" class="btn-primary">Filter</button>
      @if(request()->hasAny(['search', 'status']))
      <a href="{{ route('projects.paystack.transactions', $project) }}"
        class="btn-secondary">Clear</a>
      @endif
    </form>
  </div>

  {{-- Table --}}
  <div class="card overflow-hidden">
    @if($transactions->isEmpty())
    <div class="card-body py-16 text-center">
      <p class="text-sm text-slate-400">No transactions found.</p>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reference</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Customer</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Channel</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
          <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
          <th class="px-6 py-3.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($transactions as $tx)
        <tr class="hover:bg-slate-50/50 transition-colors">
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
              {{ Str::limit($tx->reference, 18) }}
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-slate-600">
            {{ $tx->customer?->email ?? '—' }}
          </td>
          <td class="px-6 py-4">
            <span class="badge badge-slate capitalize">{{ $tx->channel }}</span>
          </td>
          <td class="px-6 py-4 text-right font-semibold text-slate-900">
            ₦{{ number_format($tx->amount / 100, 2) }}
          </td>
          <td class="px-6 py-4 text-center">
            <span class="{{ $tx->statusBadgeClass() }} badge capitalize">
              {{ $tx->status }}
            </span>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $tx->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            <a href="{{ route('projects.paystack.transactions.show', [$project, $tx->reference]) }}"
              class="text-brand-600 hover:text-brand-700 text-xs font-medium">
              View →
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($transactions->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $transactions->links() }}
    </div>
    @endif
    @endif
  </div>

</x-app-layout>
