<x-public-layout>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Event Saya</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Kelola seluruh seminar, webinar, dan alokasi tiket Anda.</p>
        </div>
        <a href="{{ route('organizer.events.create') }}" class="bg-ink text-white font-bold text-sm px-5 py-2.5 rounded-xl hover:bg-neutral-800 shadow-md transition">+ Buat Event</a>
    </div>

    <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Judul</th>
                        <th class="py-4 px-6">Tanggal</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($events as $event)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">
                                {{ $event->judul }}
                                <span class="block text-xs font-semibold text-slate-500 dark:text-neutral-400 mt-0.5">{{ ucfirst($event->tipe) }} &bull; {{ $event->kategori }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-700 dark:text-neutral-300 font-medium whitespace-nowrap">
                                {{ $event->tanggal_mulai->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $event->status === 'terbit' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="inline-flex items-center text-xs font-bold text-lime-600 bg-lime-50 border border-lime-300 hover:bg-lime-100 px-3 py-1.5 rounded-lg transition shadow-sm">
                                        Kelola Tiket
                                    </a>
                                    <a href="{{ route('organizer.events.edit', $event) }}" class="inline-flex items-center text-xs font-bold text-slate-700 dark:text-neutral-300 bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line hover:bg-slate-200 dark:hover:bg-white/20 px-3 py-1.5 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('organizer.events.destroy', $event) }}"
                                          onsubmit="return confirm('Hapus event ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="inline-flex items-center text-xs font-bold text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">
                                Belum ada event yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $events->links() }}</div>
</x-public-layout>