<x-app-layout>
  <x-slot name="title">Customers</x-slot>

  <div class="flex items-center justify-between mb-6">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Customers</h2>
      <p class="text-slate-400 text-sm mt-0.5">
        Auto-created when transactions are initialized
      </p>
    </div>
  </div>

  <div class="card overflow-hidden">
    @if($customers->isEmpty())
    <div class="card-body py-16 text-center">
      <p class="text-sm text-slate-400">No customers yet.</p>
      <p class="text-xs text-slate-400 mt-1">
        Customers are created automatically when you initialize a transaction with an email.
      </p>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-slate-50/50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Customer</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Code</th>
          <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Transactions</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Created</th>
          <th class="px-6 py-3.5"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($customers as $customer)
        <tr class="hover:bg-slate-50/50 transition-colors">
          <td class="px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                <span class="text-brand-700 text-xs font-bold">
                  {{ strtoupper(substr($customer->email, 0, 1)) }}
                </span>
              </div>
              <div>
                <p class="text-sm font-medium text-slate-800">{{ $customer->email }}</p>
                @if($customer->first_name || $customer->last_name)
                <p class="text-xs text-slate-400">{{ $customer->fullName() }}</p>
                @endif
              </div>
            </div>
          </td>
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded">
              {{ $customer->customer_code }}
            </span>
          </td>
          <td class="px-6 py-4 text-center text-sm font-medium text-slate-700">
            {{ $customer->transactions_count }}
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $customer->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            <a href="{{ route('projects.paystack.customers.show', [$project, $customer->customer_code]) }}"
              class="text-brand-600 hover:text-brand-700 text-xs font-medium">
              View →
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($customers->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $customers->links() }}
    </div>
    @endif
    @endif
  </div>

</x-app-layout>
