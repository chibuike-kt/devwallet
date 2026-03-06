<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DevWallet') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-surface-50">

    {{-- Top nav bar for guest --}}
    <nav class="border-b border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('welcome') }}" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-slate-900 font-semibold text-base tracking-tight">DevWallet</span>
                </a>
                <div class="flex items-center gap-3">
                    @auth
                    <a href="{{ route('dashboard') }}" class="btn-primary">Go to Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="btn-secondary">Sign in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Get started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Page content --}}
    <main>
        {{ $slot }}
    </main>

</body>

</html>
