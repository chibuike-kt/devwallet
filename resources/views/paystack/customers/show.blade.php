<x-app-layout>
  <x-slot name="title">Customer</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.paystack.customers', $project) }}"
      class="hover:text-slate-600 transition-colors">Customers</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">{{ $customer->email }}</span>
  </div>

  {{-- Header --}}
  <div class="card p-6 mb-6">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
        <span class="text-brand-700 text-xl font-bold">
          {{ strtoupper(substr($customer->email, 0, 1)) }}
        </span>
      </div>
      <div>
        <h2 class="text-xl font-bold text-slate-900">{{ $customer->email }}</h2>
        <div class="flex items-center gap-3 mt-1">
          <span class="font-mono text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded">
            {{ $customer->customer_code }}
          </span>
          <span class="text-xs text-slate-400">
            {{ $customer->transactions->count() }} transaction(s)
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Details --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Customer details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['Email', $customer->email, false],
        ['First name', $customer->first_name ?? '—', false],
        ['Last name', $customer->last_name ?? '—', false],
        ['Phone', $customer->phone ?? '—', false],
        ['Code', $customer->customer_code, true],
        ['Created', $customer->created_at->format('d M Y'), false],
        ] as [$label, $value, $mono])
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
          <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800' }}">
            {{ $value }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    {{-- Transactions --}}
    <div class="card lg:col-span-2 overflow-hidden">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Transactions</h3>
      </div>
      @if($customer->transactions->isEmpty())
      <div class="card-body text-center py-8">
        <p class="text-sm text-slate-400">No transactions yet.</p>
      </div>
      @else
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50/50">
            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Reference</th>
            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Amount</th>
            <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($customer->transactions as $tx)
          <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer"
            onclick="window.location='{{ route('projects.paystack.transactions.show', [$project, $tx->reference]) }}'">
            <td class="px-6 py-3.5">
              <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
                {{ Str::limit($tx->reference, 18) }}
              </span>
            </td>
            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
              ₦{{ number_format($tx->amount / 100, 2) }}
            </td>
            <td class="px-6 py-3.5 text-center">
              <span class="{{ $tx->statusBadgeClass() }} badge capitalize">
                {{ $tx->status }}
              </span>
            </td>
            <td class="px-6 py-3.5 text-right text-xs text-slate-400">
              {{ $tx->created_at->diffForHumans() }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>

  </div>

</x-app-layout>
