<x-guest-layout>
    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
                <p class="text-slate-500 mt-2">Sign in to your DevWallet workspace</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="form-input @error('email') border-red-300 @enderror"
                                value="{{ old('email') }}"
                                placeholder="you@company.com" />
                            @error('email')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="form-label mb-0">Password</label>
                                @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:text-brand-700">
                                    Forgot password?
                                </a>
                                @endif
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                class="form-input @error('password') border-red-300 @enderror"
                                placeholder="••••••••" />
                            @error('password')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="remember_me" name="remember" type="checkbox"
                                class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500">
                            <label for="remember_me" class="text-sm text-slate-600">Remember me</label>
                        </div>

                        <button type="submit" class="btn-primary w-full justify-center py-2.5">
                            Sign in to DevWallet
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-6 text-sm text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:text-brand-700">Get started free</a>
            </p>

        </div>
    </div>
</x-guest-layout>
