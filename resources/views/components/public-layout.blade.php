@props(['wide' => false, 'navTheme' => 'light'])
@php
    // Definisi $dark di sini, sebelum <body> (body memakai $dark untuk kelas background).
    $dark = $navTheme === 'dark';
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    <script>
        // Tema gelap/terang: localStorage dulu, default ikut OS. Di head agar tanpa flicker.
        try {
            var t = localStorage.getItem('eventku-theme');
            if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ $dark ? 'bg-deep text-paper' : 'bg-cream text-slate-800' }} dark:bg-deep dark:text-paper min-h-screen flex flex-col font-sans antialiased selection:bg-neon selection:text-deep">
    @php
        // Warna teks navbar mengikuti state scroll: di atas hero gelap (belum scroll) pakai teks terang,
        // setelah scroll (glass putih) pakai teks gelap. text-current mewarisi dari header.
        $navTextBase = $dark ? 'text-white' : 'text-slate-900';

        $links = [];
        $isGuest = !auth()->check();
        // Guest: grid 3 kolom setara (1fr auto 1fr) = link tengah presisi viewport/container.
        // Auth: pertahankan flex justify-between + breakpoint md seperti semula.
        $navContainerClass = $isGuest
            ? 'grid grid-cols-[1fr_auto_1fr] items-center'
            : 'flex items-center justify-between';
        $navClass = $isGuest ? 'hidden xl:flex' : 'hidden md:flex';
        $menuBtnClass = $isGuest ? 'xl:hidden' : 'md:hidden';
        $mobilePanelClass = $isGuest ? 'xl:hidden' : 'md:hidden';
        if ($isGuest) {
            $links[] = ['Beranda', route('home'), request()->routeIs('home')];
            $links[] = ['Jelajahi Event', route('events.index'), request()->routeIs('events.*')];
            // Kategori -> anchor nyata #kategori di home (bukan events.index), active selalu false
            // agar tidak conflated dengan Jelajahi Event.
            $links[] = ['Kategori', route('home').'#kategori', false];
            $links[] = ['Tentang', route('home').'#kenapa', false];
        } else {
            $links[] = ['Dashboard', route('dashboard'), request()->routeIs('dashboard')];
            if (auth()->user()->isAdmin() && Route::has('admin.users.index')) {
                $links[] = ['Kelola User', route('admin.users.index'), request()->routeIs('admin.users.*')];
            }
            if (auth()->user()->isOrganizer()) {
                $links[] = ['Event Saya', route('organizer.events.index'), request()->routeIs('organizer.events.*')];
                if (Route::has('organizer.orders.index')) {
                    $links[] = ['Pesanan Masuk', route('organizer.orders.index'), request()->routeIs('organizer.orders.*')];
                }
                if (Route::has('organizer.checkin')) {
                    $links[] = ['Check-in Tiket', route('organizer.checkin'), request()->routeIs('organizer.checkin*')];
                }
            }
            $links[] = ['Tiket Saya', route('orders.index'), request()->routeIs('orders.*')];
        }
        // Kontrak lane backend (belum landing): null-safe.
        $notifCount = $notifCount ?? 0;
        $notifItems = $notifItems ?? [];
        $helpTerbuka = $helpTerbuka ?? 0;
    @endphp

    <header x-data="{ menu: false, user: false, notif: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="scrolled ? 'glass !border-b-0 py-2 text-slate-900 dark:!bg-deep/95 dark:!border-white/10 dark:!text-paper' : 'bg-transparent py-4 {{ $navTextBase }} dark:text-paper'"
            class="fixed top-0 inset-x-0 z-50 transition-all duration-300 border-b border-transparent">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 {{ $navContainerClass }}">
            <a href="{{ route('home') }}" aria-label="EventKu. — Beranda" data-eventku-brand class="text-2xl font-display font-black tracking-tight flex shrink-0 items-center gap-2 text-current justify-self-start">
                <img src="{{ asset('images/brand/eventku-mark-ticket-v2.svg') }}" alt="" aria-hidden="true" width="32" height="32" class="w-8 h-8 shrink-0">
                <span class="whitespace-nowrap">Event<span class="text-neon">Ku.</span></span>
            </a>

            <nav class="{{ $navClass }} items-center gap-8 text-sm font-semibold tracking-wide justify-self-center whitespace-nowrap" aria-label="Navigasi utama">
                @foreach ($links as [$label, $url, $active])
                    <a href="{{ $url }}" class="relative py-2 transition-colors duration-300 {{ $active ? 'text-neon' : 'text-current hover:text-neon group' }}">
                        {{ $label }}
                        @if($active)
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-neon rounded-full"></span>
                        @else
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-neon rounded-full transition-all duration-300 group-hover:w-full"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2 sm:gap-4 justify-self-end">
                {{-- Toggle dark/light: Alpine + localStorage, default ikut OS --}}
                <button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }"
                        @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('eventku-theme', dark ? 'dark' : 'light'); } catch (e) {}"
                        aria-label="Ganti mode gelap terang"
                        class="flex items-center justify-center h-9 w-9 rounded-full border transition-colors border-slate-200/60 hover:border-lime-400 hover:text-lime-600 text-current dark:border-white/20 dark:hover:border-neon dark:hover:text-neon">
                    <svg x-show="!dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0012 21.75a9.753 9.753 0 009.752-6.748z" /></svg>
                    <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                </button>
                @auth
                    {{-- Lonceng notifikasi: semua login, desktop + mobile.
                        Wrapper static di HP agar panel jadi sheet full-width di bawah header (jangkar tombol
                        lonceng bikin panel 288px meluber keluar viewport sempit); sm+ tetap dropdown kanan. --}}
                    <div class="static sm:relative" @click.outside="notif = false">
                        <button @click="notif = !notif; menu = false; user = false" aria-label="Notifikasi" :aria-expanded="notif.toString()" aria-controls="notification-panel"
                                class="relative flex items-center justify-center h-9 w-9 rounded-full border transition-colors {{ $dark ? 'border-white/20 hover:border-neon hover:text-neon' : 'border-slate-200/60 hover:border-lime-400 hover:text-lime-600 text-current' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            @if ($notifCount > 0)
                                <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-neon px-1 text-[11px] font-black text-deep shadow">{{ $notifCount > 9 ? '9+' : $notifCount }}</span>
                            @endif
                        </button>
                        <div id="notification-panel" x-show="notif" x-cloak @keydown.escape.window="notif = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute inset-x-4 top-full z-50 mt-2 w-auto bg-white dark:bg-card border border-slate-200 dark:border-line shadow-lg rounded-2xl py-2 text-sm transform origin-top sm:origin-top-right sm:inset-x-auto sm:left-auto sm:right-0 sm:mt-3 sm:w-72 sm:max-w-[calc(100vw-2rem)]">
                            <p class="px-4 py-2 text-xs font-black uppercase tracking-widest text-slate-500 dark:text-muted">Notifikasi</p>
                            <div class="max-h-[60vh] overflow-y-auto sm:max-h-72">
                                @forelse ($notifItems as $n)
                                    <a href="{{ $n['url'] ?? '#' }}" class="block min-w-0 px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors border-t border-slate-100 dark:border-line">
                                        <p class="text-xs font-bold uppercase tracking-wider {{ ($n['type'] ?? '') === 'bantuan' ? 'text-neon' : 'text-slate-400' }}">{{ $n['type'] ?? 'info' }}</p>
                                        <p class="font-medium text-slate-700 dark:text-paper mt-0.5 break-words">{{ $n['text'] ?? '' }}</p>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-sm font-medium text-slate-500 dark:text-muted text-center break-words">Belum ada notifikasi.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="relative" @click.outside="user = false">
                        <button @click="user = !user; notif = false" class="flex items-center gap-3 rounded-full border pl-1.5 pr-4 py-1.5 transition-all duration-300 hover:shadow-md bg-white/80 hover:bg-white border-slate-200/60 hover:border-neon shadow-sm dark:bg-white/5 dark:hover:bg-white/10 dark:border-white/20 dark:hover:border-neon">
                            <span class="h-8 w-8 rounded-full bg-ink text-white flex items-center justify-center text-sm font-bold shadow-inner dark:bg-neon dark:text-deep">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="hidden sm:block text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500 dark:text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>
                        <div x-show="user" x-cloak 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                             class="absolute right-0 mt-3 w-56 glass rounded-2xl py-2 text-sm transform origin-top-right">
                            <div class="px-4 py-3 border-b border-slate-100/50 mb-1">
                                <p class="text-xs font-semibold text-neon uppercase tracking-wider">{{ auth()->user()->role }}</p>
                                <p class="font-medium text-slate-800 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50/50 font-medium text-slate-700 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                Profil Saya
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100/50 pt-1">
                                @csrf
                                <button class="w-full flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50/50 font-medium transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('events.index') }}" aria-label="Cari event" class="hidden sm:flex items-center justify-center h-9 w-9 rounded-full border transition-colors {{ $dark ? 'border-white/20 hover:border-neon hover:text-neon' : 'border-slate-200/60 hover:border-lime-400 hover:text-lime-600 text-current' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" width="20" height="20" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="10.5" cy="10.5" r="6.5" stroke-width="1.75" /><path stroke-linecap="round" stroke-width="1.75" d="m16 16 4.5 4.5" /></svg>
                    </a>
                    <a href="{{ route('login') }}" class="hidden sm:block text-sm font-semibold text-current hover:text-neon transition-colors">Login</a>
                    <a href="{{ route('register') }}" 
                       :class="scrolled ? 'bg-ink text-white hover:bg-black' : '{{ $dark ? "bg-neon text-deep hover:bg-neon/90" : "bg-ink text-white hover:bg-black" }}'"
                       class="rounded-full px-5 py-2.5 text-sm font-semibold shadow-lg shadow-black/20 transition-all duration-300 transform hover:-translate-y-0.5">Daftar Sekarang</a>
                @endauth

                <button @click="menu = !menu; notif = false; user = false" class="{{ $menuBtnClass }} p-2 rounded-full hover:bg-black/5 transition-colors text-current" aria-label="Menu">
                    <svg x-show="!menu" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-show="menu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div x-show="menu" x-cloak 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="{{ $mobilePanelClass }} absolute top-full left-0 w-full glass border-t border-slate-200/50 shadow-2xl text-slate-900 dark:!bg-deep/95 dark:!border-white/10 dark:!text-paper">
            <div class="px-4 py-4 flex flex-col gap-2">
                @auth
                    <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-500 flex items-center justify-between">
                            Notifikasi
                            @if ($notifCount > 0)
                                <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-neon px-1.5 text-[11px] font-black text-deep">{{ $notifCount > 9 ? '9+' : $notifCount }}</span>
                            @endif
                        </p>
                        <div class="mt-2 flex flex-col gap-1 max-h-56 overflow-y-auto">
                            @forelse ($notifItems as $n)
                                <a href="{{ $n['url'] ?? '#' }}" class="block py-1.5 text-sm font-medium text-slate-700">
                                    <span class="text-xs font-bold uppercase tracking-wider {{ ($n['type'] ?? '') === 'bantuan' ? 'text-neon' : 'text-slate-400' }}">{{ $n['type'] ?? 'info' }}</span>
                                    <span class="block break-words">{{ $n['text'] ?? '' }}</span>
                                </a>
                            @empty
                                <p class="text-sm font-medium text-slate-500 py-2">Belum ada notifikasi.</p>
                            @endforelse
                        </div>
                    </div>
                @endauth
                @foreach ($links as [$label, $url, $active])
                    <a href="{{ $url }}" class="block px-4 py-3 rounded-xl text-sm font-bold {{ $active ? 'bg-neon/10 text-neon' : 'text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
                @endforeach
                @guest
                    <div class="border-t border-slate-100 mt-2 pt-4 flex flex-col gap-3 px-4">
                        <a href="{{ route('login') }}" class="block text-center py-2 text-sm font-semibold text-slate-600">Login</a>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    @if (session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition
             class="fixed top-24 right-4 z-40 max-w-sm bg-emerald-500/90 text-white px-5 py-4 rounded-2xl shadow-2xl flex items-start gap-3 border border-emerald-400">
            <div class="p-1 rounded-full bg-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-sm font-medium pt-0.5">{{ session('success') }}</p>
            <button @click="show = false" class="ml-auto text-white/70 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    @endif

    {{-- pt-20 hanya untuk halaman terang (clearance navbar di atas konten terang).
        Halaman dark punya hero bg-deep dengan pt sendiri (pt-28/32/40), jadi tanpa pt-20
        agar bg-deep mengisi area di belakang navbar transparan = navbar gelap, bukan strip cream. --}}
    <main class="flex-1 {{ $dark ? '' : 'pt-20' }}">
        @if ($wide)
            {{ $slot }}
        @else
            <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">{{ $slot }}</div>
        @endif
    </main>

    <footer class="bg-deep border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
            <div class="grid gap-10 md:grid-cols-3 md:items-start">
                <div class="flex flex-col items-center md:items-start gap-2">
                    <a href="{{ route('home') }}" aria-label="EventKu. — Beranda" data-eventku-brand class="text-xl font-display font-black text-white tracking-tight flex items-center gap-2">
                        <img src="{{ asset('images/brand/eventku-mark-ticket-v2.svg') }}" alt="" aria-hidden="true" width="32" height="32" class="w-8 h-8 shrink-0">
                        <span class="whitespace-nowrap">Event<span class="text-neon">Ku.</span></span>
                    </a>
                    <p class="text-sm text-neutral-500 font-medium">Platform seminar dan webinar.</p>
                </div>
                <nav class="flex flex-wrap justify-center gap-x-6 gap-y-3 text-sm font-semibold" aria-label="Footer">
                    @foreach ($links as [$label, $url])
                        <a href="{{ $url }}" class="text-neutral-400 hover:text-neon transition-colors">{{ $label }}</a>
                    @endforeach
                    @guest
                        <a href="{{ route('register') }}" class="text-neutral-400 hover:text-neon transition-colors">Jadi Organizer</a>
                    @endguest
                </nav>
                <div class="flex justify-center md:justify-end items-start gap-3">
                    <a href="#" aria-label="Instagram" class="flex items-center justify-center h-9 w-9 rounded-full border border-white/10 text-neutral-400 hover:text-neon hover:border-neon/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><circle cx="17.2" cy="6.8" r="1.2" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="#" aria-label="TikTok" class="flex items-center justify-center h-9 w-9 rounded-full border border-white/10 text-neutral-400 hover:text-neon hover:border-neon/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16.5 3c.4 2.3 1.9 3.8 4.2 4v3.1c-1.6 0-3-.5-4.2-1.3v6.4a6.1 6.1 0 11-6.1-6.1c.3 0 .7 0 1 .1v3.2a2.9 2.9 0 102.1 2.8V3h3z"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube" class="flex items-center justify-center h-9 w-9 rounded-full border border-white/10 text-neutral-400 hover:text-neon hover:border-neon/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="2.5" y="6" width="19" height="12" rx="3.5" stroke-width="2"/><path d="M10.5 9.8v4.4L14.5 12l-4-2.2z" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="#" aria-label="X" class="flex items-center justify-center h-9 w-9 rounded-full border border-white/10 text-neutral-400 hover:text-neon hover:border-neon/50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.8 3h3l-6.6 7.6L22 21h-6.1l-4.8-6.3L5.6 21h-3l7.1-8.1L2 3h6.3l4.3 5.7L17.8 3zm-1.1 16.1h1.7L7.4 4.8H5.6l11.1 14.3z"/></svg>
                    </a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-white/5 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs font-medium text-neutral-600">
                <p>Laravel &amp; Tailwind</p>
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}.</p>
            </div>
        </div>
    </footer>

    <x-helpdesk-widget />
</body>
</html>
