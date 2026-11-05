@php
    $rp = fn ($n) => $n > 0 ? 'Rp '.number_format($n, 0, ',', '.') : 'Gratis';
    $badge = fn ($s) => $s === 'lunas' ? 'bg-emerald-100 text-emerald-700' : ($s === 'batal' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
    // Kontrak lane controller (organizer+admin saja; peserta tak dapat variabel ini).
    $revStats = $revStats ?? ['pendapatan' => 0, 'terjual' => 0, 'menunggu' => 0, 'event' => 0];
    $revDaily = $revDaily ?? [];
    $revPerEvent = collect($revPerEvent ?? []);
    $revStatus = $revStatus ?? ['lunas' => 0, 'menunggu' => 0, 'batal' => 0];
    $topOrganizers = collect($topOrganizers ?? []);
    $hasDaily = $revDaily && array_sum(array_column($revDaily, 'total')) > 0;
    $hasPerEvent = $revPerEvent->count() > 0;
    $hasStatus = ((int) ($revStatus['lunas'] ?? 0) + (int) ($revStatus['menunggu'] ?? 0) + (int) ($revStatus['batal'] ?? 0)) > 0;
@endphp

<x-public-layout>
@if ($user->isAdmin())
    {{-- ===== ADMIN ===== --}}
    <section class="rounded-3xl bg-ink text-white p-8 shadow-xl">
        <span class="px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">MODE ADMIN AKTIF</span>
        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold">Halo, {{ $user->name }}</h1>
        <p class="mt-2 text-neutral-300 max-w-xl">Kelola pengguna, pantau pendapatan seluruh platform, dan promosikan user jadi organizer.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-white px-5 py-2.5 font-semibold text-lime-600 hover:bg-lime-50 transition">+ Buat akun Organizer</a>
            <a href="{{ route('events.index') }}" class="rounded-xl bg-white/15 px-5 py-2.5 font-semibold hover:bg-white/25 transition">Jelajahi Event</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Pendapatan</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $rp($revStats['pendapatan'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Tiket Terjual</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $revStats['terjual'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Menunggu Verifikasi</p>
            <p class="mt-2 text-3xl font-black text-amber-600">{{ $revStats['menunggu'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Event</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $revStats['event'] ?? 0 }}</p>
        </div>
    </section>

    @include('dashboard._charts')

    <section class="mt-6 bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line flex justify-between items-center">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Top Organizer</h2>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-bold uppercase tracking-wider text-lime-600 hover:text-lime-700">Kelola user</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr><th class="py-4 px-6">Organizer</th><th class="py-4 px-6 text-right">Pendapatan</th></tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($topOrganizers as $org)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">{{ $org->name }}</td>
                            <td class="py-4 px-6 font-black text-slate-900 dark:text-white text-right whitespace-nowrap">{{ $rp((int) ($org->pendapatan ?? 0)) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Belum ada organizer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-6 bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Transaksi Terbaru</h2>
        </div>
        <div class="p-6">
            @forelse ($recentOrders as $o)
                <div class="flex items-center justify-between py-3.5 border-b border-slate-200 dark:border-white/10 last:border-0">
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $o->user->name }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-neutral-400 mt-0.5">{{ $o->ticketType->event->judul }} ({{ $o->jumlah }}x {{ $o->ticketType->nama }}) &bull; {{ $o->created_at->locale('id')->diffForHumans() }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-black text-sm text-slate-900 dark:text-white">{{ $rp($o->total) }}</p>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $badge($o->status) }} border border-black/10">{{ ucfirst($o->status) }}</span>
                    </div>
                </div>
            @empty
                <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-6 text-center">Belum ada transaksi.</p>
            @endforelse
        </div>
    </section>

@elseif ($user->isOrganizer())
    {{-- ===== ORGANIZER ===== --}}
    <section class="rounded-3xl bg-ink text-white p-8 shadow-xl">
        <span class="px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">MODE ORGANIZER AKTIF</span>
        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold">Selamat datang kembali, {{ $user->name }}</h1>
        <p class="mt-2 text-neutral-300 max-w-xl">Pantau penjualan tiket, kelola kuota, dan siapkan check-in peserta.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('organizer.events.create') }}" class="rounded-xl bg-white px-5 py-2.5 font-semibold text-lime-600 hover:bg-lime-50 transition">+ Buat Event Baru</a>
            <a href="{{ route('organizer.events.index') }}" class="rounded-xl bg-white/15 px-5 py-2.5 font-semibold hover:bg-white/25 transition">Kelola Event Saya</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Pendapatan</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $rp($revStats['pendapatan'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Tiket Terjual</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $revStats['terjual'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Menunggu Verifikasi</p>
            <p class="mt-2 text-3xl font-black text-amber-600">{{ $revStats['menunggu'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Jumlah Event</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $revStats['event'] ?? 0 }}</p>
        </div>
    </section>

    @include('dashboard._charts')

    @php $persen = $stats['kuota'] > 0 ? min(100, round($stats['terjual'] / $stats['kuota'] * 100)) : 0; @endphp
    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Event Aktif</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $stats['event_aktif'] }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Tiket Terjual</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $stats['terjual'] }} <span class="text-sm font-semibold text-slate-400 dark:text-neutral-500">/ {{ $stats['kuota'] }}</span></p>
            <div class="mt-3 h-2.5 rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden"><div class="h-full bg-black rounded-full transition-all duration-700" style="width: {{ $persen }}%"></div></div>
            <p class="mt-1.5 text-xs font-semibold text-slate-500 dark:text-neutral-400">{{ $persen }}% kuota terisi</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Pendapatan</p>
            <p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ 'Rp '.number_format($stats['pendapatan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors">
            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Menunggu Verifikasi</p>
            <p class="mt-2 text-3xl font-black text-amber-600">{{ $stats['menunggu'] }}</p>
        </div>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
            <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line flex justify-between items-center">
                <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Event Saya</h2>
                <a href="{{ route('organizer.events.index') }}" class="text-xs font-bold uppercase tracking-wider text-lime-600 hover:text-lime-700 inline-flex items-center gap-1.5">
                    Lihat semua
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
            <div class="p-6">
                @forelse ($myEvents as $e)
                    <a href="{{ route('organizer.events.ticket-types.index', $e) }}" class="flex items-center justify-between py-3.5 border-b border-slate-200 dark:border-white/10 last:border-0 hover:bg-slate-50 dark:hover:bg-white/10 -mx-3 px-3 rounded-xl transition">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $e->judul }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-neutral-400 mt-0.5">{{ $e->tanggal_mulai->format('d M Y, H:i') }} &bull; {{ ucfirst($e->tipe) }}</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full {{ $e->status === 'terbit' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-200 text-slate-700 border border-slate-300' }}">{{ ucfirst($e->status) }}</span>
                    </a>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-6 text-center">Belum ada event. Buat yang pertama!</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
            <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
                <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Transaksi Terbaru</h2>
            </div>
            <div class="p-6">
                @forelse ($recentOrders as $o)
                    <div class="flex items-center justify-between py-3.5 border-b border-slate-200 dark:border-white/10 last:border-0">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $o->user->name }}</p>
                            <p class="text-xs font-medium text-slate-500 dark:text-neutral-400 mt-0.5">{{ $o->ticketType->event->judul }} ({{ $o->jumlah }}x {{ $o->ticketType->nama }}) &bull; {{ $o->created_at->locale('id')->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-sm text-slate-900 dark:text-white">{{ $rp($o->total) }}</p>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $badge($o->status) }} border border-black/10">{{ ucfirst($o->status) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-6 text-center">Belum ada transaksi.</p>
                @endforelse
            </div>
        </div>
    </section>

@else
    {{-- ===== PESERTA ===== --}}
    <section class="rounded-3xl bg-ink text-white p-8 shadow-xl">
        <span class="px-3 py-1 rounded-full bg-white/15 text-xs font-semibold">PESERTA</span>
        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold">Halo, {{ $user->name }}</h1>
        <p class="mt-2 text-neutral-300 max-w-xl">Cari event seru dan tunjukkan QR tiketmu saat check-in di pintu masuk.</p>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('events.index') }}" class="rounded-xl bg-white px-5 py-2.5 font-semibold text-lime-600 hover:bg-lime-50 transition">Jelajahi Event</a>
            <a href="{{ route('orders.index') }}" class="rounded-xl bg-white/15 px-5 py-2.5 font-semibold hover:bg-white/25 transition">Lihat Semua Tiket</a>
        </div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Tiket Siap Digunakan</p><p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $stats['tiket_aktif'] }}</p></div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Event Terdaftar</p><p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ $stats['event_terdaftar'] }}</p></div>
        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-5 hover:border-lime-400 transition-colors"><p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Transaksi Lunas</p><p class="mt-2 text-3xl font-black text-slate-900 dark:text-white">{{ 'Rp '.number_format($stats['total'], 0, ',', '.') }}</p></div>
    </section>

    <section class="mt-6 bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-6">
        <h2 class="font-black text-xl text-slate-900 dark:text-white">Ajukan jadi Organizer</h2>
        @php $or = $organizerRequest ?? null; @endphp
        @if (! $or)
            <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Isi alasan dan kontak, admin akan memverifikasi pengajuanmu.</p>
            <form method="POST" action="{{ route('organizer-request.store') }}" data-organizer-request-form class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <div class="min-w-0">
                    <label for="organizer-alasan" class="block text-sm font-semibold text-slate-700 dark:text-neutral-200 mb-1.5">Alasan <span class="font-normal text-slate-500 dark:text-neutral-400">(opsional)</span></label>
                    <input id="organizer-alasan" type="text" name="alasan" maxlength="1000" value="{{ old('alasan') }}" placeholder="Alasan ingin menjadi organizer" @error('alasan') aria-invalid="true" aria-describedby="organizer-alasan-error" @enderror class="w-full min-w-0 border border-slate-300 dark:border-line bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder:text-slate-500 dark:placeholder:text-neutral-400 focus:border-neon focus:ring-neon dark:focus:border-neon dark:focus:ring-neon rounded-xl px-4 py-2.5 text-sm">
                    @error('alasan') <p id="organizer-alasan-error" class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div class="min-w-0">
                    <label for="organizer-kontak" class="block text-sm font-semibold text-slate-700 dark:text-neutral-200 mb-1.5">Kontak <span class="font-normal text-slate-500 dark:text-neutral-400">(opsional)</span></label>
                    <input id="organizer-kontak" type="text" name="kontak" maxlength="255" value="{{ old('kontak') }}" placeholder="Email atau nomor telepon" @error('kontak') aria-invalid="true" aria-describedby="organizer-kontak-error" @enderror class="w-full min-w-0 border border-slate-300 dark:border-line bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder:text-slate-500 dark:placeholder:text-neutral-400 focus:border-neon focus:ring-neon dark:focus:border-neon dark:focus:ring-neon rounded-xl px-4 py-2.5 text-sm">
                    @error('kontak') <p id="organizer-kontak-error" class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="sm:col-span-2 rounded-xl bg-neon px-5 py-2.5 font-bold text-ink hover:bg-neon/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neon transition-colors">Kirim Pengajuan</button>
            </form>
        @elseif ($or->status === 'diajukan')
            <p class="mt-2 text-sm font-bold text-amber-700 bg-amber-50 border border-amber-300 rounded-xl px-4 py-3">Pengajuanmu menunggu verifikasi admin.</p>
        @elseif ($or->status === 'disetujui')
            <p class="mt-2 text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 rounded-xl px-4 py-3">Pengajuanmu disetujui — selamat menjadi organizer!</p>
        @else
            <p class="mt-2 text-sm font-bold text-red-700 bg-red-50 border border-red-300 rounded-xl px-4 py-3">Pengajuanmu ditolak. Kamu bisa mengajukan ulang di bawah.</p>
            <form method="POST" action="{{ route('organizer-request.store') }}" data-organizer-request-form class="mt-4 grid gap-3 sm:grid-cols-2">
                @csrf
                <div class="min-w-0">
                    <label for="organizer-alasan" class="block text-sm font-semibold text-slate-700 dark:text-neutral-200 mb-1.5">Alasan <span class="font-normal text-slate-500 dark:text-neutral-400">(opsional)</span></label>
                    <input id="organizer-alasan" type="text" name="alasan" maxlength="1000" value="{{ old('alasan') }}" placeholder="Alasan ingin menjadi organizer" @error('alasan') aria-invalid="true" aria-describedby="organizer-alasan-error" @enderror class="w-full min-w-0 border border-slate-300 dark:border-line bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder:text-slate-500 dark:placeholder:text-neutral-400 focus:border-neon focus:ring-neon dark:focus:border-neon dark:focus:ring-neon rounded-xl px-4 py-2.5 text-sm">
                    @error('alasan') <p id="organizer-alasan-error" class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <div class="min-w-0">
                    <label for="organizer-kontak" class="block text-sm font-semibold text-slate-700 dark:text-neutral-200 mb-1.5">Kontak <span class="font-normal text-slate-500 dark:text-neutral-400">(opsional)</span></label>
                    <input id="organizer-kontak" type="text" name="kontak" maxlength="255" value="{{ old('kontak') }}" placeholder="Email atau nomor telepon" @error('kontak') aria-invalid="true" aria-describedby="organizer-kontak-error" @enderror class="w-full min-w-0 border border-slate-300 dark:border-line bg-white dark:bg-deep text-slate-900 dark:text-paper placeholder:text-slate-500 dark:placeholder:text-neutral-400 focus:border-neon focus:ring-neon dark:focus:border-neon dark:focus:ring-neon rounded-xl px-4 py-2.5 text-sm">
                    @error('kontak') <p id="organizer-kontak-error" class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="sm:col-span-2 rounded-xl bg-neon px-5 py-2.5 font-bold text-ink hover:bg-neon/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neon transition-colors">Ajukan Ulang</button>
            </form>
        @endif
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <h2 class="font-black text-xl mb-3 text-slate-900 dark:text-white">E-Ticket Aktif Kamu</h2>
            @if ($featured)
                @php $ev = $featured->order->ticketType->event; @endphp
                <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden md:flex">
                    <div class="p-6 flex-1">
                        <span class="text-xs font-bold text-lime-600 uppercase tracking-wider">{{ $ev->kategori }}</span>
                        <h3 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">{{ $ev->judul }}</h3>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-slate-500 dark:text-neutral-400 text-xs font-semibold uppercase">Tanggal</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $ev->tanggal_mulai->format('d M Y, H:i') }}</dd></div>
                            <div><dt class="text-slate-500 dark:text-neutral-400 text-xs font-semibold uppercase">Lokasi</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $ev->lokasi }}</dd></div>
                            <div><dt class="text-slate-500 dark:text-neutral-400 text-xs font-semibold uppercase">Tipe tiket</dt><dd class="font-bold text-slate-900 dark:text-white">{{ $featured->order->ticketType->nama }}</dd></div>
                            <div><dt class="text-slate-500 dark:text-neutral-400 text-xs font-semibold uppercase">Status</dt><dd class="font-bold text-emerald-600">Siap check-in</dd></div>
                        </dl>
                        <div class="mt-5 flex items-center justify-between rounded-xl bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line px-4 py-3" x-data="{ copied: false }">
                            <div>
                                <p class="text-xs font-bold text-slate-500 dark:text-neutral-400 uppercase">Kode tiket</p>
                                <p class="font-mono font-black tracking-widest text-slate-900 dark:text-white text-base">{{ $featured->kode }}</p>
                            </div>
                            <button @click="navigator.clipboard.writeText('{{ $featured->kode }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                    class="text-sm font-bold text-lime-600 hover:text-lime-700" x-text="copied ? 'Tersalin' : 'Salin'"></button>
                        </div>
                        @if ($featured->status === 'terpakai')
                            <a href="{{ route('tickets.certificate', $featured) }}" class="mt-4 inline-block rounded-xl bg-ink px-5 py-2.5 font-bold text-white hover:bg-black transition shadow">E-Sertifikat</a>
                        @endif
                    </div>
                    <div class="md:w-56 bg-lime-50/80 p-6 flex flex-col items-center justify-center border-t md:border-t-0 md:border-l-2 border-dashed border-lime-300">
                        <div class="bg-white p-3 rounded-xl shadow-md border-2 border-slate-300">
                            @if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($featured->kode) !!}
                            @else
                                <p class="font-mono text-xs w-[150px] break-all text-center">{{ $featured->kode }}</p>
                            @endif
                        </div>
                        <p class="mt-3 text-xs font-bold text-slate-600 dark:text-neutral-400">Scan di pintu masuk</p>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-card rounded-2xl border-2 border-dashed border-slate-300 dark:border-line p-10 text-center shadow-sm">
                    <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line flex items-center justify-center text-slate-400 dark:text-neutral-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                    </div>
                    <p class="mt-3 font-bold text-slate-800 dark:text-neutral-200">Belum ada tiket aktif</p>
                    <p class="text-sm font-medium text-slate-500 dark:text-neutral-400">Beli tiket event pertamamu untuk melihat QR di sini.</p>
                    <a href="{{ route('events.index') }}" class="mt-4 inline-block rounded-xl bg-ink px-5 py-2.5 font-bold text-white hover:bg-black transition shadow">Jelajahi Event</a>
                </div>
            @endif
        </div>

        <div>
            <h2 class="font-black text-xl mb-3 text-slate-900 dark:text-white">Pesanan Terakhir</h2>
            <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-4 space-y-2">
                @forelse ($recentOrders as $o)
                    <a href="{{ route('orders.show', $o) }}" class="block rounded-xl p-3 hover:bg-slate-50 dark:hover:bg-white/10 transition border border-transparent hover:border-slate-200 dark:border-white/10">
                        <div class="flex justify-between items-start gap-2">
                            <p class="font-bold text-sm text-slate-900 dark:text-white">#{{ $o->id }} {{ $o->ticketType->event->judul }}</p>
                            <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $badge($o->status) }} border border-black/10">{{ ucfirst($o->status) }}</span>
                        </div>
                        <p class="text-xs font-medium text-slate-500 dark:text-neutral-400 mt-1">{{ $o->jumlah }}x {{ $o->ticketType->nama }} &bull; {{ $rp($o->total) }}</p>
                    </a>
                @empty
                    <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 p-3 text-center">Belum ada pesanan.</p>
                @endforelse
            </div>
        </div>
    </section>
@endif
</x-public-layout>
