<x-app-layout>
    <x-slot name="title">New Wallet — {{ $project->name }}</x-slot>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-slate-400 mb-6">
        <a href="{{ route('projects.index') }}" class="hover:text-slate-600 transition-colors">Projects</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('projects.show', $project) }}" class="hover:text-slate-600 transition-colors">{{ $project->name }}</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('projects.wallets.index', $project) }}" class="hover:text-slate-600 transition-colors">Wallets</a>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-slate-600">New</span>
    </div>

    <div class="max-w-xl">
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-slate-900">Create a wallet</h2>
            <p class="text-slate-500 text-sm mt-1">
                Wallets belong to <span class="font-medium text-slate-700">{{ $project->name }}</span>.
                Balances start at zero and are updated through simulation scenarios.
            </p>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('projects.wallets.store', $project) }}" class="space-y-6">
                    @csrf

                    {{-- Wallet name --}}
                    <div>
                        <label for="name" class="form-label">
                            Wallet name <span class="text-red-400">*</span>
                        </label>
                        <input id="name" name="name" type="text" required autofocus
                            class="form-input @error('name') border-red-300 @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. Main Wallet, Payout Account" />
                        @error('name')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Currency --}}
                    <div>
                        <label class="form-label">Currency <span class="text-red-400">*</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach([
                                'NGN' => ['symbol' => '₦', 'label' => 'Nigerian Naira', 'flag' => '🇳🇬'],
                                'USD' => ['symbol' => '$', 'label' => 'US Dollar',       'flag' => '🇺🇸'],
                                'KES' => ['symbol' => 'KSh', 'label' => 'Kenyan Shilling', 'flag' => '🇰🇪'],
                                'GHS' => ['symbol' => 'GH₵', 'label' => 'Ghanaian Cedi',  'flag' => '🇬🇭'],
                            ] as $code => $info)
                            <label class="relative flex cursor-pointer">
                                <input type="radio" name="currency" value="{{ $code }}"
                                    class="sr-only peer"
                                    {{ old('currency', 'NGN') === $code ? 'checked' : '' }}>
                                <div class="flex-1 border-2 border-slate-200 rounded-xl p-4 peer-checked:border-brand-500 peer-checked:bg-brand-50 transition-all duration-150 hover:border-slate-300">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-lg">{{ $info['flag'] }}</span>
                                        <span class="font-bold text-slate-800 text-sm">{{ $code }}</span>
                                        <span class="text-slate-400 text-sm">{{ $info['symbol'] }}</span>
                                    </div>
                                    <p class="text-xs text-slate-500">{{ $info['label'] }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('currency')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info callout --}}
                    <div class="flex items-start gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Wallets start with a zero balance. Use <strong class="text-slate-700">Simulation Scenarios</strong> to fund, debit, or transfer between wallets. Every balance change creates a ledger entry.
                        </p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                        <button type="submit" class="btn-primary">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Create wallet
                        </button>
                        <a href="{{ route('projects.wallets.index', $project) }}" class="btn-secondary">Cancel</a>
                    </div>

                </form>
            </div>
        </div>
    </div>

</x-app-layout>
