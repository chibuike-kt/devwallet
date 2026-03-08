<x-app-layout>
  <x-slot name="title">Projects</x-slot>

  {{-- Page header --}}
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Projects</h2>
      <p class="text-slate-500 text-sm mt-1">Each project is an isolated simulation environment.</p>
    </div>
    <a href="{{ route('projects.create') }}" class="btn-primary">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
      </svg>
      New project
    </a>
  </div>

  {{-- Flash message --}}
  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg">
    <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
    <p class="text-sm text-emerald-800">{{ session('success') }}</p>
  </div>
  @endif

  {{-- Projects grid --}}
  @if($projects->isEmpty())
  <div class="card">
    <div class="card-body flex flex-col items-center justify-center py-20 text-center">
      <div class="w-14 h-14 rounded-2xl bg-brand-50 flex items-center justify-center mb-5">
        <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
        </svg>
      </div>
      <h3 class="text-base font-semibold text-slate-900 mb-2">No projects yet</h3>
      <p class="text-sm text-slate-500 max-w-sm mb-6">
        Create your first project to start simulating wallet flows, transactions, and webhook events.
      </p>
      <a href="{{ route('projects.create') }}" class="btn-primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create your first project
      </a>
    </div>
  </div>
  @else
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($projects as $project)
    <a href="{{ route('projects.show', $project) }}"
      class="card hover:shadow-card-hover transition-all duration-200 group block">
      <div class="p-6">
        {{-- Project icon + name --}}
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
              style="background-color: {{ $project->color }}">
              {{ $project->initials() }}
            </div>
            <div>
              <h3 class="font-semibold text-slate-900 text-sm group-hover:text-brand-700 transition-colors">
                {{ $project->name }}
              </h3>
              <p class="text-xs text-slate-400 mt-0.5">{{ $project->slug }}</p>
            </div>
          </div>

          {{-- Environment badge --}}
          <span class="{{ $project->environment === 'staging' ? 'badge-yellow' : 'badge-blue' }} badge">
            {{ $project->environmentLabel() }}
          </span>
        </div>

        {{-- Description --}}
        @if($project->description)
        <p class="text-sm text-slate-500 leading-relaxed line-clamp-2 mb-5">
          {{ $project->description }}
        </p>
        @else
        <p class="text-sm text-slate-400 italic mb-5">No description provided.</p>
        @endif

        {{-- Footer meta --}}
        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
          <div class="flex items-center gap-4 text-xs text-slate-400">
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
              0 wallets
            </span>
            <span class="flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
              </svg>
              0 transactions
            </span>
          </div>
          <span class="text-xs text-slate-400">{{ $project->created_at->diffForHumans() }}</span>
        </div>
      </div>
    </a>
    @endforeach
  </div>
  @endif

</x-app-layout>
