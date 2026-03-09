<x-app-layout>
  <x-slot name="title">Register Endpoint — {{ $project->name }}</x-slot>

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
    <span class="text-slate-600">Register</span>
  </div>

  <div class="max-w-2xl">
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-slate-900">Register webhook endpoint</h2>
      <p class="text-slate-500 text-sm mt-1">
        DevWallet will deliver event payloads to this URL when transactions occur.
      </p>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="{{ route('projects.webhooks.store', $project) }}" class="space-y-6">
          @csrf

          <div>
            <label for="url" class="form-label">
              Endpoint URL <span class="text-red-400">*</span>
            </label>
            <input id="url" name="url" type="url" required autofocus
              class="form-input @error('url') border-red-300 @enderror"
              value="{{ old('url') }}"
              placeholder="https://yourapp.com/webhooks/devwallet" />
            @error('url')
            <p class="form-error">{{ $message }}</p>
            @enderror
            <p class="mt-1.5 text-xs text-slate-400">
              Use <span class="font-mono bg-slate-100 px-1 rounded">https://webhook.site</span>
              for a free test receiver, or any real endpoint you control.
            </p>
          </div>

          <div>
            <label for="description" class="form-label">Description</label>
            <input id="description" name="description" type="text"
              class="form-input"
              value="{{ old('description') }}"
              placeholder="e.g. Production webhook handler" />
          </div>

          {{-- Event types --}}
          <div>
            <label class="form-label">Subscribe to events</label>
            <p class="text-xs text-slate-400 mb-3">Leave all unchecked to receive every event type.</p>
            <div class="grid grid-cols-2 gap-2">
              @foreach([
              'wallet.funded',
              'wallet.debited',
              'wallet.debit.failed',
              'transfer.success',
              'transfer.failed',
              'transfer.pending',
              'transaction.reversed',
              ] as $eventType)
              <label class="flex items-center gap-2.5 p-3 rounded-lg border border-slate-200 hover:border-slate-300 cursor-pointer transition-colors">
                <input type="checkbox" name="events[]" value="{{ $eventType }}"
                  class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500"
                  {{ in_array($eventType, old('events', [])) ? 'checked' : '' }}>
                <span class="font-mono text-xs text-slate-700">{{ $eventType }}</span>
              </label>
              @endforeach
            </div>
          </div>

          {{-- Info box --}}
          <div class="flex items-start gap-3 p-4 bg-brand-50 border border-brand-100 rounded-xl">
            <svg class="w-4 h-4 text-brand-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs text-brand-700 leading-relaxed">
              A signing secret will be auto-generated. Use it to verify the
              <span class="font-mono">X-DevWallet-Signature</span> header on incoming requests.
              Payloads are signed with HMAC-SHA256.
            </div>
          </div>

          <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit" class="btn-primary">Register endpoint</button>
            <a href="{{ route('projects.webhooks.index', $project) }}" class="btn-secondary">Cancel</a>
          </div>

        </form>
      </div>
    </div>
  </div>
</x-app-layout>
