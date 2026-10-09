<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Pesanan Masuk</h1>

    @if (session('success')) <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div> @endif

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Pemesan</th>
                    <th class="p-3">Event / Tiket</th>
                    <th class="p-3">Jumlah</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Bukti</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t">
                        <td class="p-3">{{ $order->user->name ?? '-' }}</td>
                        <td class="p-3">{{ $order->ticketType->event->judul }}<br><span class="text-gray-500">{{ $order->ticketType->nama }}</span></td>
                        <td class="p-3">{{ $order->jumlah }}</td>
                        <td class="p-3">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</td>
                        <td class="p-3">
                            @if ($order->bukti_bayar)
                                <a href="{{ asset('storage/'.$order->bukti_bayar) }}" target="_blank" class="text-indigo-600 hover:underline">Lihat</a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-3">{{ ucfirst($order->status) }}</td>
                        <td class="p-3">
                            @if ($order->status === 'menunggu')
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('organizer.orders.approve', $order) }}">
                                        @csrf
                                        <button class="text-green-700 hover:underline">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('organizer.orders.reject', $order) }}"
                                          onsubmit="return confirm('Tolak pesanan ini?')">
                                        @csrf
                                        <button class="text-red-600 hover:underline">Tolak</button>
                                    </form>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-4 text-center text-gray-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-public-layout>