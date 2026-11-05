<x-guest-layout>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Selamat datang kembali</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Masuk untuk mengelola event dan tiket kamu.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5"
          x-data="{ email: @js(old('email', '')), password: '', show: false, loading: false }"
          @submit="loading = true">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Email</label>
            <input id="email" type="email" name="email" x-model="email" required autofocus autocomplete="username"
                   placeholder="nama@email.com"
                    class="mt-1 block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-slate-400 border-slate-300 dark:border-line px-4 py-3 shadow-sm focus:border-lime-500 focus:ring-lime-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Password</label>
            <div class="relative mt-1">
                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required autocomplete="current-password"
                       placeholder="Masukkan password"
                        class="block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-slate-400 border-slate-300 dark:border-line px-4 py-3 pr-12 shadow-sm focus:border-lime-500 focus:ring-lime-500">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 text-slate-500 dark:text-neutral-300 hover:text-lime-600 inline-flex items-center" aria-label="Tampilkan password">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-slate-600 dark:text-neutral-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 dark:border-line text-lime-600 focus:ring-lime-500">
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-medium text-lime-600 hover:underline">Lupa password?</a>
            @endif
        </div>

        <button type="submit" :disabled="loading"
                class="w-full rounded-xl bg-ink hover:bg-neutral-800 py-3 font-semibold text-white shadow-md shadow-black/20 hover:shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!loading">Masuk</span>
            <span x-show="loading" x-cloak>Memproses...</span>
        </button>

        {{-- Password lokal tidak berlaku untuk akun deployment. Jangan expose secret hosting. --}}
        @php($localDemo = app()->environment('local'))
        <div class="rounded-2xl border border-lime-100 bg-lime-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-lime-600">{{ $localDemo ? 'Akun demo, klik untuk mengisi' : 'Akun demo, klik untuk mengisi email' }}</p>
            @unless ($localDemo)
                <p class="mt-2 text-sm text-slate-700">Gunakan password demo hosting dari pemilik EventKu. Password lokal tidak berlaku di website ini.</p>
            @endunless
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" @click="email = 'organizer@eventku.test'; password = @js($localDemo ? 'password' : '')"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-lime-600 border border-lime-200 hover:bg-ink hover:text-white transition">Organizer</button>
                <button type="button" @click="email = 'peserta@eventku.test'; password = @js($localDemo ? 'password' : '')"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-lime-600 border border-lime-200 hover:bg-ink hover:text-white transition">Peserta</button>
                <button type="button" @click="email = 'admin@eventku.test'; password = @js($localDemo ? 'password' : '')"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-lime-600 border border-lime-200 hover:bg-ink hover:text-white transition">Admin</button>
            </div>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-neutral-400">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-lime-600 hover:underline">Daftar sekarang</a>
    </p>
</x-guest-layout>
