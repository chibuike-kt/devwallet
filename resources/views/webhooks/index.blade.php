<x-app-layout>
  <x-slot name="title">Webhooks — {{ $project->name }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">Webhooks</span>
  </div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Webhook Simulator</h2>
      <p class="text-slate-500 text-sm mt-1">Register endpoints, dispatch events, inspect deliveries and retries.</p>
    </div>
    <a href="{{ route('projects.webhooks.create', $project) }}" class="btn-primary">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      Register endpoint
    </a>
  </div>

  {{-- Flash --}}
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

    {{-- Left: endpoints + dispatch --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Registered endpoints --}}
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 text-sm">Registered endpoints</h3>
          <span class="badge badge-slate">{{ $endpoints->count() }} endpoint(s)</span>
        </div>

        @if($endpoints->isEmpty())
        <div class="card-body flex flex-col items-center justify-center py-12 text-center">
          <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
          </div>
          <p class="text-sm font-medium text-slate-700 mb-1">No endpoints registered</p>
          <p class="text-xs text-slate-400 mb-5">Register a URL to receive webhook events from this project.</p>
          <a href="{{ route('projects.webhooks.create', $project) }}" class="btn-primary">
            Register first endpoint
          </a>
        </div>
        @else
        <div class="divide-y divide-slate-100">
          @foreach($endpoints as $endpoint)
          <div class="px-6 py-4 flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="{{ $endpoint->statusBadgeClass() }} badge capitalize">
                  {{ $endpoint->status }}
                </span>
                @if($endpoint->description)
                <span class="text-xs text-slate-500">{{ $endpoint->description }}</span>
                @endif
              </div>
              <p class="text-sm font-mono text-slate-700 truncate">{{ $endpoint->url }}</p>
              <p class="text-xs text-slate-400 mt-1">
                Secret: <span class="font-mono">{{ $endpoint->maskedSecret() }}</span>
              </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <a href="{{ route('projects.webhooks.show', [$project, $endpoint]) }}"
                class="btn-secondary text-xs py-1.5 px-3">
                Deliveries
              </a>
              <form method="POST" action="{{ route('projects.webhooks.destroy', [$project, $endpoint]) }}"
                onsubmit="return confirm('Remove this endpoint?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors p-1.5">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </form>
            </div>
          </div>
          @endforeach
        </div>
        @endif
      </div>

      {{-- Dispatch a webhook event --}}
      @if($endpoints->isNotEmpty())
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Dispatch webhook event</h3>
          <p class="text-xs text-slate-500 mt-0.5">Select a transaction and dispatch its event to all active endpoints.</p>
        </div>
        <div class="card-body">
          @php
          $dispatchable = \App\Models\Transaction::where('project_id', $project->id)
          ->with('wallet')
          ->latest()
          ->take(20)
          ->get();
          @endphp

          @if($dispatchable->isEmpty())
          <p class="text-sm text-slate-400 text-center py-4">
            No transactions yet. Run a scenario first.
          </p>
          @else
          <form method="POST" action="{{ route('projects.webhooks.dispatch', $project) }}"
            class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-64">
              <label class="form-label">Transaction</label>
              <select name="transaction_id" class="form-input">
                @foreach($dispatchable as $tx)
                <option value="{{ $tx->id }}">
                  {{ $tx->reference }} — {{ $tx->type->label() }} — {{ $tx->status->label() }}
                </option>
                @endforeach
              </select>
            </div>
            <div class="flex items-end gap-2">
              <button type="submit" name="simulate_failure" value="0" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Dispatch
              </button>
              <button type="submit" name="simulate_failure" value="1"
                class="btn-secondary border-red-200 text-red-600 hover:bg-red-50">
                Dispatch (fail)
              </button>
            </div>
          </form>
          @endif
        </div>
      </div>
      @endif

      {{-- Recent events table --}}
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Recent webhook events</h3>
        </div>
        @if($recentEvents->isEmpty())
        <div class="card-body text-center py-10">
          <p class="text-sm text-slate-400">No webhook events yet. Dispatch one above.</p>
        </div>
        @else
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 bg-surface-50">
              <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Transaction</th>
              <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Deliveries</th>
              <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($recentEvents as $event)
            <tr class="hover:bg-surface-50 transition-colors">
              <td class="px-6 py-3.5">
                <span class="{{ $event->eventTypeBadgeClass() }} badge font-mono text-xs">
                  {{ $event->event_type }}
                </span>
              </td>
              <td class="px-6 py-3.5">
                @if($event->transaction)
                <a href="{{ route('projects.transactions.show', [$project, $event->transaction]) }}"
                  class="font-mono text-xs text-brand-600 hover:text-brand-700">
                  {{ Str::limit($event->transaction->reference, 20) }}
                </a>
                @else
                <span class="text-xs text-slate-400">—</span>
                @endif
              </td>
              <td class="px-6 py-3.5 text-center">
                @php
                $successCount = $event->deliveries->where('status', 'success')->count();
                $failedCount = $event->deliveries->where('status', 'failed')->count();
                $total = $event->deliveries->count();
                @endphp
                <div class="flex items-center justify-center gap-1.5">
                  @if($successCount > 0)
                  <span class="badge-green badge text-xs">{{ $successCount }} ok</span>
                  @endif
                  @if($failedCount > 0)
                  <span class="badge-red badge text-xs">{{ $failedCount }} failed</span>
                  @endif
                  @if($total === 0)
                  <span class="badge-slate badge text-xs">none</span>
                  @endif
                </div>
              </td>
              <td class="px-6 py-3.5 text-right text-xs text-slate-400">
                {{ $event->created_at->diffForHumans() }}
              </td>
              <td class="px-6 py-3.5 text-right">
                <form method="POST"
                  action="{{ route('projects.webhooks.duplicate', [$project, $event]) }}">
                  @csrf
                  <button type="submit"
                    class="text-xs text-violet-600 hover:text-violet-700 font-medium transition-colors">
                    Duplicate →
                  </button>
                </form>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>
    </div>

    {{-- Right: delivery log --}}
    <div class="space-y-5">
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Recent deliveries</h3>
        </div>
        @php
        $recentDeliveries = \App\Models\WebhookDelivery::whereHas('event', function($q) use ($project) {
        $q->where('project_id', $project->id);
        })
        ->with(['event', 'endpoint'])
        ->latest()
        ->take(15)
        ->get();
        @endphp

        @if($recentDeliveries->isEmpty())
        <div class="card-body text-center py-8">
          <p class="text-xs text-slate-400">Delivery attempts will appear here.</p>
        </div>
        @else
        <div class="divide-y divide-slate-100">
          @foreach($recentDeliveries as $delivery)
          <div class="px-5 py-3.5">
            <div class="flex items-center justify-between mb-1.5">
              <span class="{{ $delivery->statusBadgeClass() }} badge text-xs capitalize">
                {{ $delivery->status }}
              </span>
              <div class="flex items-center gap-1.5">
                @if($delivery->http_status)
                <span class="font-mono text-xs text-slate-500">
                  HTTP {{ $delivery->http_status }}
                </span>
                @endif
                @if($delivery->duration_ms)
                <span class="text-xs text-slate-400">{{ $delivery->duration_ms }}ms</span>
                @endif
              </div>
            </div>
            <p class="font-mono text-xs text-slate-500 truncate">
              {{ $delivery->event->event_type ?? '—' }}
            </p>
            <p class="text-xs text-slate-400 mt-0.5">
              Attempt #{{ $delivery->attempt_number }} · {{ $delivery->created_at->diffForHumans() }}
            </p>
            @if($delivery->isFailed())
            <form method="POST"
              action="{{ route('projects.webhook-deliveries.retry', [$project, $delivery]) }}"
              class="mt-2">
              @csrf
              <button type="submit"
                class="text-xs text-brand-600 hover:text-brand-700 font-medium transition-colors">
                ↺ Retry delivery
              </button>
            </form>
            @endif
          </div>
          @endforeach
        </div>
        @endif
      </div>
    </div>

  </div>

</x-app-layout>
