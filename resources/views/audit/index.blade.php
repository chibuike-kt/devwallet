<x-app-layout>
  <x-slot name="title">Audit Log</x-slot>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Audit Log</h2>
      <p class="text-slate-500 text-sm mt-1">
        Every significant action across your simulation environment.
      </p>
    </div>
    <span class="badge badge-green">
      <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
      Live
    </span>
  </div>

  @if($activities->isEmpty())
  <div class="card">
    <div class="card-body flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-5">
        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-900 mb-2">No audit events yet</h3>
      <p class="text-sm text-slate-500">Events are recorded as you create projects, run scenarios, and dispatch webhooks.</p>
    </div>
  </div>
  @else
  <div class="card overflow-hidden">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-100 bg-surface-50">
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Event</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Subject</th>
          <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Caused by</th>
          <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">When</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @foreach($activities as $activity)
        <tr class="hover:bg-surface-50 transition-colors">
          <td class="px-6 py-4 max-w-sm">
            <p class="text-sm text-slate-800">{{ $activity->description }}</p>
            @if($activity->subject_type)
            <p class="text-xs text-slate-400 mt-0.5">
              {{ class_basename($activity->subject_type) }}
              @if($activity->subject_id)
              #{{ $activity->subject_id }}
              @endif
            </p>
            @endif
          </td>
          <td class="px-6 py-4">
            @if($activity->subject)
            <span class="badge badge-slate text-xs">
              {{ class_basename($activity->subject_type) }}
            </span>
            @else
            <span class="text-xs text-slate-400">—</span>
            @endif
          </td>
          <td class="px-6 py-4">
            @if($activity->causer)
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                <span class="text-brand-700 text-xs font-semibold">
                  {{ strtoupper(substr($activity->causer->name, 0, 1)) }}
                </span>
              </div>
              <span class="text-xs text-slate-600">{{ $activity->causer->name }}</span>
            </div>
            @else
            <span class="text-xs text-slate-400">System</span>
            @endif
          </td>
          <td class="px-6 py-4 text-right">
            <span class="text-xs text-slate-400" title="{{ $activity->created_at->format('d M Y, H:i:s') }}">
              {{ $activity->created_at->diffForHumans() }}
            </span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @if($activities->hasPages())
    <div class="px-6 py-4 border-t border-slate-100">
      {{ $activities->links() }}
    </div>
    @endif
  </div>
  @endif

</x-app-layout>
