<x-public-layout :wide="true">
    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden bg-slate-900 pt-32 pb-20 lg:pt-48 lg:pb-32">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-indigo-600/30 blur-[120px] animate-blob"></div>
            <div class="absolute top-[20%] -right-[10%] w-[60%] h-[60%] rounded-full bg-purple-600/20 blur-[120px] animate-blob" style="animation-delay: 2s;"></div>
            <div class="absolute -bottom-[20%] left-[20%] w-[60%] h-[60%] rounded-full bg-pink-600/20 blur-[120px] animate-blob" style="animation-delay: 4s;"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 lg:px-8 text-center z-10">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-dark text-xs font-bold tracking-widest text-indigo-300 uppercase mb-8 animate-fade-in-up">
                <span class="relative flex h-2 w-2">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                </span>
                Platform Seminar & Webinar
            </div>
            
            <h1 class="text-5xl md:text-7xl font-display font-black text-white leading-[1.1] tracking-tight mb-6 animate-fade-in-up" style="animation-delay: 0.1s;">
                Temukan Event <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400">Terbaik Untukmu</span>
            </h1>
            
            <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-12 font-medium animate-fade-in-up" style="animation-delay: 0.2s;">
                Platform modern untuk menemukan, mendaftar, dan mengelola tiket event Anda dengan mudah. Dilengkapi dengan e-ticket dan QR check-in instan.
            </p>

            <form action="{{ route('events.index') }}" method="GET"
                  class="max-w-2xl mx-auto flex flex-col sm:flex-row gap-3 glass p-3 rounded-3xl animate-fade-in-up shadow-2xl shadow-indigo-900/50" style="animation-delay: 0.3s;">
                <div class="relative flex-1 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" name="q" placeholder="Cari judul event, kategori, atau pembicara..."
                           class="w-full bg-transparent border-0 pl-12 pr-4 py-3 text-slate-800 placeholder-slate-500 focus:ring-0 font-medium text-lg">
                </div>
                <button class="bg-slate-900 hover:bg-indigo-600 text-white rounded-2xl px-8 py-4 font-bold transition-all duration-300 shadow-lg hover:shadow-indigo-500/50 transform hover:-translate-y-0.5">
                    Eksplorasi
                </button>
            </form>

            <!-- Stats -->
            <div class="mt-20 grid grid-cols-2 md:grid-cols-3 gap-8 max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 0.4s;">
                <div class="glass-dark rounded-3xl p-6 transform hover:-translate-y-1 transition-transform duration-300">
                    <p class="text-4xl font-display font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-400">{{ $stats['event'] }}+</p>
                    <p class="text-sm font-semibold text-slate-400 mt-2 uppercase tracking-wider">Event Aktif</p>
                </div>
                <div class="glass-dark rounded-3xl p-6 transform hover:-translate-y-1 transition-transform duration-300">
                    <p class="text-4xl font-display font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400">{{ $stats['organizer'] }}+</p>
                    <p class="text-sm font-semibold text-slate-400 mt-2 uppercase tracking-wider">Penyelenggara</p>
                </div>
                <div class="glass-dark rounded-3xl p-6 transform hover:-translate-y-1 transition-transform duration-300 col-span-2 md:col-span-1">
                    <p class="text-4xl font-display font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-orange-400">{{ $stats['tiket'] }}+</p>
                    <p class="text-sm font-semibold text-slate-400 mt-2 uppercase tracking-wider">Tiket Terjual</p>
                </div>
            </div>
        </div>
        
        <!-- Bottom Curve -->
        <div class="absolute bottom-0 inset-x-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto text-slate-50 drop-shadow-sm">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="currentColor"/>
            </svg>
        </div>
    </section>

    {{-- FITUR UNGGULAN --}}
    <section class="py-24 bg-slate-50 relative">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-sm font-bold text-indigo-600 uppercase tracking-widest mb-3">Keunggulan</h2>
                <h3 class="text-3xl md:text-5xl font-display font-black text-slate-900 leading-tight">
                    Pengalaman Terbaik untuk <br/> Peserta & Penyelenggara
                </h3>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ([
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />', 'color' => 'indigo', 'title' => 'E-Ticket Instan', 'desc' => 'Dapatkan tiket digital Anda seketika setelah pembayaran terverifikasi.'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />', 'color' => 'purple', 'title' => 'Scan QR Code', 'desc' => 'Check-in secepat kilat dengan fitur pemindai QR Code di lokasi.'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />', 'color' => 'pink', 'title' => 'Dashboard Analitik', 'desc' => 'Pantau penjualan tiket dan data peserta secara real-time.'],
                    ['icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />', 'color' => 'emerald', 'title' => 'Transaksi Aman', 'desc' => 'Sistem pembayaran aman dan alokasi kuota yang terjamin.'],
                ] as $feature)
                    <div class="bg-white rounded-[2rem] p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] border border-slate-100 hover:-translate-y-2 transition-all duration-300 group">
                        <div class="w-16 h-16 rounded-2xl bg-{{ $feature['color'] }}-50 flex items-center justify-center text-{{ $feature['color'] }}-500 mb-6 group-hover:scale-110 group-hover:rotate-3 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $feature['icon'] !!}</svg>
                        </div>
                        <h4 class="text-xl font-bold text-slate-800 mb-3">{{ $feature['title'] }}</h4>
                        <p class="text-slate-500 leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- EVENT MENDATANG --}}
    <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-4 lg:px-8" x-data="{ filter: 'all' }">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
                <div class="max-w-2xl">
                    <h2 class="text-3xl md:text-4xl font-display font-black text-slate-900 mb-4">Sedang Tren & Mendatang</h2>
                    <p class="text-lg text-slate-500">Jangan lewatkan kesempatan untuk belajar dari para ahli di bidangnya.</p>
                </div>
                
                <div class="flex bg-slate-100 p-1.5 rounded-full self-start md:self-auto">
                    @foreach (['all' => 'Semua', 'online' => 'Online', 'offline' => 'Offline'] as $key => $label)
                        <button @click="filter = '{{ $key }}'"
                                :class="filter === '{{ $key }}' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="px-6 py-2.5 rounded-full text-sm font-bold transition-all duration-300">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($events as $event)
                    <div x-show="filter === 'all' || filter === '{{ $event->tipe }}'" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="h-full">
                        <x-event-card :event="$event" />
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center bg-slate-50 rounded-3xl border border-dashed border-slate-300">
                        <div class="text-6xl mb-4">🕵️‍♂️</div>
                        <h3 class="text-xl font-bold text-slate-700 mb-2">Belum ada event saat ini</h3>
                        <p class="text-slate-500">Event menarik sedang dalam persiapan. Pantau terus!</p>
                    </div>
                @endforelse
            </div>

            <div class="text-center mt-16">
                <a href="{{ route('events.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-slate-200 px-8 py-4 font-bold text-slate-600 hover:border-indigo-600 hover:bg-indigo-600 hover:text-white transition-all duration-300 group">
                    Jelajahi Semua Event
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- CARA KERJA --}}
    <section class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAzNHYtNGgtMnY0aC00djJoNHY0aDJ2LTRoNHYtMmgtNHptMC0zMFYwaC0ydjRoLTR2Mmg0djRoMnYtNGg0VjRoLTR6TTI4IDQ2di00aC0ydjRoLTR2Mmg0djRoMnYtNGg0di0yaC00ek0xNCA2djRoMnY0aDJWNmgyVjRoLTJWMEgxNHY0aC0ydjJoMnoiIGZpbGw9IiM0ZjQ2ZTUiIGZpbGwtb3BhY2l0eT0iMC4xIi8+PC9nPjwvc3ZnPg==')]"></div>
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <h2 class="text-3xl md:text-4xl font-display font-black text-white text-center mb-16">Perjalanan Anda Dimulai Di Sini</h2>
            
            <div class="grid md:grid-cols-3 gap-12 relative">
                <!-- Connecting Line -->
                <div class="hidden md:block absolute top-1/2 left-0 w-full h-0.5 bg-gradient-to-r from-indigo-500/0 via-indigo-500/50 to-indigo-500/0 -translate-y-1/2"></div>
                
                @foreach ([
                    ['Pilih Event', 'Temukan ribuan seminar & webinar yang sesuai minat Anda.', '🔍'],
                    ['Amankan Tiket', 'Selesaikan pembayaran aman & tiket instan terbit.', '💳'],
                    ['Hadir & Nikmati', 'Tunjukkan QR Code di pintu masuk dan siap belajar!', '🎉'],
                ] as $index => [$title, $desc, $emoji])
                    <div class="relative bg-slate-800/80 backdrop-blur-sm border border-slate-700/50 rounded-[2rem] p-8 text-center hover:bg-slate-800 transition-colors duration-300">
                        <div class="absolute -top-6 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center font-bold text-white shadow-[0_0_20px_rgba(99,102,241,0.5)] border-4 border-slate-900">
                            {{ $index + 1 }}
                        </div>
                        <div class="text-5xl mb-6 mt-4">{{ $emoji }}</div>
                        <h3 class="text-xl font-bold text-white mb-3">{{ $title }}</h3>
                        <p class="text-slate-400">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    @guest
        <section class="py-24 bg-slate-50">
            <div class="max-w-5xl mx-auto px-4 lg:px-8">
                <div class="relative rounded-[3rem] overflow-hidden bg-indigo-600 shadow-2xl shadow-indigo-600/30">
                    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-80 h-80 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 rounded-full bg-purple-500/20 blur-3xl"></div>
                    
                    <div class="relative p-12 md:p-20 text-center">
                        <h2 class="text-4xl md:text-5xl font-display font-black text-white mb-6 leading-tight">Mulai Perjalanan<br/>Intelektual Anda Hari Ini</h2>
                        <p class="text-lg text-indigo-100 max-w-2xl mx-auto mb-10">Bergabunglah dengan ribuan pembelajar lainnya. Buat akun gratis sekarang dan dapatkan akses ke event-event eksklusif.</p>
                        
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-indigo-600 font-bold hover:scale-105 hover:shadow-xl transition-all duration-300">
                            Daftar Gratis Sekarang
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    @endguest
</x-public-layout>