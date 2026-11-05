<x-public-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Tiket Saya</h1>
        <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Daftar e-ticket seminar dan webinar yang telah Anda daftarkan.</p>
    </div>

    <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Event</th>
                        <th class="py-4 px-6">Jenis Tiket</th>
                        <th class="py-4 px-6">Jumlah</th>
                        <th class="py-4 px-6">Total Bayar</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 text-slate-900 dark:text-white">
                                <p class="font-bold">{{ $order->ticketType->event->judul }}</p>
                                <p class="mt-1 text-xs font-medium text-slate-500 dark:text-neutral-400">
                                    Pesanan #{{ $order->id }}
                                    @if ($order->created_at)
                                        · <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>
                                    @endif
                                </p>
                                @if ($order->status === 'lunas' && $order->ticketType->event->tanggal_selesai && now()->greaterThanOrEqualTo($order->ticketType->event->tanggal_selesai))
                                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-neutral-400">Event selesai · masa check-in berakhir</p>
                                @endif
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-700 dark:text-neutral-300 whitespace-nowrap">{{ $order->ticketType->nama }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-700 dark:text-neutral-300 whitespace-nowrap">{{ $order->jumlah }}</td>
                            <td class="py-4 px-6 font-black text-slate-900 dark:text-white whitespace-nowrap">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $order->status === 'lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($order->status === 'batal' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center text-xs font-bold text-lime-600 bg-lime-50 border border-lime-300 hover:bg-lime-100 px-3.5 py-1.5 rounded-lg transition shadow-sm">
                                    Detail Tiket
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Belum ada tiket atau pesanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-public-layout>
