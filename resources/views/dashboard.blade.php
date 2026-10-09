@php
    $rp = fn ($n) => $n > 0 ? 'Rp '.number_format($n, 0, ',', '.') : 'Gratis';
    $badge = fn ($s) => $s === 'lunas' ? 'bg-emerald-100 text-emerald-700' : ($s === 'batal' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
@endphp

<x-public-layout>
@if ($user->isOrganizer())
    {{-- ===== ORGANIZER ===== --}}
    <section class="rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white p-8 shadow-xl">
        <span class="px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">MODE ORGANIZER AKTIF</span>
        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold">Selamat datang kembali, {{ $user->name }}! 👋</h1>
        <p class="mt-2 text-white/80 max-w-xl">Pantau penjualan tiket, kelola kuota, dan siapkan check-in peserta.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('organizer.events.create') }}" class="rounded-xl bg-white px-5 py-2.5 font-semibold text-indigo-700 hover:scale-105 transition">+ Buat Event Baru</a>
            <a href="{{ route('organizer.events.index') }}" class="rounded-xl bg-white/15 px-5 py-2.5 font-semibold hover:bg-white/25 transition">Kelola Event Saya</a>
        </div>
    </section>

    @php $persen = $stats['kuota'] > 0 ? min(100, round($stats['terjual'] / $stats['kuota'] * 100)) : 0; @endphp
    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Event Aktif</p>
            <p class="mt-1 text-3xl font-extrabold">{{ $stats['event_aktif'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Tiket Terjual</p>
            <p class="mt-1 text-3xl font-extrabold">{{ $stats['terjual'] }} <span class="text-sm font-medium text-slate-400">/ {{ $stats['kuota'] }}</span></p>
            <div class="mt-3 h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-indigo-500 rounded-full transition-all duration-700" style="width: {{ $persen }}%"></div></div>
            <p class="mt-1 text-xs text-slate-400">{{ $persen }}% kuota terisi</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Total Pendapatan</p>
            <p class="mt-1 text-3xl font-extrabold">{{ 'Rp '.number_format($stats['pendapatan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <p class="text-sm text-slate-500">Menunggu Verifikasi</p>
            <p class="mt-1 text-3xl font-extrabold text-amber-600">{{ $stats['menunggu'] }}</p>
        </div>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">Event Saya</h2>
                <a href="{{ route('organizer.events.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">Lihat semua &rarr;</a>
            </div>
            @forelse ($myEvents as $e)
                <a href="{{ route('organizer.events.ticket-types.index', $e) }}" class="flex items-center justify-between py-3 border-t border-slate-100 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                    <div>
                        <p class="font-semibold">{{ $e->judul }}</p>
                        <p class="text-xs text-slate-500">{{ $e->tanggal_mulai->format('d M Y, H:i') }} &bull; {{ ucfirst($e->tipe) }}</p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $e->status === 'terbit' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($e->status) }}</span>
                </a>
            @empty
                <p class="text-sm text-slate-500">Belum ada event. Buat yang pertama!</p>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 class="font-bold text-lg mb-4">Transaksi Terbaru</h2>
            @forelse ($recentOrders as $o)
                <div class="flex items-center justify-between py-3 border-t border-slate-100">
                    <div>
                        <p class="font-semibold">{{ $o->user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $o->ticketType->event->judul }} ({{ $o->jumlah }}x {{ $o->ticketType->nama }}) &bull; {{ $o->created_at->locale('id')->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-sm">{{ $rp($o->total) }}</p>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge($o->status) }}">{{ ucfirst($o->status) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada transaksi.</p>
            @endforelse
        </div>
    </section>

@else
    {{-- ===== PESERTA ===== --}}
    <section class="rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 text-white p-8 shadow-xl">
        <span class="px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">PESERTA</span>
        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold">Halo, {{ $user->name }}! 🎟️</h1>
        <p class="mt-2 text-white/80 max-w-xl">Cari event seru dan tunjukkan QR tiketmu saat check-in di pintu masuk.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('events.index') }}" class="rounded-xl bg-white px-5 py-2.5 font-semibold text-indigo-700 hover:scale-105 transition">Jelajahi Event</a>
            <a href="{{ route('orders.index') }}" class="rounded-xl bg-white/15 px-5 py-2.5 font-semibold hover:bg-white/25 transition">Lihat Semua Tiket</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="bg-white rounded-2xl border border-slate-200 p-5"><p class="text-sm text-slate-500">Tiket Siap Digunakan</p><p class="mt-1 text-3xl font-extrabold">{{ $stats['tiket_aktif'] }}</p></div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5"><p class="text-sm text-slate-500">Event Terdaftar</p><p class="mt-1 text-3xl font-extrabold">{{ $stats['event_terdaftar'] }}</p></div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5"><p class="text-sm text-slate-500">Total Transaksi Lunas</p><p class="mt-1 text-3xl font-extrabold">{{ 'Rp '.number_format($stats['total'], 0, ',', '.') }}</p></div>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="font-bold text-xl mb-3">E-Ticket Aktif Kamu</h2>
            @if ($featured)
                @php $ev = $featured->order->ticketType->event; @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden md:flex">
                    <div class="p-6 flex-1">
                        <span class="text-xs font-semibold text-indigo-600 uppercase">{{ $ev->kategori }}</span>
                        <h3 class="mt-1 text-2xl font-extrabold">{{ $ev->judul }}</h3>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-slate-400 text-xs uppercase">Tanggal</dt><dd class="font-semibold">{{ $ev->tanggal_mulai->format('d M Y, H:i') }}</dd></div>
                            <div><dt class="text-slate-400 text-xs uppercase">Lokasi</dt><dd class="font-semibold">{{ $ev->lokasi }}</dd></div>
                            <div><dt class="text-slate-400 text-xs uppercase">Tipe tiket</dt><dd class="font-semibold">{{ $featured->order->ticketType->nama }}</dd></div>
                            <div><dt class="text-slate-400 text-xs uppercase">Status</dt><dd class="font-semibold text-emerald-600">Siap check-in</dd></div>
                        </dl>
                        <div class="mt-5 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3" x-data="{ copied: false }">
                            <div>
                                <p class="text-xs text-slate-400 uppercase">Kode tiket</p>
                                <p class="font-mono font-bold tracking-widest">{{ $featured->kode }}</p>
                            </div>
                            <button @click="navigator.clipboard.writeText('{{ $featured->kode }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="text-sm font-semibold text-indigo-600 hover:underline" x-text="copied ? 'Tersalin ✓' : 'Salin'"></button>
                        </div>
                    </div>
                    <div class="md:w-56 bg-indigo-50 p-6 flex flex-col items-center justify-center border-t md:border-t-0 md:border-l border-dashed border-indigo-200">
                        <div class="bg-white p-3 rounded-xl shadow">
                            @if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($featured->kode) !!}
                            @else
                                <p class="font-mono text-xs w-[150px] break-all text-center">{{ $featured->kode }}</p>
                            @endif
                        </div>
                        <p class="mt-3 text-xs font-semibold text-slate-500">Scan di pintu masuk</p>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
                    <p class="text-4xl">🎫</p>
                    <p class="mt-2 font-semibold">Belum ada tiket aktif</p>
                    <p class="text-sm text-slate-500">Beli tiket event pertamamu untuk melihat QR di sini.</p>
                    <a href="{{ route('events.index') }}" class="mt-4 inline-block rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700 transition">Jelajahi Event</a>
                </div>
            @endif
        </div>

        <div>
            <h2 class="font-bold text-xl mb-3">Pesanan Terakhir</h2>
            <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2">
                @forelse ($recentOrders as $o)
                    <a href="{{ route('orders.show', $o) }}" class="block rounded-xl p-3 hover:bg-slate-50 transition">
                        <div class="flex justify-between items-start gap-2">
                            <p class="font-semibold text-sm">#{{ $o->id }} {{ $o->ticketType->event->judul }}</p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge($o->status) }}">{{ ucfirst($o->status) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $o->jumlah }}x {{ $o->ticketType->nama }} &bull; {{ $rp($o->total) }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500 p-3">Belum ada pesanan.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
</x-public-layout>