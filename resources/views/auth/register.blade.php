<x-guest-layout>
    <h1 class="text-2xl font-extrabold text-slate-900">Buat akun baru 🎉</h1>
    <p class="mt-1 text-sm text-slate-500">Daftar gratis dan mulai ikuti event pertamamu.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-5"
          x-data="{
              password: '',
              confirm: '',
              show: false,
              loading: false,
              get score() {
                  let s = 0;
                  if (this.password.length >= 8) s++;
                  if (/[A-Z]/.test(this.password)) s++;
                  if (/[0-9]/.test(this.password)) s++;
                  if (/[^A-Za-z0-9]/.test(this.password)) s++;
                  return s;
              },
              get label() {
                  return this.password.length === 0 ? '' : ['Terlalu pendek', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][this.score];
              }
          }"
          @submit="loading = true">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="Nama kamu"
                   class="mt-1 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   placeholder="nama@email.com"
                   class="mt-1 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <div class="relative mt-1">
                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required autocomplete="new-password"
                       placeholder="Minimal 8 karakter"
                       class="block w-full rounded-xl border-slate-300 px-4 py-3 pr-12 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 text-slate-400 hover:text-indigo-600" aria-label="Tampilkan password">
                    <span x-text="show ? '🙈' : '👁️'"></span>
                </button>
            </div>

            {{-- Indikator kekuatan password --}}
            <div class="mt-2" x-show="password.length > 0" x-cloak x-transition>
                <div class="grid grid-cols-4 gap-1">
                    <template x-for="i in 4" :key="i">
                        <div class="h-1.5 rounded-full transition-colors duration-300"
                             :class="i <= score ? (score <= 1 ? 'bg-red-500' : score === 2 ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-slate-200'"></div>
                    </template>
                </div>
                <p class="mt-1 text-xs text-slate-500" x-text="label"></p>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi password</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" x-model="confirm" required autocomplete="new-password"
                   placeholder="Ulangi password"
                   class="mt-1 block w-full rounded-xl border-slate-300 px-4 py-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <p class="mt-1 text-xs" x-show="confirm.length > 0" x-cloak
               :class="confirm === password ? 'text-emerald-600' : 'text-red-500'"
               x-text="confirm === password ? '✓ Password cocok' : 'Password belum sama'"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" :disabled="loading"
                class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 py-3 font-semibold text-white shadow-lg shadow-indigo-500/30 hover:shadow-xl hover:-translate-y-0.5 transition disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!loading">Daftar</span>
            <span x-show="loading" x-cloak>Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:underline">Masuk</a>
    </p>
</x-guest-layout>