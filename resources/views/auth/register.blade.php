<x-guest-layout>
    <div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">

            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-900">Create your workspace</h2>
                <p class="text-slate-500 mt-2">Start simulating African payment flows in minutes</p>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="name" class="form-label">Full name</label>
                            <input id="name" name="name" type="text" autocomplete="name" required
                                class="form-input @error('name') border-red-300 @enderror"
                                value="{{ old('name') }}"
                                placeholder="Ada Okonkwo" />
                            @error('name')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="form-label">Email address</label>
                            <input id="email" name="email" type="email" autocomplete="email" required
                                class="form-input @error('email') border-red-300 @enderror"
                                value="{{ old('email') }}"
                                placeholder="ada@company.com" />
                            @error('email')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="form-label">Password</label>
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                class="form-input @error('password') border-red-300 @enderror"
                                placeholder="Min. 8 characters" />
                            @error('password')
                            <p class="form-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                class="form-input"
                                placeholder="Repeat your password" />
                        </div>
                        

                        <button type="submit" class="btn-primary w-full justify-center py-2.5">
                            Create account
                        </button>

                    </form>
                </div>
            </div>

            <p class="text-center mt-6 text-sm text-slate-500">
                Already have an account?
                <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:text-brand-700">Sign in</a>
            </p>

        </div>
    </div>
</x-guest-layout>
