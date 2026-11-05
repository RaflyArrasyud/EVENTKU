<x-public-layout>
    <div class="mb-6">
        <a href="{{ route('organizer.events.index') }}" class="text-xs font-bold uppercase tracking-wider text-lime-600 hover:text-lime-700 inline-flex items-center gap-1.5 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Event Saya
        </a>
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Jenis Tiket: {{ $event->judul }}</h1>
        <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Atur kategori tiket, harga registrasi, dan batas kuota peserta.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-2 border-red-300 text-red-800 rounded-2xl">
            @foreach ($errors->all() as $error) <p class="text-sm font-semibold">{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('organizer.events.ticket-types.store', $event) }}"
          class="bg-white dark:bg-card p-6 rounded-2xl border-2 border-slate-300 dark:border-line shadow-md mb-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        @csrf
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Nama Tiket</label>
            <input type="text" name="nama" placeholder="Reguler / VIP" value="{{ old('nama') }}"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-2.5 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Harga (Rp, 0 = Gratis)</label>
            <input type="number" name="harga" min="0" value="{{ old('harga', 0) }}"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-2.5 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Kuota Tiket</label>
            <input type="number" name="kuota" min="1" value="{{ old('kuota', 50) }}"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-2.5 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm text-sm">
        </div>
        <button class="bg-ink text-white px-5 py-3 rounded-xl font-bold text-sm hover:bg-neutral-800 shadow-md transition w-full">+ Tambah Tiket</button>
    </form>

    <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Nama</th>
                        <th class="py-4 px-6">Harga</th>
                        <th class="py-4 px-6">Kuota</th>
                        <th class="py-4 px-6">Terjual</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($ticketTypes as $tt)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">{{ $tt->nama }}</td>
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $tt->harga > 0 ? 'Rp '.number_format($tt->harga, 0, ',', '.') : 'Gratis' }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-700 dark:text-neutral-300 whitespace-nowrap">{{ $tt->kuota }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-700 dark:text-neutral-300 whitespace-nowrap">{{ $tt->terjual ?? 0 }}</td>
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                <form method="POST" action="{{ route('organizer.events.ticket-types.destroy', [$event, $tt]) }}"
                                      onsubmit="return confirm('Hapus jenis tiket ini?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="inline-flex items-center text-xs font-bold text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Belum ada jenis tiket yang ditambahkan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-public-layout>