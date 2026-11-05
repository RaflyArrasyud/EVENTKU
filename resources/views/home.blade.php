<x-public-layout :wide="true" nav-theme="light">
    @php
        // Ranking client-side untuk tab Terbaru / Populer / Terpopuler
        // Terbaru: urutan tanggal_mulai menanjak (sudah dari controller).
        // Populer: tiket terjual (relasi nyata: ticketTypes->terjual()).
        // Terpopuler: sisa tiket (relasi nyata: ticketTypes->sisa()).
        $terbaruRank = $events->mapWithKeys(fn ($e, $i) => [$e->id => $i]);
        $byPopuler = $events->sortByDesc(fn ($e) => $e->ticketTypes->sum(fn ($t) => $t->terjual()))->values();
        $populerRank = $byPopuler->mapWithKeys(fn ($e, $i) => [$e->id => $i]);
        $byTerpopuler = $events->sortByDesc(fn ($e) => $e->ticketTypes->sum(fn ($t) => $t->sisa()))->values();
        $terpopulerRank = $byTerpopuler->mapWithKeys(fn ($e, $i) => [$e->id => $i]);

        // Floating Live card pakai data nyata event pertama, tanpa karang tanggal/peserta.
        $live = $events->first();
        $livePeserta = $live ? $live->ticketTypes->sum(fn ($t) => $t->terjual()) : 0;

        // Section F siap struktur, hidden bila kosong (Opsi A, tanpa fabricate).
        $speakers = $speakers ?? [];
    @endphp

    {{-- (B) HERO TERANG — kiri teks + kanan mark brand besar --}}
    <section class="relative overflow-hidden bg-cream dark:bg-deep pt-8 sm:pt-10 lg:pt-12 pb-20 lg:pb-32">
        <div class="max-w-shell mx-auto px-4 sm:px-5 lg:px-10">
            <div class="grid lg:grid-cols-[1.2fr_1fr] gap-12 lg:gap-14 items-center">

                {{-- KIRI: badge + H1 + deskripsi + search + 3 stat nyata --}}
                <div class="animate-fade-in-up">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-ink dark:bg-card border border-ink dark:border-line text-[11px] sm:text-xs font-bold tracking-wide text-neon uppercase mb-7">
                        <span>Seminar</span>
                        <span class="text-neon/40">&middot;</span>
                        <span>Webinar</span>
                        <span class="text-neon/40">&middot;</span>
                        <span>Workshop</span>
                        <span class="text-neon/40">&middot;</span>
                        <span>Event Lainnya</span>
                    </div>

                    <h1 style="font-size: clamp(2.25rem, 5vw, 3.5rem)" class="font-display font-black text-ink dark:text-paper leading-[1.04] tracking-tight mb-6">
                        Temukan Event
                        <span class="block text-neon">Terbaik Untukmu</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-muted max-w-xl mb-8 font-medium leading-relaxed">
                        Daftar, bayar, dan dapatkan e-ticket instan dalam hitungan detik.<br class="hidden sm:block">
                        Check-in cepat dengan QR Code di lokasi penyelenggaraan.<br class="hidden sm:block">
                        Pilih dari seminar, webinar, dan workshop terverifikasi.
                    </p>

                    {{-- SEARCH BAR: action GET ke events.index param q (UTUH) --}}
                    <form action="{{ route('events.index') }}" method="GET"
                          class="max-w-xl flex flex-col sm:flex-row gap-3 bg-white dark:bg-card p-2 rounded-full border border-slate-300 dark:border-line shadow-sm">
                        <div class="relative flex-1 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-4 h-5 w-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <input type="text" name="q" placeholder="Cari judul atau kategori event..."
                                   class="w-full bg-transparent border-0 pl-12 pr-4 py-3 text-slate-900 dark:text-paper placeholder-slate-400 dark:placeholder-muted focus:ring-0 font-medium text-base">
                        </div>
                        <button class="bg-neon hover:bg-neon/90 text-deep rounded-full px-7 py-3 font-bold transition-colors duration-200 whitespace-nowrap">
                            Cari Event
                        </button>
                    </form>

                    {{-- STATS BERIKON — angka dari $stats asli --}}
                    <div data-home-stats class="mt-10 grid grid-cols-3 gap-3 sm:gap-4 max-w-xl">
                        <div class="min-w-0 flex flex-col xl:flex-row items-center gap-2 text-center xl:text-left">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div class="min-w-0 w-full xl:w-auto">
                                <p class="text-2xl sm:text-3xl font-display font-black text-neon leading-none">{{ $stats['event'] }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-muted mt-1.5 uppercase tracking-normal whitespace-nowrap">Event Aktif</p>
                            </div>
                        </div>
                        <div class="min-w-0 flex flex-col xl:flex-row items-center gap-2 text-center xl:text-left">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            </div>
                            <div class="min-w-0 w-full xl:w-auto">
                                <p class="text-2xl sm:text-3xl font-display font-black text-neon leading-none">{{ $stats['organizer'] }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-muted mt-1.5 uppercase tracking-normal whitespace-nowrap">Penyelenggara</p>
                            </div>
                        </div>
                        <div class="min-w-0 flex flex-col xl:flex-row items-center gap-2 text-center xl:text-left">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.098 9.9c-.783-.57-.38-1.81.588-1.81h4.915a1 1 0 00.95-.69l1.519-4.674z" /></svg>
                            </div>
                            <div class="min-w-0 w-full xl:w-auto">
                                <p class="text-2xl sm:text-3xl font-display font-black text-neon leading-none">{{ $stats['tiket'] }}</p>
                                <p class="text-[10px] sm:text-[11px] font-semibold text-slate-500 dark:text-muted mt-1.5 uppercase tracking-normal whitespace-nowrap">Tiket Terjual</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KANAN: ikon tiket + wordmark, tanpa dekorasi --}}
                <div class="relative motion-safe:animate-fade-in-up flex items-center justify-center min-w-0 py-6 lg:py-10" style="animation-delay: 0.15s;">
                    <div class="flex items-center justify-center gap-2 sm:gap-3 motion-safe:animate-[heroFloat_7s_ease-in-out_infinite]" role="img" aria-label="EventKu.">
                        <img src="{{ asset('images/brand/eventku-mark-ticket-v2.svg') }}" alt="" aria-hidden="true" width="64" height="64"
                             class="w-20 sm:w-28 xl:w-36 h-auto shrink-0">
                        <span aria-hidden="true" class="font-display font-black text-4xl sm:text-5xl xl:text-6xl leading-none tracking-tight whitespace-nowrap text-ink dark:text-paper">Event<span class="text-neon">Ku.</span></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Widget Live Webinar FIXED pojok kanan-bawah layar (kontrak: $liveOnline Collection, $nextOnline 1 event/null) --}}
    @php
        $liveList = collect($liveOnline ?? []);
        $nextOnline = $nextOnline ?? null;
        $imgSpeaker = file_exists(public_path('images/landing/hero-speaker.jpg'));
    @endphp
    @if ($liveList->count() > 0 || $nextOnline)
    <div class="fixed bottom-4 left-4 sm:bottom-6 sm:left-6 z-40 max-w-[13rem] sm:max-w-[15rem]" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
        @if ($liveList->count() > 0)
        <button type="button" @click="open = ! open" class="w-full text-left flex items-center gap-3 bg-white dark:bg-card border border-slate-200 dark:border-line rounded-2xl p-2.5 pr-4 shadow-2xl cursor-pointer">
            <div class="h-14 w-14 rounded-xl overflow-hidden border border-slate-200 dark:border-line shrink-0">
                @if ($imgSpeaker)
                    <img src="{{ asset('images/landing/hero-speaker.jpg') }}" alt="Pembicara webinar" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-neon flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-red-400">Live</span>
                </div>
                <p class="text-sm font-bold text-ink dark:text-paper truncate">{{ $liveList->first()->judul ?? 'Webinar Berlangsung' }}</p>
                <p class="text-[11px] font-medium text-slate-500 dark:text-muted truncate">{{ $liveList->first()->tanggal_mulai?->format('d M Y, H:i') ?? '' }}</p>
            </div>
        </button>
        <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute bottom-full left-0 mb-2 w-64 max-w-[80vw] rounded-2xl shadow-2xl bg-white dark:bg-card border border-slate-200 dark:border-line overflow-hidden"
                style="display: none;">
            <p class="px-4 pt-3 pb-2 text-[10px] font-bold uppercase tracking-widest text-emerald-400">Sedang Berlangsung</p>
            <div class="max-h-60 overflow-y-auto pb-2">
                @foreach ($liveList->take(5) as $ev)
                <a href="{{ route('events.show', $ev->id) }}" class="block px-4 py-2 hover:bg-neon/10 transition-colors">
                    <p class="text-sm font-bold text-ink dark:text-paper truncate">{{ $ev->judul }}</p>
                    <p class="text-[11px] font-medium text-slate-500 dark:text-muted">{{ $ev->tanggal_mulai?->format('d M Y, H:i') ?? '-' }}</p>
                </a>
                @endforeach
            </div>
        </div>
        @elseif ($nextOnline)
        <button type="button" @click="open = ! open" class="w-full text-left flex items-center gap-3 bg-white dark:bg-card border border-slate-200 dark:border-line rounded-2xl p-2.5 pr-4 shadow-2xl cursor-pointer">
            <div class="h-14 w-14 rounded-xl overflow-hidden border border-slate-200 dark:border-line shrink-0">
                @if ($imgSpeaker)
                    <img src="{{ asset('images/landing/hero-speaker.jpg') }}" alt="Pembicara webinar" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full bg-neon flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                @endif
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="relative flex h-2 w-2">
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-zinc-400"></span>
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-muted">Segera</span>
                </div>
                <p class="text-sm font-bold text-ink dark:text-paper truncate">{{ $nextOnline->judul ?? 'Webinar Mendatang' }}</p>
                <p class="text-[11px] font-medium text-slate-500 dark:text-muted truncate">{{ $nextOnline->tanggal_mulai?->format('d M Y, H:i') ?? '' }}</p>
            </div>
        </button>
        <div x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute bottom-full left-0 mb-2 w-64 max-w-[80vw] rounded-2xl shadow-2xl bg-white dark:bg-card border border-slate-200 dark:border-line overflow-hidden"
                style="display: none;">
            <p class="px-4 pt-3 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500 dark:text-muted">Segera Hadir</p>
            <div class="max-h-60 overflow-y-auto pb-2">
                <a href="{{ route('events.show', $nextOnline->id) }}" class="block px-4 py-2 hover:bg-neon/10 transition-colors">
                    <p class="text-sm font-bold text-ink dark:text-paper truncate">{{ $nextOnline->judul }}</p>
                    <p class="text-[11px] font-medium text-slate-500 dark:text-muted">{{ $nextOnline->tanggal_mulai?->format('d M Y, H:i') ?? '-' }}</p>
                </a>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div x-data="{ cat: 'all', sort: 'terbaru' }">
    {{-- (C) KATEGORI EXPLORER — 9 pill hardcode berikon --}}
    <section id="kategori" class="scroll-mt-24 pt-8 lg:pt-12 pb-8 lg:pb-12 bg-deep dark:bg-cream border-t border-line dark:border-slate-200">
        <div class="max-w-shell mx-auto px-4 sm:px-5 lg:px-10">
            <div class="mb-8">
                <h2 class="text-xs font-bold text-neon uppercase tracking-widest mb-2">Jelajahi Kategori</h2>
                <h3 class="text-2xl sm:text-3xl font-display font-black leading-tight">
                    <span class="text-paper dark:text-ink">Temukan Event Sesuai</span>
                    <span class="block text-neon">Minat dan Tujuanmu</span>
                </h3>
            </div>

            <div class="flex sm:flex-wrap gap-2.5 overflow-x-auto sm:overflow-visible pb-2 sm:pb-0 -mx-4 px-4 sm:mx-0 sm:px-0 scrollbar-hide">
                <button @click="cat = 'all'"
                        :class="cat === 'all' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Semua
                </button>
                <button @click="cat = 'Seminar'"
                        :class="cat === 'Seminar' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 014.5 5.5"/></svg>
                    Seminar
                </button>
                <button @click="cat = 'Webinar'"
                        :class="cat === 'Webinar' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Webinar
                </button>
                <button @click="cat = 'Workshop'"
                        :class="cat === 'Workshop' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.42 15.17L17.25 21A2.582 2.582 0 0021 17.25l-5.83-5.83m-1.42 1.42L13.5 10.5l1.5-1.5L9 3l-1.5 1.5m8.25 8.25L17.25 14M9.75 9.75l1.5-1.5m-4 4l1.5 1.5L3 18.75 4.5 17.25m4-4l1.5 1.5"/></svg>
                    Workshop
                </button>
                <button @click="cat = 'Kompetisi'"
                        :class="cat === 'Kompetisi' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 21h8m-4-4v4m-7-17h14v5a7 7 0 01-14 0V4zM5 6H3a1 1 0 00-1 1c0 2.5 2 4 4 4m14-5h2a1 1 0 011 1c0 2.5-2 4-4 4"/></svg>
                    Kompetisi
                </button>
                <button @click="cat = 'Karier'"
                        :class="cat === 'Karier' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Karier
                </button>
                <button @click="cat = 'Teknologi'"
                        :class="cat === 'Teknologi' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 5h10a2 2 0 012 2v10a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2zm3 5h4v4h-4v-4z"/></svg>
                    Teknologi
                </button>
                <button @click="cat = 'Bisnis'"
                        :class="cat === 'Bisnis' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Bisnis
                </button>
                <button @click="cat = 'Lainnya'"
                        :class="cat === 'Lainnya' ? 'bg-neon text-deep border-neon' : 'bg-card text-muted border-line hover:border-neon/50 dark:bg-white dark:text-slate-600 dark:border-slate-300 dark:hover:border-neon/60'"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold border transition-colors duration-200 whitespace-nowrap shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5.586a1 1 0 01.707.293l7.414 7.414a1 1 0 010 1.414l-5.586 5.586a1 1 0 01-1.414 0L5.707 10.293A1 1 0 015.414 9.586V4a1 1 0 011-1h1z"/></svg>
                    Lainnya
                </button>
            </div>
        </div>
    </section>

    {{-- (D) EVENT PILIHAN — H2 kiri, tab kanan sejajar, grid 4 kolom --}}
    <section class="pt-8 lg:pt-12 pb-16 lg:pb-24 bg-cream dark:bg-deep border-t border-slate-200 dark:border-line">
        <div class="max-w-shell mx-auto px-4 sm:px-5 lg:px-10">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-5 mb-10">
                <div>
                    <h2 class="text-xs font-bold text-neon uppercase tracking-widest mb-2">Event Pilihan</h2>
                    <h3 class="text-2xl sm:text-3xl font-display font-black leading-tight">
                        <span class="text-ink dark:text-paper">Event Terbaru &amp;</span>
                        <span class="text-neon"> Populer</span>
                    </h3>
                </div>
                <div class="flex gap-6 border-b border-slate-300 dark:border-line overflow-x-auto scrollbar-hide shrink-0">
                    <button @click="sort = 'terbaru'"
                            :class="sort === 'terbaru' ? 'text-neon border-neon' : 'text-slate-500 dark:text-muted border-transparent hover:text-ink dark:hover:text-paper'"
                            class="relative pb-3 text-sm font-bold transition-colors duration-200 whitespace-nowrap -mb-px border-b-2">
                        Terbaru
                    </button>
                    <button @click="sort = 'populer'"
                            :class="sort === 'populer' ? 'text-neon border-neon' : 'text-slate-500 dark:text-muted border-transparent hover:text-ink dark:hover:text-paper'"
                            class="relative pb-3 text-sm font-bold transition-colors duration-200 whitespace-nowrap -mb-px border-b-2">
                        Populer
                    </button>
                    <button @click="sort = 'terpopuler'"
                            :class="sort === 'terpopuler' ? 'text-neon border-neon' : 'text-slate-500 dark:text-muted border-transparent hover:text-ink dark:hover:text-paper'"
                            class="relative pb-3 text-sm font-bold transition-colors duration-200 whitespace-nowrap -mb-px border-b-2">
                        Terpopuler
                    </button>
                </div>
            </div>

            {{-- CAROUSEL 1 BARIS: swipe + panah overlay (filter cat & order dipakai ulang) --}}
            <div class="relative group/carousel" x-data="{ canL: false, canR: true }"
                 x-init="$nextTick(() => { const t = $refs.track; canR = t.scrollWidth > t.clientWidth + 8; })"
                 x-effect="cat; sort; $nextTick(() => { const t = $refs.track; if (!t) return; t.scrollTo({ left: 0 }); canL = false; canR = t.scrollWidth > t.clientWidth + 8; })">
                <div x-ref="track" role="region" aria-label="Daftar event"
                     @scroll="canL = $el.scrollLeft > 8; canR = $el.scrollLeft < ($el.scrollWidth - $el.clientWidth - 8)"
                     @resize.window="canL = $el.scrollLeft > 8; canR = $el.scrollLeft < ($el.scrollWidth - $el.clientWidth - 8)"
                     class="flex gap-5 sm:gap-6 overflow-x-auto snap-x snap-mandatory scroll-smooth motion-reduce:scroll-auto pb-2 scrollbar-hide">
                    @forelse ($events as $event)
                        <div x-show="cat === 'all' || cat === '{{ $event->kategori }}'"
                             :style="{ order: sort === 'terbaru' ? {{ $terbaruRank[$event->id] }} : (sort === 'populer' ? {{ $populerRank[$event->id] }} : {{ $terpopulerRank[$event->id] }}) }"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-6"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="snap-start shrink-0 basis-[80%] sm:basis-[calc(50%-0.75rem)] lg:basis-[calc(25%-1.125rem)]">
                            <x-event-card :event="$event" />
                        </div>
                    @empty
                        <div class="w-full shrink-0 py-16 text-center bg-white dark:bg-card rounded-2xl border border-dashed border-slate-300 dark:border-line">
                            <div class="w-12 h-12 mx-auto rounded-full bg-cream dark:bg-surface flex items-center justify-center text-neon mb-3 border border-slate-300 dark:border-line">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <h3 class="text-lg font-bold text-ink dark:text-paper mb-1">Belum ada event saat ini</h3>
                            <p class="text-sm text-slate-600 dark:text-muted">Event menarik sedang dalam persiapan. Pantau terus!</p>
                        </div>
                    @endforelse
                </div>

                <button type="button" aria-label="Geser ke kiri"
                        @click="$refs.track.scrollBy({ left: -$refs.track.clientWidth, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
                        :disabled="!canL" :class="{ '!opacity-30 pointer-events-none': !canL }"
                        class="absolute left-2 top-1/2 -translate-y-1/2 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white dark:bg-card border border-slate-300 dark:border-line text-ink dark:text-paper shadow-xl transition-all duration-200 hover:bg-neon hover:text-deep hover:border-neon motion-reduce:transition-none opacity-100 md:opacity-0 md:group-hover/carousel:opacity-100 focus-visible:!opacity-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button type="button" aria-label="Geser ke kanan"
                        @click="$refs.track.scrollBy({ left: $refs.track.clientWidth, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })"
                        :disabled="!canR" :class="{ '!opacity-30 pointer-events-none': !canR }"
                        class="absolute right-2 top-1/2 -translate-y-1/2 z-10 flex h-11 w-11 items-center justify-center rounded-full bg-white dark:bg-card border border-slate-300 dark:border-line text-ink dark:text-paper shadow-xl transition-all duration-200 hover:bg-neon hover:text-deep hover:border-neon motion-reduce:transition-none opacity-100 md:opacity-0 md:group-hover/carousel:opacity-100 focus-visible:!opacity-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('events.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border-2 border-slate-300 dark:border-line px-8 py-3.5 font-bold text-ink dark:text-paper hover:border-neon hover:bg-neon hover:text-deep transition-all duration-200 group">
                    Jelajahi Semua Event
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </a>
            </div>
        </div>
    </section>
    </div>

    {{-- (E) KENAPA EVENTKU — panel hijau-tinted, intro kiri + 4 benefit list vertikal kanan --}}
    <section id="kenapa" class="py-16 lg:py-24 bg-deep dark:bg-cream border-t border-line dark:border-slate-200">
        <div class="max-w-shell mx-auto px-4 sm:px-5 lg:px-10">
            <div class="bg-[#0a1a10] dark:bg-white border border-[#1a3a20] dark:border-slate-200 dark:shadow-md rounded-2xl p-8 lg:p-12">
                <div class="grid lg:grid-cols-[1fr_1.2fr] gap-10 lg:gap-14">

                    <div>
                        <h2 class="text-xs font-bold text-neon uppercase tracking-widest mb-2">Kenapa EventKu?</h2>
                        <h3 class="text-2xl sm:text-3xl font-display font-black leading-tight mb-4">
                            <span class="text-paper dark:text-ink">Semua yang Kamu Butuhkan</span>
                            <span class="block text-neon">dalam Satu Platform</span>
                        </h3>
                        <p class="text-base text-muted dark:text-slate-600 mb-8 leading-relaxed">EventKu hadir untuk memudahkan kamu menemukan acara yang relevan, mendapatkan pengalaman baru, dan memperluas jaringan.</p>
                        @guest
                            <a href="{{ route('events.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-neon px-6 py-3 font-bold text-deep hover:bg-neon/90 transition-colors">
                                Jelajahi Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        @endguest
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-neon px-6 py-3 font-bold text-deep hover:bg-neon/90 transition-colors">
                                Jelajahi Sekarang
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                        @endauth
                    </div>

                    <div class="divide-y divide-[#1a3a20] dark:divide-slate-200">
                        <div class="flex items-start gap-4 py-5 first:pt-0 last:pb-0">
                            <div class="w-11 h-11 rounded-full border border-neon/40 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-paper dark:text-ink">Proses Mudah dan Cepat</h4>
                                <p class="text-sm text-muted dark:text-slate-600 leading-relaxed mt-1">Daftar event hanya dalam hitungan menit.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 py-5 first:pt-0 last:pb-0">
                            <div class="w-11 h-11 rounded-full border border-neon/40 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-paper dark:text-ink">E-Ticket dan QR Check-in</h4>
                                <p class="text-sm text-muted dark:text-slate-600 leading-relaxed mt-1">Tiket digital langsung ke email dan mudah dipindai saat&nbsp;check&#8209;in.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 py-5 first:pt-0 last:pb-0">
                            <div class="w-11 h-11 rounded-full border border-neon/40 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-paper dark:text-ink">Aman dan Terpercaya</h4>
                                <p class="text-sm text-muted dark:text-slate-600 leading-relaxed mt-1">Pembayaran aman dan data kamu selalu terlindungi.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 py-5 first:pt-0 last:pb-0">
                            <div class="w-11 h-11 rounded-full border border-neon/40 flex items-center justify-center text-neon shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 21l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-paper dark:text-ink">Dukungan 24/7</h4>
                                <p class="text-sm text-muted dark:text-slate-600 leading-relaxed mt-1">Tim kami siap membantu kapan saja.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- (F) PEMBICARA PILIHAN — struktur siap, HIDDEN bila $speakers kosong (Opsi A) --}}
    @if (count($speakers) > 0)
    <section class="py-16 lg:py-24 bg-cream dark:bg-deep border-t border-slate-200 dark:border-line" x-data="{ all: false }">
        <div class="max-w-shell mx-auto px-4 sm:px-5 lg:px-10">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <h2 class="text-xs font-bold text-neon uppercase tracking-widest mb-2">Pembicara Pilihan</h2>
                    <h3 class="text-2xl sm:text-3xl font-display font-black text-ink dark:text-paper leading-tight">Bertemu dengan Pembicara Inspiratif</h3>
                </div>
                @if (count($speakers) > 5)
                <button type="button" @click="all = !all" class="inline-flex items-center gap-1.5 text-sm font-bold text-slate-500 dark:text-muted hover:text-neon transition-colors whitespace-nowrap">
                    <span x-text="all ? 'Tutup' : 'Lihat Semua Pembicara'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform" :class="{ 'rotate-180': all }" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
                @endif
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-5">
                @foreach ($speakers as $i => $speaker)
                <div @if ($i >= 5) x-show="all" x-transition @endif class="bg-white dark:bg-card rounded-2xl p-6 border border-slate-200 dark:border-line dark:shadow-none shadow-sm text-center">
                    <div class="w-20 h-20 mx-auto rounded-full border-2 border-neon/50 bg-cream dark:bg-surface flex items-center justify-center text-neon mb-4 overflow-hidden">
                        @if (!empty($speaker['foto']))
                            <img src="{{ $speaker['foto'] }}" alt="{{ $speaker['nama'] ?? 'Pembicara' }}" class="h-full w-full object-cover">
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        @endif
                    </div>
                    <p class="font-bold text-ink dark:text-paper text-sm">{{ $speaker['nama'] ?? 'Pembicara' }}</p>
                    <p class="text-xs text-slate-500 dark:text-muted mt-1">{{ $speaker['peran'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif
</x-public-layout>
