@props(['wide' => false])
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans antialiased selection:bg-indigo-500 selection:text-white">
    @php
        $links = [
            ['Beranda', route('home'), request()->routeIs('home')],
            ['Jelajahi Event', route('events.index'), request()->routeIs('events.*')],
        ];
        if (auth()->check()) {
            $links[] = ['Dashboard', route('dashboard'), request()->routeIs('dashboard')];
            if (auth()->user()->isOrganizer()) {
                $links[] = ['Event Saya', route('organizer.events.index'), request()->routeIs('organizer.*')];
            }
            $links[] = ['Tiket Saya', route('orders.index'), request()->routeIs('orders.*')];
        }
    @endphp

    <header x-data="{ menu: false, user: false, scrolled: false }" 
            @scroll.window="scrolled = (window.pageYOffset > 20)"
            :class="{ 'glass !border-b-0 py-2': scrolled, 'bg-transparent py-4': !scrolled }"
            class="fixed top-0 inset-x-0 z-50 transition-all duration-300 border-b border-transparent">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-2xl font-display font-black bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-violet-600 tracking-tight flex items-center gap-2">
                <span class="text-3xl filter drop-shadow-md">🎟️</span> {{ config('app.name') }}
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm font-semibold tracking-wide">
                @foreach ($links as [$label, $url, $active])
                    <a href="{{ $url }}" class="relative py-2 transition-colors duration-300 {{ $active ? 'text-indigo-600' : 'text-slate-600 hover:text-indigo-600 group' }}">
                        {{ $label }}
                        @if($active)
                            <span class="absolute bottom-0 left-0 w-full h-0.5 bg-indigo-600 rounded-full"></span>
                        @else
                            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-indigo-600 rounded-full transition-all duration-300 group-hover:w-full"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-4">
                @auth
                    <div class="relative" @click.outside="user = false">
                        <button @click="user = !user" class="flex items-center gap-3 rounded-full bg-white/80 hover:bg-white border border-slate-200/60 shadow-sm pl-1.5 pr-4 py-1.5 transition-all duration-300 hover:shadow-md hover:border-indigo-200">
                            <span class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-sm font-bold shadow-inner">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="hidden sm:block text-sm font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
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
                                <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider">{{ auth()->user()->role }}</p>
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
                    <a href="{{ route('login') }}" class="hidden sm:block text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 hover:bg-indigo-600 hover:shadow-indigo-500/30 transition-all duration-300 transform hover:-translate-y-0.5">Daftar Sekarang</a>
                @endauth

                <button @click="menu = !menu" class="md:hidden p-2 rounded-full hover:bg-slate-100 transition-colors" aria-label="Menu">
                    <svg x-show="!menu" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg x-show="menu" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
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
             class="md:hidden absolute top-full left-0 w-full glass border-t border-slate-200/50 shadow-2xl">
            <div class="px-4 py-4 flex flex-col gap-2">
                @foreach ($links as [$label, $url, $active])
                    <a href="{{ $url }}" class="block px-4 py-3 rounded-xl text-sm font-bold {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
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
             class="fixed top-24 right-4 z-40 max-w-sm glass bg-emerald-500/90 text-white px-5 py-4 rounded-2xl shadow-2xl flex items-start gap-3 border-emerald-400">
            <div class="p-1 rounded-full bg-white/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
            </div>
            <p class="text-sm font-medium pt-0.5">{{ session('success') }}</p>
            <button @click="show = false" class="ml-auto text-white/70 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </div>
    @endif

    <main class="flex-1 pt-20">
        @if ($wide)
            {{ $slot }}
        @else
            <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8 animate-fade-in-up">{{ $slot }}</div>
        @endif
    </main>

    <footer class="bg-white border-t border-slate-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-col items-center md:items-start gap-2">
                    <a href="{{ route('home') }}" class="text-xl font-display font-black text-slate-800 tracking-tight flex items-center gap-2">
                        🎟️ {{ config('app.name') }}
                    </a>
                    <p class="text-sm text-slate-500 font-medium">Platform seminar dan webinar modern Anda.</p>
                </div>
                <div class="flex gap-6 text-sm font-semibold text-slate-600">
                    <a href="#" class="hover:text-indigo-600 transition-colors">Tentang Kami</a>
                    <a href="#" class="hover:text-indigo-600 transition-colors">Bantuan</a>
                    <a href="#" class="hover:text-indigo-600 transition-colors">Privasi</a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs font-medium text-slate-400">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <p>Dibuat dengan <span class="text-red-500">♥</span> menggunakan Laravel & Tailwind</p>
            </div>
        </div>
    </footer>
</body>
</html>