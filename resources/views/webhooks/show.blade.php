<x-app-layout>
  <x-slot name="title">Endpoint Deliveries</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.webhooks.index', $project) }}" class="hover:text-slate-600 transition-colors">Webhooks</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600 font-mono text-xs truncate">{{ Str::limit($webhook->url, 40) }}</span>
  </div>

  {{-- Endpoint header --}}
  <div class="card p-6 mb-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <span class="{{ $webhook->statusBadgeClass() }} badge capitalize">{{ $webhook->status }}</span>
          @if($webhook->description)
          <span class="text-sm text-slate-500">{{ $webhook->description }}</span>
          @endif
        </div>
        <p class="font-mono text-sm text-slate-800 font-semibold">{{ $webhook->url }}</p>
        <p class="text-xs text-slate-400 mt-2">
          Secret: <span class="font-mono bg-slate-100 px-2 py-0.5 rounded">{{ $webhook->maskedSecret() }}</span>
        </p>
      </div>
      <a href="{{ route('projects.webhooks.index', $project) }}" class="btn-secondary flex-shrink-0">
        ← Back
      </a>
    </div>
  </div>

  {{-- Delivery log --}}
  <div class="card overflow-hidden">
    <div class="card-header flex items-center justify-between">
      <h3 class="font-semibold text-slate-900 text-sm">Delivery log</h3>
      <span class="badge badge-slate">{{ $deliveries->total() }} attempt(s)</span>
    </div>

    @if($deliveries->isEmpty())
    <div class="card-body text-center py-12">
      <p class="text-sm text-slate-400">No delivery attempts yet for this endpoint.</p>
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-surface-50">
          <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
          <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
          <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">HTTP</th>
          <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Attempt</th>
          <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Duration</th>
          <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
          <th class="px-6 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($deliveries as $delivery)
        <tr class="hover:bg-surface-50 transition-colors">
          <td class="px-6 py-4">
            <span class="{{ $delivery->statusBadgeClass() }} badge capitalize">
              {{ $delivery->status }}
            </span>
          </td>
          <td class="px-6 py-4">
            <span class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
              {{ $delivery->event->event_type ?? '—' }}
            </span>
          </td>
          <td class="px-6 py-4 text-center">
            @if($delivery->http_status)
            <span class="font-mono text-xs font-semibold
                                    {{ $delivery->http_status >= 200 && $delivery->http_status < 300 ? 'text-emerald-600' : 'text-red-500' }}">
              {{ $delivery->http_status }}
            </span>
            @else
            <span class="text-xs text-slate-400">—</span>
            @endif
          </td>
          <td class="px-6 py-4 text-center text-xs text-slate-500">
            #{{ $delivery->attempt_number }}
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-500">
            {{ $delivery->duration_ms ? $delivery->duration_ms . 'ms' : '—' }}
          </td>
          <td class="px-6 py-4 text-right text-xs text-slate-400">
            {{ $delivery->created_at->diffForHumans() }}
          </td>
          <td class="px-6 py-4 text-right">
            @if($delivery->isFailed())
            <form method="POST"
              action="{{ route('projects.webhook-deliveries.retry', [$project, $delivery]) }}">
              @csrf
              <button type="submit"
                class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                ↺ Retry
              </button>
            </form>
            @endif
          </td>
        </tr>
        @if($delivery->failure_reason)
        <tr class="bg-red-50">
          <td colspan="7" class="px-6 py-2 text-xs text-red-600">
            {{ $delivery->failure_reason }}
          </td>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
    @if($deliveries->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $deliveries->links() }}
    </div>
    @endif
    @endif
  </div>

</x-app-layout>
