<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ (isset($title) ? $title . ' — ' : '') }}DevWallet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-[#f5f6fa] font-sans antialiased">

    <div class="flex h-full min-h-screen">

        {{-- ── Sidebar ─────────────────────────────────────────────────────────── --}}
        <aside class="w-64 bg-[#011B33] flex flex-col flex-shrink-0 fixed inset-y-0 left-0 z-30">

            {{-- Logo --}}
            <div class="h-16 flex items-center px-6 border-b border-white/10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-brand-500 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <span class="text-white font-bold text-base tracking-tight">DevWallet</span>
                    <span class="text-[10px] font-semibold text-brand-400 bg-brand-500/20 px-1.5 py-0.5 rounded-full">
                        SANDBOX
                    </span>
                </a>
            </div>

            {{-- Project switcher --}}
            @php $activeProject = session('active_project_id')
            ? \App\Models\Project::find(session('active_project_id'))
            : auth()->user()?->projects()->active()->first();
            @endphp

            <div class="px-4 py-3 border-b border-white/10">
                @if($activeProject)
                <a href="{{ route('projects.index') }}"
                    class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white/10 transition-colors group">
                    <div class="w-7 h-7 rounded-md flex items-center justify-center flex-shrink-0"
                        style="background-color: {{ $activeProject->color ?? '#0e8de6' }}22">
                        <span class="text-xs font-bold"
                            style="color: {{ $activeProject->color ?? '#0e8de6' }}">
                            {{ $activeProject->initials() }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-semibold truncate">
                            {{ $activeProject->name }}
                        </p>
                        <p class="text-white/40 text-[10px] uppercase tracking-wider">
                            {{ $activeProject->environment }} environment
                        </p>
                    </div>
                    <svg class="w-3.5 h-3.5 text-white/30 group-hover:text-white/60 transition-colors flex-shrink-0"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </a>
                @else
                <a href="{{ route('projects.create') }}"
                    class="flex items-center gap-2 p-2 rounded-lg border border-dashed border-white/20
                          hover:border-white/40 transition-colors">
                    <svg class="w-4 h-4 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span class="text-white/40 text-xs">Create a project</span>
                </a>
                @endif
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

                {{-- Main nav --}}
                <x-sidebar-link route="dashboard" icon="overview">
                    Overview
                </x-sidebar-link>

                @if($activeProject)
                <x-sidebar-link :route="route('projects.paystack.transactions', $activeProject)"
                    active="{{ request()->routeIs('projects.paystack.transactions*') }}"
                    icon="transactions">
                    Transactions
                </x-sidebar-link>

                <x-sidebar-link :route="route('projects.paystack.transfers', $activeProject)"
                    active="{{ request()->routeIs('projects.paystack.transfers*') }}"
                    icon="transfers">
                    Transfers
                </x-sidebar-link>

                <x-sidebar-link :route="route('projects.paystack.customers', $activeProject)"
                    active="{{ request()->routeIs('projects.paystack.customers*') }}"
                    icon="customers">
                    Customers
                </x-sidebar-link>

                <x-sidebar-link :route="route('projects.webhooks.index', $activeProject)"
                    active="{{ request()->routeIs('projects.webhooks.*') }}"
                    icon="webhooks">
                    Webhooks
                </x-sidebar-link>

                <x-sidebar-link :route="route('projects.scenarios.index', $activeProject)"
                    active="{{ request()->routeIs('projects.scenarios.*') }}"
                    icon="simulation">
                    Simulation
                </x-sidebar-link>

                <x-sidebar-link :route="route('projects.api-keys.index', $activeProject)"
                    active="{{ request()->routeIs('projects.api-keys.*') }}"
                    icon="keys">
                    API Keys
                </x-sidebar-link>

                {{-- Advanced section --}}
                <div x-data="{ open: {{ request()->routeIs('projects.wallets.*', 'projects.settlements.*', 'audit.*') ? 'true' : 'false' }} }"
                    class="pt-3">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-3 py-1.5 text-white/30
                                   hover:text-white/50 transition-colors text-[10px] font-semibold
                                   uppercase tracking-widest">
                        <span>Advanced</span>
                        <svg class="w-3 h-3 transition-transform" :class="open ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="mt-0.5 space-y-0.5">
                        <x-sidebar-link :route="route('projects.wallets.index', $activeProject)"
                            active="{{ request()->routeIs('projects.wallets.*') }}"
                            icon="wallets">
                            Wallets
                        </x-sidebar-link>

                        <x-sidebar-link :route="route('projects.settlements.index', $activeProject)"
                            active="{{ request()->routeIs('projects.settlements.*') }}"
                            icon="settlements">
                            Settlements
                        </x-sidebar-link>

                        <x-sidebar-link route="audit.index" icon="audit">
                            Audit Log
                        </x-sidebar-link>
                    </div>
                </div>
                @endif

            </nav>

            {{-- User footer --}}
            <div class="border-t border-white/10 px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-brand-500/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-brand-300 text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-white/40 text-[10px] truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-white/30 hover:text-white/70 transition-colors p-1"
                            title="Log out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        {{-- ── Main content ────────────────────────────────────────────────────── --}}
        <div class="flex-1 flex flex-col ml-64 min-h-screen">

            {{-- Top bar --}}
            <header class="h-16 bg-white border-b border-slate-100 flex items-center
                        justify-between px-8 sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    @if(isset($title))
                    <h1 class="text-sm font-semibold text-slate-900">{{ $title }}</h1>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex items-center gap-1.5 text-xs text-emerald-600
                             bg-emerald-50 px-2.5 py-1 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Sandbox
                    </span>
                    @if($activeProject)
                    <a href="{{ route('projects.api-keys.index', $activeProject) }}"
                        class="text-xs text-slate-500 hover:text-slate-700 transition-colors font-medium">
                        API Keys →
                    </a>
                    @endif
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 px-8 py-7">
                {{ $slot }}
            </main>

        </div>

    </div>

    {{-- Alpine for advanced section toggle --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

</body>

</html>
