<x-app-layout>
  <x-slot name="title">New Project</x-slot>

  <div class="max-w-2xl">

    {{-- Back link --}}
    <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-6 transition-colors">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
      Back to projects
    </a>

    <div class="mb-8">
      <h2 class="text-xl font-semibold text-slate-900">Create a new project</h2>
      <p class="text-slate-500 text-sm mt-1">Projects are isolated environments for your payment simulations.</p>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
          @csrf

          {{-- Name --}}
          <div>
            <label for="name" class="form-label">Project name <span class="text-red-400">*</span></label>
            <input id="name" name="name" type="text" required autofocus
              class="form-input @error('name') border-red-300 @enderror"
              value="{{ old('name') }}"
              placeholder="e.g. Kuda Wallet Engine" />
            @error('name')
            <p class="form-error">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-400">A short, memorable name for this simulation project.</p>
          </div>

          {{-- Description --}}
          <div>
            <label for="description" class="form-label">Description <span class="text-slate-400 font-normal">(optional)</span></label>
            <textarea id="description" name="description" rows="3"
              class="form-input resize-none @error('description') border-red-300 @enderror"
              placeholder="What payment flows or scenarios are you testing?">{{ old('description') }}</textarea>
            @error('description')
            <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          {{-- Environment --}}
          <div>
            <label class="form-label">Environment <span class="text-red-400">*</span></label>
            <div class="grid grid-cols-2 gap-3">
              @foreach(['test' => ['label' => 'Test', 'desc' => 'Safe experimentation. No real-world impact.', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'], 'staging' => ['label' => 'Staging', 'desc' => 'Pre-production validation environment.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z']] as $value => $env)
              <label class="relative flex cursor-pointer">
                <input type="radio" name="environment" value="{{ $value }}"
                  class="sr-only peer"
                  {{ old('environment', 'test') === $value ? 'checked' : '' }}>
                <div class="flex-1 border-2 border-slate-200 rounded-xl p-4 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition-all duration-150 hover:border-slate-300">
                  <div class="flex items-center gap-3 mb-2">
                    <svg class="w-4 h-4 text-slate-500 peer-checked:text-brand-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $env['icon'] }}" />
                    </svg>
                    <span class="text-sm font-semibold text-slate-800">{{ $env['label'] }}</span>
                  </div>
                  <p class="text-xs text-slate-500">{{ $env['desc'] }}</p>
                </div>
              </label>
              @endforeach
            </div>
            @error('environment')
            <p class="form-error">{{ $message }}</p>
            @enderror
          </div>

          {{-- Color --}}
          <div>
            <label class="form-label">Project color</label>
            <div class="flex items-center gap-3">
              @foreach(['#0e8de6', '#059669', '#7c3aed', '#db2777', '#d97706', '#dc2626', '#0891b2', '#65a30d'] as $color)
              <label class="cursor-pointer">
                <input type="radio" name="color" value="{{ $color }}"
                  class="sr-only peer"
                  {{ old('color', '#0e8de6') === $color ? 'checked' : '' }}>
                <div class="w-7 h-7 rounded-full ring-2 ring-offset-2 ring-transparent peer-checked:ring-slate-400 transition-all hover:scale-110"
                  style="background-color: {{ $color }}"></div>
              </label>
              @endforeach
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit" class="btn-primary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Create project
            </button>
            <a href="{{ route('projects.index') }}" class="btn-secondary">Cancel</a>
          </div>

        </form>
      </div>
    </div>

  </div>
</x-app-layout>
