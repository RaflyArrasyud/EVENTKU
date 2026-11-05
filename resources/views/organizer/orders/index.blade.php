<x-public-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Pesanan Masuk</h1>
        <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Verifikasi pembayaran dan persetujuan pesanan tiket dari peserta.</p>
    </div>

    @if (session('success')) <div class="mb-6 p-4 bg-emerald-50 border-2 border-emerald-300 text-emerald-800 font-semibold rounded-2xl">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-6 p-4 bg-red-50 border-2 border-red-300 text-red-800 font-semibold rounded-2xl">{{ session('error') }}</div> @endif

    <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Pemesan</th>
                        <th class="py-4 px-6">Event / Tiket</th>
                        <th class="py-4 px-6">Jumlah</th>
                        <th class="py-4 px-6">Total</th>
                        <th class="py-4 px-6">Bukti</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($orders as $order)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $order->user->name ?? '-' }}</td>
                            <td class="py-4 px-6">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $order->ticketType->event->judul }}</p>
                                <span class="text-xs font-semibold text-slate-500 dark:text-neutral-400">{{ $order->ticketType->nama }}</span>
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-700 dark:text-neutral-300 whitespace-nowrap">{{ $order->jumlah }}</td>
                            <td class="py-4 px-6 font-black text-slate-900 dark:text-white whitespace-nowrap">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if ($order->bukti_bayar)
                                    <a href="{{ route('orders.proof', $order) }}" target="_blank" class="inline-flex items-center text-xs font-bold text-lime-600 bg-lime-50 border border-lime-300 hover:bg-lime-100 px-3 py-1.5 rounded-lg transition">
                                        Lihat Bukti
                                    </a>
                                @else
                                        <span class="text-slate-400 dark:text-neutral-500 font-semibold">-</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @php
                                    $isGw = method_exists($order, 'isGatewayPayment') ? (bool) $order->isGatewayPayment() : (($order->getAttribute('payment_method') ?? null) === 'midtrans_qris');
                                    $gwStatus = $isGw ? ($order->getAttribute('payment_status') ?? null) : null;
                                @endphp
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                    {{ $order->status === 'lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($order->status === 'batal' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                @if ($isGw)
                                    <span class="block text-xs font-semibold text-slate-500 dark:text-neutral-400 mt-1">Otomatis • QRIS demo{{ $gwStatus ? ' • '.ucfirst($gwStatus) : '' }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                @if ($order->status === 'menunggu' && !$isGw)
                                    <div class="flex items-center justify-end gap-2">
                                        <form method="POST" action="{{ route('organizer.orders.approve', $order) }}">
                                            @csrf
                                            <button class="inline-flex items-center text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('organizer.orders.reject', $order) }}"
                                              onsubmit="return confirm('Tolak pesanan ini?')">
                                            @csrf
                                            <button class="inline-flex items-center text-xs font-bold text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs font-medium text-slate-400 dark:text-neutral-500">Selesai</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Belum ada pesanan masuk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
</x-public-layout>
