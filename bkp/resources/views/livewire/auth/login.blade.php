<div class="flex items-center justify-center min-h-screen bg-gradient-to-br from-neutral-200/60 to-neutral-400/30 dark:from-neutral-900 dark:to-neutral-800/90 p-4">

    <div class="w-full max-w-md bg-white/30 dark:bg-neutral-900/40 backdrop-blur-md rounded-2xl border border-white/30 dark:border-neutral-700 shadow-3xl p-8 space-y-8">
        <h2 class="text-2xl font-semibold text-neutral-800 dark:text-neutral-100 text-center drop-shadow-lg">
            Sign in to your account
        </h2>

        <form wire:submit.prevent="login" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Email address
                </label>
                <input wire:model.defer="email" id="email" name="email" type="email" required autofocus
                    class="mt-2 block w-full px-4 py-2 border border-neutral-200 dark:border-neutral-700 rounded-lg bg-white/70 dark:bg-neutral-900/60 text-neutral-800 dark:text-neutral-100 shadow-inner focus:outline-none focus:ring-2 focus:ring-red-500 backdrop-blur-sm transition"
                />
                @error('email')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                    Password
                </label>
                <input wire:model.defer="password" id="password" name="password" type="password" required
                    class="mt-2 block w-full px-4 py-2 border border-neutral-200 dark:border-neutral-700 rounded-lg bg-white/70 dark:bg-neutral-900/60 text-neutral-800 dark:text-neutral-100 shadow-inner focus:outline-none focus:ring-2 focus:ring-red-500 backdrop-blur-sm transition"
                />
                @error('password')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 select-none">
                    <input type="checkbox" wire:model="remember"
                        class="h-4 w-4 text-neutral-700 focus:ring-red-500 border-neutral-300 rounded-lg bg-white/70 dark:bg-neutral-900/60" />
                    <span class="text-neutral-600 dark:text-neutral-300">Remember me</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                        class="text-neutral-700 hover:underline dark:text-neutral-400">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit"
                class="w-full py-2 rounded-xl bg-gradient-to-r from-red-600 via-red-600 to-red-700 hover:from-red-600 hover:to-red-800 text-white font-semibold shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 transition backdrop-blur-sm">
                Sign In
            </button>
        </form>
    </div>
</div>
