<x-guest-layout>
    <h1 class="text-2xl font-extrabold text-slate-900">Selamat datang kembali 👋</h1>
    <p class="mt-1 text-sm text-slate-500">Masuk untuk mengelola event dan tiket kamu.</p>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5"
          x-data="{ email: @js(old('email', '')), password: '', show: false, loading: false }"
          @submit="loading = true">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" x-model="email" required autofocus autocomplete="username"
                   placeholder="nama@email.com"
                   class="mt-1 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative mt-1">
                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required autocomplete="current-password"
                       placeholder="Masukkan password"
                       class="block w-full rounded-xl border-slate-300 px-4 py-3 pr-12 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-indigo-600" aria-label="Tampilkan password">
                    <span x-text="show ? '🙈' : '👁️'"></span>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="inline-flex items-center gap-2 text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Ingat saya
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:underline">Lupa password?</a>
            @endif
        </div>

        <button type="submit" :disabled="loading"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-3 font-semibold text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:-translate-y-0.5 transition disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!loading">Masuk</span>
            <span x-show="loading" x-cloak>Memproses...</span>
        </button>

        {{-- Akun demo (hapus blok ini sebelum deploy sungguhan) --}}
        <div class="rounded-2xl border border-indigo-100 bg-indigo-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-700">Akun demo, klik untuk mengisi</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <button type="button" @click="email = 'organizer@eventku.test'; password = 'password'"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-600 hover:text-white transition">Organizer</button>
                <button type="button" @click="email = 'peserta@eventku.test'; password = 'password'"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-600 hover:text-white transition">Peserta</button>
                <button type="button" @click="email = 'admin@eventku.test'; password = 'password'"
                        class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200 hover:bg-indigo-600 hover:text-white transition">Admin</button>
            </div>
        </div>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:underline">Daftar sekarang</a>
    </p>
</x-guest-layout>