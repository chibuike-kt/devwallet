<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? '') ? $title . ' — ' : '' }}{{ config('app.name', 'DevWallet') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-50">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col w-64 bg-white border-r border-slate-200 flex-shrink-0">

            {{-- Logo --}}
            <div class="flex items-center gap-2.5 h-16 px-5 border-b border-slate-200">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <span class="text-slate-900 font-semibold text-sm tracking-tight">DevWallet</span>
                    <p class="text-slate-400 text-xs leading-tight">Simulation Platform</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

                @php
                $activeProjectId = session('active_project_id');
                $activeProject = $activeProjectId
                ? auth()->user()->projects()->find($activeProjectId)
                : auth()->user()->projects()->active()->first();
                @endphp

                <p class="px-3 pt-1 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Main</p>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Overview
                </a>

                <a href="{{ route('projects.index') }}"
                    class="sidebar-link {{ request()->routeIs('projects.index') || request()->routeIs('projects.create') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    Projects
                </a>

                {{-- Project-scoped section --}}
                @if($activeProject)

                <div class="pt-3 pb-1">
                    <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-50 border border-slate-200">
                        <div class="w-5 h-5 rounded flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                            style="background-color: {{ $activeProject->color }}">
                            {{ strtoupper(substr($activeProject->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700 truncate">{{ $activeProject->name }}</p>
                            <p class="text-xs text-slate-400">{{ $activeProject->environmentLabel() }}</p>
                        </div>
                        <a href="{{ route('projects.index') }}" title="Switch project"
                            class="text-slate-400 hover:text-slate-600 transition-colors flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </a>
                    </div>
                </div>

                <p class="px-3 pt-2 pb-1 text-xs font-semibold text-slate-400 uppercase tracking-wider">Simulation</p>

                <a href="{{ route('projects.scenarios.index', $activeProject) }}"
                    class="sidebar-link {{ request()->routeIs('projects.scenarios.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Scenarios
                </a>

                <a href="{{ route('projects.wallets.index', $activeProject) }}"
                    class="sidebar-link {{ request()->routeIs('projects.wallets.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Wallets
                </a>

                <a href="{{ route('projects.transactions.index', $activeProject) }}"
                    class="sidebar-link {{ request()->routeIs('projects.transactions.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transactions
                </a>

                
                <a href="{{ route('projects.webhooks.index', $activeProject) }}"
                    class="sidebar-link {{ request()->routeIs('projects.webhooks.*') ? 'active' : '' }}">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Webhooks
                </a>

                {{-- Ledger links per wallet --}}
                @php
                $sidebarWallets = $activeProject->wallets()->take(3)->get();
                @endphp
                @if($sidebarWallets->isNotEmpty())
                @foreach($sidebarWallets as $sidebarWallet)
                <a href="{{ route('projects.wallets.ledger', [$activeProject, $sidebarWallet]) }}"
                    class="sidebar-link pl-8 {{ request()->routeIs('projects.wallets.ledger') && request()->route('wallet')?->id === $sidebarWallet->id ? 'active' : '' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span class="truncate text-xs">{{ $sidebarWallet->name }} Ledger</span>
                </a>
                @endforeach
                @endif

                @else

                <p class="px-3 pt-4 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Simulation</p>

                @foreach(['Scenarios', 'Wallets', 'Transactions', 'Ledger'] as $item)
                <div class="sidebar-link opacity-50 cursor-not-allowed">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {{ $item }}
                    <span class="ml-auto badge badge-slate text-xs">No project</span>
                </div>
                @endforeach

                @endif

                <p class="px-3 pt-4 pb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">System</p>

                <a href="#" class="sidebar-link">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Audit Log
                    <span class="ml-auto badge badge-slate">Soon</span>
                </a>

            </nav>

            {{-- User footer --}}
            <div class="px-3 py-4 border-t border-slate-200">
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-surface-100 cursor-pointer transition-colors group">
                    <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-brand-700 text-xs font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Sign out"
                            class="text-slate-400 hover:text-slate-600 transition-colors opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        {{-- Main content area --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0">
                <div>
                    @isset($title)
                    <h1 class="text-base font-semibold text-slate-900">{{ $title }}</h1>
                    @endisset
                </div>
                <div class="flex items-center gap-3">
                    <span class="badge badge-green">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Sandbox Active
                    </span>
                </div>
            </header>

            {{-- Scrollable page content --}}
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>

        </div>

    </div>

</body>

</html>
