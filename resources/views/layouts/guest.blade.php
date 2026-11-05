<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    <script>
        // Samakan tema gelap/terang dengan homepage (localStorage, default ikut OS).
        try {
            var t = localStorage.getItem('eventku-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 dark:text-neutral-200">
    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Panel kiri: branding (hanya tampil di layar lebar) --}}
        <div class="hidden lg:flex relative overflow-hidden flex-col justify-between p-12 text-white bg-ink">
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-black/40"></div>

            <a href="{{ route('home') }}" class="relative inline-flex items-center gap-2 text-2xl font-extrabold tracking-tight">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-lime-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                <span>{{ config('app.name') }}</span>
            </a>

            <div class="relative">
                <h2 class="text-4xl font-extrabold leading-tight">
                    Temukan dan ikuti event<br>favoritmu dengan mudah
                </h2>
                <p class="mt-4 text-neutral-300 max-w-md">
                    Beli tiket, dapatkan QR code, dan check-in cepat di pintu masuk.
                </p>
                <ul class="mt-8 space-y-3 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        </span>
                        Tiket digital langsung tersedia
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" /></svg>
                        </span>
                        QR check-in cepat dan aman
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="h-8 w-8 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-lime-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </span>
                        Dashboard untuk organizer
                    </li>
                </ul>
            </div>

            <p class="relative text-xs text-lime-300">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        {{-- Panel kanan: form --}}
        <div class="flex flex-col justify-center bg-cream dark:bg-deep px-4 py-10 sm:px-12">
            <a href="{{ route('home') }}" class="lg:hidden mb-6 inline-flex items-center justify-center gap-2 text-2xl font-extrabold text-lime-600 tracking-tight">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-lime-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                <span>{{ config('app.name') }}</span>
            </a>

            <div class="w-full max-w-md mx-auto bg-white dark:bg-card rounded-3xl shadow-xl border border-slate-100 dark:border-white/10 p-8">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-center text-sm text-slate-500 dark:text-neutral-400 hover:text-lime-600 transition inline-flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke beranda
            </a>
        </div>
    </div>
</body>
</html>