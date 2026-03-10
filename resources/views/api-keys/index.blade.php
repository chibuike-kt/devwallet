<x-app-layout>
  <x-slot name="title">API Keys — {{ $project->name }}</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="text-slate-600">API Keys</span>
  </div>

  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">API Keys</h2>
      <p class="text-slate-500 text-sm mt-1">
        Authenticate requests to the DevWallet sandbox API from your own application.
      </p>
    </div>
  </div>

  {{-- New key reveal banner — shown once --}}
  @if($newKey)
  <div class="mb-6 p-5 bg-emerald-50 border border-emerald-200 rounded-xl">
    <div class="flex items-start gap-3">
      <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
      </svg>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-emerald-900 mb-1">
          Your new API key — copy it now
        </p>
        <p class="text-xs text-emerald-700 mb-3">
          This is the only time the full key will be shown. It cannot be recovered after you leave this page.
        </p>
        <div class="flex items-center gap-3">
          <code id="new-key-value"
            class="flex-1 font-mono text-sm bg-white border border-emerald-300 text-emerald-900 px-4 py-2.5 rounded-lg select-all">
            {{ $newKey }}
          </code>
          <button onclick="copyNewKey()"
            class="btn-primary flex-shrink-0 text-sm">
            Copy
          </button>
        </div>
      </div>
    </div>
  </div>
  @endif

  {{-- Flash messages --}}
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

    {{-- Left: key list --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- Existing keys --}}
      <div class="card overflow-hidden">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-slate-900 text-sm">Active keys</h3>
          <span class="badge badge-slate">
            {{ $keys->where('status', 'active')->count() }} / 10
          </span>
        </div>

        @if($keys->isEmpty())
        <div class="card-body flex flex-col items-center justify-center py-14 text-center">
          <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
          </div>
          <p class="text-sm font-medium text-slate-700 mb-1">No API keys yet</p>
          <p class="text-xs text-slate-400">
            Generate a key to start making authenticated requests to the DevWallet API.
          </p>
        </div>
        @else
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-100 bg-surface-50">
              <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Name</th>
              <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Key</th>
              <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Last used</th>
              <th class="px-6 py-3.5"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($keys as $key)
            <tr class="hover:bg-surface-50 transition-colors
                                {{ $key->isRevoked() ? 'opacity-50' : '' }}">
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-slate-800">{{ $key->name }}</p>
                <p class="text-xs text-slate-400 mt-0.5">
                  Created {{ $key->created_at->format('d M Y') }}
                </p>
              </td>
              <td class="px-6 py-4">
                <code class="font-mono text-xs text-slate-600 bg-slate-100 px-2 py-1 rounded">
                  {{ $key->maskedKey() }}
                </code>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="{{ $key->statusBadgeClass() }} badge capitalize">
                  {{ $key->status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-xs text-slate-400">
                {{ $key->last_used_at?->diffForHumans() ?? 'Never' }}
              </td>
              <td class="px-6 py-4 text-right">
                @if($key->isActive())
                <form method="POST"
                  action="{{ route('projects.api-keys.revoke', [$project, $key]) }}"
                  onsubmit="return confirm('Revoke key \'{{ addslashes($key->name) }}\'? Any app using it will lose access immediately.')">
                  @csrf
                  <button type="submit"
                    class="text-xs text-red-500 hover:text-red-700 font-medium transition-colors">
                    Revoke
                  </button>
                </form>
                @else
                <span class="text-xs text-slate-300">Revoked</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        @endif
      </div>

      {{-- Usage example --}}
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Usage example</h3>
        </div>
        <div class="card-body space-y-4">
          <p class="text-xs text-slate-500">
            Pass your key as a Bearer token in the <code class="font-mono bg-slate-100 px-1 rounded">Authorization</code> header on every API request.
          </p>

          {{-- cURL --}}
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">cURL</p>
            <pre class="bg-slate-900 text-emerald-400 text-xs font-mono rounded-xl p-4 overflow-x-auto leading-relaxed">curl -X POST {{ url('/api/v1/wallets/fund') }} \
  -H "Authorization: Bearer sk_test_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "wallet_reference": "WLT-XXXXXXXX",
    "amount": 5000,
    "currency": "NGN",
    "narration": "Customer deposit"
  }'</pre>
          </div>

          {{-- JavaScript --}}
          <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">JavaScript</p>
            <pre class="bg-slate-900 text-emerald-400 text-xs font-mono rounded-xl p-4 overflow-x-auto leading-relaxed">const response = await fetch('{{ url('/api/v1/wallets/fund') }}', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer sk_test_your_key_here',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    wallet_reference: 'WLT-XXXXXXXX',
    amount: 5000,
    currency: 'NGN',
    narration: 'Customer deposit',
  })
});

const data = await response.json();</pre>
          </div>
        </div>
      </div>
    </div>

    {{-- Right: generate new key --}}
    <div class="space-y-5">
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Generate new key</h3>
        </div>
        <div class="card-body">
          <form method="POST"
            action="{{ route('projects.api-keys.store', $project) }}"
            class="space-y-4">
            @csrf
            <div>
              <label class="form-label">
                Key name <span class="text-red-400">*</span>
              </label>
              <input type="text"
                name="name"
                class="form-input @error('name') border-red-300 @enderror"
                value="{{ old('name') }}"
                placeholder="e.g. Local development"
                autofocus>
              @error('name')
              <p class="form-error">{{ $message }}</p>
              @enderror
              <p class="text-xs text-slate-400 mt-1.5">
                Name it by environment or purpose.
              </p>
            </div>

            <button type="submit" class="btn-primary w-full justify-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Generate key
            </button>
          </form>
        </div>
      </div>

      {{-- Security note --}}
      <div class="card p-5 bg-amber-50 border-amber-100">
        <div class="flex items-start gap-3">
          <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="text-xs text-amber-800 leading-relaxed space-y-1.5">
            <p class="font-semibold">Keep your keys safe</p>
            <p>Keys are shown once and never stored in plaintext. If you lose a key, revoke it and generate a new one.</p>
            <p>Never commit keys to version control. Use environment variables.</p>
          </div>
        </div>
      </div>

      {{-- Environment variables tip --}}
      <div class="card p-5">
        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">
          .env example
        </p>
        <pre class="font-mono text-xs text-slate-700 leading-relaxed">DEVWALLET_API_KEY=sk_test_...
DEVWALLET_BASE_URL={{ url('/api/v1') }}</pre>
      </div>
    </div>

  </div>

  <script>
    function copyNewKey() {
      const val = document.getElementById('new-key-value')?.innerText?.trim();
      if (!val) return;
      navigator.clipboard.writeText(val).then(() => {
        const btn = event.target;
        btn.textContent = 'Copied!';
        setTimeout(() => btn.textContent = 'Copy', 2000);
      });
    }
  </script>

</x-app-layout>
