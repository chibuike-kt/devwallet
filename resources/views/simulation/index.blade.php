<x-app-layout>
  <x-slot name="title">Simulation</x-slot>

  {{-- Header --}}
  <div class="flex items-center justify-between mb-8">
    <div>
      <h2 class="text-xl font-bold text-slate-900">Simulation Controls</h2>
      <p class="text-slate-400 text-sm mt-0.5">
        Control how DevWallet responds to API calls from your app.
      </p>
    </div>
    <form method="POST"
      action="{{ route('projects.simulation.reset', $project) }}">
      @csrf
      <button type="submit"
        class="btn-secondary text-sm"
        onclick="return confirm('Reset all simulation settings to defaults?')">
        Reset to defaults
      </button>
    </form>
  </div>

  @if(session('success'))
  <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200
                    rounded-xl text-sm text-emerald-700">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    {{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200
                    rounded-xl text-sm text-red-700">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    {{ session('error') }}
  </div>
  @endif

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ── Payment behaviour ───────────────────────────────────────────── --}}
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Payment behaviour</h3>
        <p class="text-xs text-slate-400 mt-0.5">
          Controls how transactions resolve when your app calls verify / confirm.
        </p>
      </div>
      <form method="POST"
        action="{{ route('projects.simulation.settings', $project) }}"
        class="card-body space-y-6">
        @csrf

        {{-- Failure rate slider --}}
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-slate-700">
              Failure rate
            </label>
            <span id="failure-rate-label"
              class="text-sm font-bold tabular-nums
                                     {{ $project->sim_failure_rate > 0 ? 'text-red-600' : 'text-emerald-600' }}">
              {{ $project->sim_failure_rate }}%
            </span>
          </div>
          <input type="range"
            id="failure-rate-slider"
            min="0" max="100" step="5"
            value="{{ $project->sim_failure_rate }}"
            class="w-full accent-brand-500"
            oninput="updateFailureLabel(this.value)">
          <div class="flex justify-between text-[10px] text-slate-400 mt-1">
            <span>0% — always succeed</span>
            <span>100% — always fail</span>
          </div>
          <p class="text-xs text-slate-400 mt-2">
            Each transaction has a
            <strong class="text-slate-700">{{ $project->sim_failure_rate }}%</strong>
            chance of failing when verified.
          </p>
        </div>

        {{-- Force next fail --}}
        <div class="flex items-start gap-4 p-4 rounded-xl
                            {{ $project->sim_force_next_fail ? 'bg-red-50 border border-red-200' : 'bg-slate-50 border border-slate-200' }}">
          <label class="flex items-center gap-3 cursor-pointer flex-1">
            <input type="checkbox"
              name="sim_force_next_fail"
              value="1"
              {{ $project->sim_force_next_fail ? 'checked' : '' }}
              class="w-4 h-4 text-red-600 rounded">
            <div>
              <p class="text-sm font-semibold text-slate-900">
                Force next transaction to fail
              </p>
              <p class="text-xs text-slate-500 mt-0.5">
                The very next verify / confirm call will return a failure.
                Automatically clears after one use.
              </p>
            </div>
          </label>
          @if($project->sim_force_next_fail)
          <span class="badge badge-red text-xs flex-shrink-0 mt-0.5">ARMED</span>
          @endif
        </div>

        {{-- Transfer delay --}}
        <div>
          <label class="text-sm font-medium text-slate-700 mb-2 block">
            Transfer processing speed
          </label>
          <div class="grid grid-cols-3 gap-2">
            @foreach([
            ['instant', 'Instant', '~0ms', 'Fires webhook immediately after response'],
            ['slow', 'Slow', '~5s', 'Simulates delayed bank processing'],
            ['timeout', 'Timeout', '~30s', 'Simulates network timeout / stuck transfer'],
            ] as [$val, $label, $time, $desc])
            <label class="cursor-pointer">
              <input type="radio"
                name="sim_transfer_delay"
                value="{{ $val }}"
                class="sr-only peer"
                {{ $project->sim_transfer_delay === $val ? 'checked' : '' }}>
              <div class="p-3 rounded-xl border-2 text-center transition-all
                                        peer-checked:border-brand-500 peer-checked:bg-brand-50
                                        border-slate-200 hover:border-slate-300">
                <p class="text-sm font-semibold text-slate-900">{{ $label }}</p>
                <p class="text-xs font-mono text-slate-500 mt-0.5">{{ $time }}</p>
              </div>
            </label>
            @endforeach
          </div>
          <p class="text-xs text-slate-400 mt-2">
            @if($project->sim_transfer_delay === 'slow')
            Webhooks for transfers will fire ~5 seconds after the API response.
            @elseif($project->sim_transfer_delay === 'timeout')
            Webhooks for transfers will fire ~30 seconds after the API response —
            useful for testing retry logic.
            @else
            Webhooks fire immediately in the same request cycle.
            @endif
          </p>
        </div>

        {{-- Hidden fields needed for validation --}}
        <input type="hidden" name="sim_failure_rate"
          id="failure-rate-hidden" value="{{ $project->sim_failure_rate }}">

        <button type="submit" class="btn-primary w-full">
          Save settings
        </button>

      </form>
    </div>

    {{-- ── Manual webhook trigger ───────────────────────────────────────── --}}
    <div class="space-y-5">

      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Fire a webhook manually</h3>
          <p class="text-xs text-slate-400 mt-0.5">
            Send a real webhook payload to your registered endpoints right now.
            Useful for testing your webhook handler without making a full payment.
          </p>
        </div>
        <form method="POST"
          action="{{ route('projects.simulation.webhook', $project) }}"
          class="card-body space-y-4">
          @csrf

          {{-- Event type --}}
          <div>
            <label class="form-label">Event type</label>
            <select name="event_type"
              id="webhook-event-type"
              class="form-input"
              onchange="toggleWebhookSource(this.value)">
              <option value="charge.success">charge.success</option>
              <option value="transfer.success">transfer.success</option>
              <option value="transfer.failed">transfer.failed</option>
            </select>
          </div>

          {{-- Transaction selector (for charge events) --}}
          <div id="transaction-selector">
            <label class="form-label">Transaction (optional)</label>
            <select name="transaction_id" class="form-input">
              <option value="">Most recent successful transaction</option>
              @foreach($recentTransactions as $tx)
              <option value="{{ $tx->id }}">
                {{ Str::limit($tx->reference, 20) }}
                — ₦{{ number_format($tx->amount / 100, 2) }}
                ({{ $tx->customer?->email ?? 'no email' }})
              </option>
              @endforeach
            </select>
            @if($recentTransactions->isEmpty())
            <p class="text-xs text-amber-600 mt-1">
              No successful transactions yet. Make a payment first.
            </p>
            @endif
          </div>

          {{-- Transfer selector (for transfer events) --}}
          <div id="transfer-selector" class="hidden">
            <label class="form-label">Transfer (optional)</label>
            <select name="transfer_id" class="form-input">
              <option value="">Most recent transfer</option>
              @foreach($recentTransfers as $transfer)
              <option value="{{ $transfer->id }}">
                {{ $transfer->transfer_code }}
                — ₦{{ number_format($transfer->amount / 100, 2) }}
                → {{ $transfer->recipient_name }}
              </option>
              @endforeach
            </select>
            @if($recentTransfers->isEmpty())
            <p class="text-xs text-amber-600 mt-1">
              No transfers yet. Make a transfer first.
            </p>
            @endif
          </div>

          {{-- Endpoints --}}
          @if($endpoints->isEmpty())
          <div class="p-3 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-700">
            No active webhook endpoints.
            <a href="{{ route('projects.webhooks.index', $project) }}"
              class="underline font-medium">Add one →</a>
          </div>
          @else
          <div class="p-3 rounded-lg bg-slate-50 border border-slate-200">
            <p class="text-xs font-medium text-slate-500 mb-1.5">
              Will fire to {{ $endpoints->count() }} endpoint(s):
            </p>
            @foreach($endpoints as $endpoint)
            <p class="font-mono text-xs text-slate-700 truncate">
              {{ $endpoint->url }}
            </p>
            @endforeach
          </div>
          @endif

          <button type="submit"
            class="btn-primary w-full"
            {{ $endpoints->isEmpty() ? 'disabled' : '' }}>
            Fire webhook →
          </button>

        </form>
      </div>

      {{-- ── Current state summary ────────────────────────────────────── --}}
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900 text-sm">Current simulation state</h3>
        </div>
        <div class="card-body space-y-0">

          @php
          $stateItems = [
          [
          'label' => 'Failure rate',
          'value' => $project->sim_failure_rate . '%',
          'ok' => $project->sim_failure_rate === 0,
          'warn' => $project->sim_failure_rate > 0,
          ],
          [
          'label' => 'Force next fail',
          'value' => $project->sim_force_next_fail ? 'ARMED' : 'Off',
          'ok' => !$project->sim_force_next_fail,
          'warn' => $project->sim_force_next_fail,
          ],
          [
          'label' => 'Transfer speed',
          'value' => ucfirst($project->sim_transfer_delay),
          'ok' => $project->sim_transfer_delay === 'instant',
          'warn' => $project->sim_transfer_delay !== 'instant',
          ],
          ];
          @endphp

          @foreach($stateItems as $item)
          <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
            <p class="text-xs font-medium text-slate-500">{{ $item['label'] }}</p>
            <div class="flex items-center gap-2">
              <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                {{ $item['ok'] ? 'bg-emerald-400' : 'bg-red-400' }}">
              </span>
              <span class="text-sm font-semibold
                                {{ $item['ok'] ? 'text-slate-700' : 'text-red-600' }}">
                {{ $item['value'] }}
              </span>
            </div>
          </div>
          @endforeach

          {{-- Provider base URL reminder --}}
          <div class="pt-3">
            <p class="text-xs font-medium text-slate-400 mb-1.5">Your base URL</p>
            <div class="flex items-center gap-2 bg-slate-900 rounded-lg px-3 py-2">
              <code class="font-mono text-xs text-emerald-400 flex-1 truncate">
                {{ $project->providerBaseUrl() }}
              </code>
              <button onclick="navigator.clipboard.writeText('{{ $project->providerBaseUrl() }}')"
                class="text-slate-400 hover:text-white transition-colors flex-shrink-0"
                title="Copy">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2-2v8a2 2 0 002 2z" />
                </svg>
              </button>
            </div>
          </div>

        </div>
      </div>

    </div>

  </div>

</x-app-layout>

<script>
  function updateFailureLabel(value) {
    const label = document.getElementById('failure-rate-label');
    const hidden = document.getElementById('failure-rate-hidden');
    label.textContent = value + '%';
    hidden.value = value;
    label.className = label.className.replace(/text-(red|emerald)-600/, '');
    label.classList.add(value > 0 ? 'text-red-600' : 'text-emerald-600');
  }

  function toggleWebhookSource(eventType) {
    const txSelector = document.getElementById('transaction-selector');
    const tfrSelector = document.getElementById('transfer-selector');
    const isTransfer = eventType.startsWith('transfer');
    txSelector.classList.toggle('hidden', isTransfer);
    tfrSelector.classList.toggle('hidden', !isTransfer);
  }
</script>
