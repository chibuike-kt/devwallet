<x-app-layout>
  <x-slot name="title">New Project</x-slot>

  <div class="max-w-2xl">
    <div class="mb-8">
      <h2 class="text-xl font-bold text-slate-900">Create a project</h2>
      <p class="text-slate-400 text-sm mt-1">
        Choose your payment provider and environment to get started.
      </p>
    </div>

    <form method="POST" action="{{ route('projects.store') }}" class="space-y-6">
      @csrf

      {{-- Provider selection --}}
      <div>
        <label class="form-label text-sm font-semibold text-slate-700 mb-3 block">
          Payment provider <span class="text-red-400">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
          @foreach([
          [
          'value' => 'paystack',
          'label' => 'Paystack',
          'description' => 'Nigerian-first payment gateway',
          'color' => '#00C3F7',
          'base_url' => '/api/paystack',
          'icon' => 'P',
          ],
          [
          'value' => 'flutterwave',
          'label' => 'Flutterwave',
          'description' => 'Pan-African payments',
          'color' => '#F5A623',
          'base_url' => '/api/flutterwave/v3',
          'icon' => 'F',
          ],
          [
          'value' => 'stripe',
          'label' => 'Stripe',
          'description' => 'Global payments platform',
          'color' => '#635BFF',
          'base_url' => '/api/stripe/v1',
          'icon' => 'S',
          ],
          ] as $provider)
          <label class="provider-card relative cursor-pointer">
            <input type="radio" name="provider"
              value="{{ $provider['value'] }}"
              class="sr-only peer"
              {{ old('provider', 'paystack') === $provider['value'] ? 'checked' : '' }}>
            <div class="p-4 rounded-xl border-2 border-slate-200
                                    peer-checked:border-[{{ $provider['color'] }}]
                                    peer-checked:bg-slate-50
                                    hover:border-slate-300 transition-all">
              <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center
                                            text-white font-bold text-sm flex-shrink-0"
                  style="background-color: {{ $provider['color'] }}">
                  {{ $provider['icon'] }}
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-900">
                    {{ $provider['label'] }}
                  </p>
                  <p class="text-xs text-slate-400">
                    {{ $provider['description'] }}
                  </p>
                </div>
              </div>
              <p class="font-mono text-[10px] text-slate-400 bg-slate-100
                                       px-2 py-1 rounded truncate">
                {{ url($provider['base_url']) }}
              </p>
            </div>
          </label>
          @endforeach
        </div>
        @error('provider')
        <p class="form-error mt-2">{{ $message }}</p>
        @enderror
      </div>

      {{-- Project name --}}
      <div>
        <label for="name" class="form-label">
          Project name <span class="text-red-400">*</span>
        </label>
        <input id="name" name="name" type="text" required autofocus
          class="form-input @error('name') border-red-300 @enderror"
          value="{{ old('name') }}"
          placeholder="e.g. My Checkout Integration">
        @error('name')
        <p class="form-error">{{ $message }}</p>
        @enderror
      </div>

      {{-- Description --}}
      <div>
        <label for="description" class="form-label">Description</label>
        <input id="description" name="description" type="text"
          class="form-input"
          value="{{ old('description') }}"
          placeholder="What are you testing?">
      </div>

      {{-- Environment --}}
      <div>
        <label class="form-label">Environment</label>
        <div class="flex gap-3">
          @foreach(['test' => 'Test', 'staging' => 'Staging'] as $val => $label)
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="environment" value="{{ $val }}"
              class="text-brand-600"
              {{ old('environment', 'test') === $val ? 'checked' : '' }}>
            <span class="text-sm text-slate-700">{{ $label }}</span>
          </label>
          @endforeach
        </div>
      </div>

      <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
        <button type="submit" class="btn-primary">Create project</button>
        <a href="{{ route('projects.index') }}" class="btn-secondary">Cancel</a>
      </div>

    </form>
  </div>
</x-app-layout>
