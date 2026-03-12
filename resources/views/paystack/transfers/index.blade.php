<x-app-layout>
  <x-slot name="title">Transfers</x-slot>

  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Transfers</h2>
      <p class="text-slate-400 text-sm mt-0.5">Outbound transfers for {{ $project->name }}</p>
    </div>
  </div>

  {{-- Filter --}}
  <div class="card p-4 mb-6">
    <form method="GET" class="flex items-end gap-3">
      <div class="w-44">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
          <option value="">All</option>
          <option value="pending" {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
          <option value="success" {{ request('status') === 'success'  ? 'selected' : '' }}>Success</option>
          <option value="failed" {{ request('status') === 'failed'   ? 'selected' : '' }}>Failed</option>
          <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>Reversed</option>
        </select>
      </div>
      <button type="submit" class="btn-primary">Filter</button>
      @if(request('status'))
      <a href="{{ route('projects.paystack.transfers', $project) }}" class="btn-secondary">Clear</a>
      @endif
    </form>
  </div>

  <div class="card overflow-hidden">
    @if($transfers->isEmpty())
    <div class="card-body py-16 text-center">
      <p class="text-sm text-slate-400">No transfers yet.</p>
      <p class="text-xs text-slate-400 mt-1">
        POST to <code class="font-mono bg-slate-100 px-1 rounded">/api/paystack/transfer</code> to create one.
      </p>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transfer code</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Recipient</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
          <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
          <th class="px-6 py-3.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($transfers as $transfer)
        <tr class="hover:bg-slate-50/50 transition-colors">
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
              {{ $transfer->transfer_code }}
            </span>
          </td>
          <td class="px-6 py-4">
            <p class="text-sm font-medium text-slate-800">{{ $transfer->recipient_name }}</p>
            <p class="text-xs text-slate-400 mt-0.5">
              {{ $transfer->recipient_account_number }} · {{ $transfer->recipient_bank_name }}
            </p>
          </td>
          <td class="px-6 py-4 text-right font-semibold text-slate-900">
            ₦{{ number_format($transfer->amount / 100, 2) }}
          </td>
          <td class="px-6 py-4 text-center">
            <span class="{{ $transfer->statusBadgeClass() }} badge capitalize">
              {{ $transfer->status }}
            </span>
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $transfer->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            <a href="{{ route('projects.paystack.transfers.show', [$project, $transfer->reference]) }}"
              class="text-brand-600 hover:text-brand-700 text-xs font-medium">
              View →
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($transfers->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $transfers->links() }}
    </div>
    @endif
    @endif
  </div>

</x-app-layout>
