<x-app-layout>
  <x-slot name="title">Transfer</x-slot>

  <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
    <a href="{{ route('projects.paystack.transfers', $project) }}"
      class="hover:text-slate-600 transition-colors">Transfers</a>
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
    <span class="font-mono text-xs">{{ $transfer->transfer_code }}</span>
  </div>

  <div class="card p-6 mb-6">
    <div class="flex items-start justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <span class="{{ $transfer->statusBadgeClass() }} badge capitalize text-sm px-3 py-1">
            {{ $transfer->status }}
          </span>
        </div>
        <p class="text-3xl font-bold text-slate-900 mt-2">
          ₦{{ number_format($transfer->amount / 100, 2) }}
        </p>
        <p class="font-mono text-xs text-slate-400 mt-1">{{ $transfer->transfer_code }}</p>
      </div>
      <div class="text-right text-sm text-slate-500">
        <p>{{ $transfer->created_at->format('d M Y, H:i') }}</p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Transfer details</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['Transfer code', $transfer->transfer_code, true],
        ['Reference', $transfer->reference, true],
        ['Status', ucfirst($transfer->status), false],
        ['Amount', '₦' . number_format($transfer->amount / 100, 2), false],
        ['Narration', $transfer->narration ?? '—', false],
        ['Created', $transfer->created_at->format('d M Y, H:i:s'), false],
        ['Completed', $transfer->completed_at?->format('d M Y, H:i:s') ?? '—', false],
        ] as [$label, $value, $mono])
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
          <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800 font-medium' }}">
            {{ $value }}
          </p>
        </div>
        @endforeach
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-slate-900 text-sm">Recipient</h3>
      </div>
      <div class="card-body space-y-0">
        @foreach([
        ['Name', $transfer->recipient_name, false],
        ['Account number', $transfer->recipient_account_number, true],
        ['Bank code', $transfer->recipient_bank_code, false],
        ['Bank name', $transfer->recipient_bank_name, false],
        ] as [$label, $value, $mono])
        <div class="flex items-center justify-between py-3 border-b border-slate-100 last:border-0">
          <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
          <p class="{{ $mono ? 'font-mono text-xs bg-slate-100 px-2 py-1 rounded' : 'text-sm text-slate-800 font-medium' }}">
            {{ $value }}
          </p>
        </div>
        @endforeach
      </div>
    </div>
  </div>

</x-app-layout>
