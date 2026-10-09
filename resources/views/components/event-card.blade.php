@props(['event'])
@php
    $min = $event->ticketTypes->min('harga');
    $harga = $event->ticketTypes->isEmpty() ? 'Segera hadir' : ($min > 0 ? 'Mulai Rp '.number_format($min, 0, ',', '.') : 'Gratis');
    $isOnline = strtolower($event->tipe) === 'online';
@endphp

<a href="{{ route('events.show', $event) }}"
   class="group relative h-full flex flex-col bg-white rounded-3xl overflow-hidden shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07),0_10px_20px_-2px_rgba(0,0,0,0.04)] hover:shadow-2xl hover:-translate-y-2 transition-all duration-500">
    
    <div class="relative h-56 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent z-10"></div>
        @if ($event->poster)
            <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->judul }}" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
        @else
            <div class="h-full w-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-6xl group-hover:scale-110 transition-transform duration-700 ease-in-out">🎟️</div>
        @endif
        
        <div class="absolute top-4 left-4 z-20 flex gap-2">
            <span class="glass px-3 py-1.5 rounded-full text-xs font-bold tracking-wider uppercase {{ $isOnline ? 'text-emerald-500' : 'text-amber-500' }}">
                {{ $isOnline ? '🌐 Online' : '🏢 Offline' }}
            </span>
        </div>
        
        <div class="absolute bottom-4 left-4 right-4 z-20">
            <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-widest uppercase text-white bg-indigo-600/90 backdrop-blur-sm">
                {{ $event->kategori }}
            </span>
        </div>
    </div>
    
    <div class="p-6 flex flex-col flex-1 bg-white relative z-20">
        <h3 class="font-display font-bold text-xl leading-snug line-clamp-2 text-slate-800 group-hover:text-indigo-600 transition-colors duration-300">
            {{ $event->judul }}
        </h3>
        
        <div class="mt-4 space-y-2">
            <p class="flex items-center text-sm font-medium text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                {{ $event->tanggal_mulai->format('d M Y, H:i') }}
            </p>
            <p class="flex items-center text-sm font-medium text-slate-500 truncate">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                {{ $event->lokasi }}
            </p>
        </div>
        
        <div class="mt-auto pt-6 flex items-center justify-between border-t border-slate-100">
            <div>
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Mulai dari</p>
                <span class="font-display font-bold text-lg text-slate-800">{{ $harga }}</span>
            </div>
            <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform -rotate-45 group-hover:rotate-0 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </div>
        </div>
    </div>
</a>