@props(['event'])
@php
    $min = $event->ticketTypes->min('harga');
    $harga = $event->ticketTypes->isEmpty() ? 'Segera hadir' : ($min > 0 ? 'Rp '.number_format($min, 0, ',', '.') : 'Gratis');
    $isOnline = strtolower($event->tipe) === 'online';
    // Stok nyata: sisa() = kuota - terjual() (relasi TicketType, order non-batal).
    $sisa = $event->ticketTypes->sum(fn ($t) => $t->sisa());
    $tersedia = $sisa > 0;
    $deskripsi = $event->deskripsi ? \Illuminate\Support\Str::limit($event->deskripsi, 120) : '';
@endphp

<a href="{{ route('events.show', $event) }}"
   class="group relative h-full flex flex-col bg-white dark:bg-card rounded-2xl overflow-hidden border border-slate-200 dark:border-line hover:border-neon/50 hover:-translate-y-1 transition-all duration-300">

    {{-- Poster 16:9 --}}
    <div class="relative aspect-[16/9] overflow-hidden">
        @if ($event->poster)
            <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->judul }}"
                 class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500 ease-in-out">
        @else
            <div class="h-full w-full bg-slate-100 dark:bg-surface flex items-center justify-center text-neon/50 group-hover:scale-105 transition-transform duration-500 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
            </div>
        @endif

        {{-- Badge tipe (online/offline) --}}
        <span class="absolute top-3 left-3 inline-flex items-center gap-1.5 bg-deep/85 backdrop-blur-sm px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide uppercase {{ $isOnline ? 'text-emerald-400' : 'text-amber-400' }}">
            @if ($isOnline)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                <span>Online</span>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg>
                <span>Offline</span>
            @endif
        </span>

        {{-- Badge stok nyata (Tersedia / Habis) --}}
        <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wide {{ $tersedia ? 'bg-neon/15 text-neon border border-neon/30' : 'bg-red-500/15 text-red-400 border border-red-500/30' }}">
            {{ $tersedia ? 'Tersedia' : 'Habis' }}
        </span>
    </div>

    {{-- Body --}}
    <div class="p-5 flex flex-col flex-1">
        <span class="text-[11px] font-bold uppercase tracking-widest text-neon mb-2">{{ $event->kategori }}</span>

        <h3 class="font-display font-bold text-lg leading-snug line-clamp-2 text-ink dark:text-paper group-hover:text-neon group-focus-within:text-neon transition-colors duration-300">
            {{ $event->judul }}
        </h3>

        @if ($deskripsi)
            <p class="mt-2 text-sm text-slate-600 dark:text-muted leading-relaxed line-clamp-2">{{ $deskripsi }}</p>
        @endif

        <div class="mt-4 space-y-2">
            <p class="flex items-center text-[13px] font-medium text-slate-600 dark:text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-neon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span class="truncate">{{ $event->tanggal_mulai->format('d M Y, H:i') }}</span>
            </p>
            <p class="flex items-center text-[13px] font-medium text-slate-600 dark:text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-neon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="truncate">{{ $event->lokasi }}</span>
            </p>
            <p class="flex items-center text-[13px] font-medium text-slate-600 dark:text-muted">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-neon shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <span class="truncate">{{ $event->organizer?->name ?? 'Penyelenggara' }}</span>
            </p>
        </div>

        {{-- Footer: harga + tombol full-width --}}
        <div class="mt-auto pt-5">
            <div class="flex items-baseline justify-between gap-2 mb-3">
                <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-muted tracking-wider">Mulai dari</span>
                <span class="font-display font-bold text-base text-ink dark:text-paper">{{ $harga }}</span>
            </div>
            <span class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-surface border border-slate-200 dark:border-line text-ink dark:text-paper font-bold text-sm py-3 group-hover:bg-neon group-hover:text-deep group-hover:border-transparent group-focus-within:bg-neon group-focus-within:text-deep group-focus-within:border-transparent transition-colors duration-300">
                Daftar Sekarang
            </span>
        </div>
    </div>
</a>
