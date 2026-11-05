<x-guest-layout>
    <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Buat akun baru</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-neutral-400">Daftar gratis dan mulai ikuti event pertamamu.</p>

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
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Nama lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   placeholder="Nama kamu"
                   class="mt-1 block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-muted border-slate-300 dark:border-line px-4 py-3 shadow-sm focus:border-neon focus:ring-neon">
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   placeholder="nama@email.com"
                   class="mt-1 block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-muted border-slate-300 dark:border-line px-4 py-3 shadow-sm focus:border-neon focus:ring-neon">
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Password</label>
            <div class="relative mt-1">
                <input id="password" :type="show ? 'text' : 'password'" name="password" x-model="password" required autocomplete="new-password"
                       placeholder="Minimal 8 karakter"
                       class="block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-muted border-slate-300 dark:border-line px-4 py-3 pr-12 shadow-sm focus:border-neon focus:ring-neon">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-4 text-slate-400 dark:text-neutral-500 hover:text-lime-600 inline-flex items-center" aria-label="Tampilkan password">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" /></svg>
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
                <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400" x-text="label"></p>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-neutral-300">Konfirmasi password</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" x-model="confirm" required autocomplete="new-password"
                   placeholder="Ulangi password"
                    class="mt-1 block w-full rounded-xl bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder-slate-500 dark:placeholder-muted border-slate-300 dark:border-line px-4 py-3 shadow-sm focus:border-neon focus:ring-neon">
            <p class="mt-1 text-xs" x-show="confirm.length > 0" x-cloak
               :class="confirm === password ? 'text-emerald-600' : 'text-red-500'"
               x-text="confirm === password ? 'Password cocok' : 'Password belum sama'"></p>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <button type="submit" :disabled="loading"
                class="w-full rounded-xl bg-ink hover:bg-neutral-800 py-3 font-semibold text-white shadow-md shadow-black/20 hover:shadow-lg transition disabled:opacity-60 disabled:cursor-not-allowed">
            <span x-show="!loading">Daftar</span>
            <span x-show="loading" x-cloak>Memproses...</span>
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-neutral-400">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-lime-600 hover:underline">Masuk</a>
    </p>
</x-guest-layout>
