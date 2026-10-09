<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Tiket Saya</h1>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Event</th>
                    <th class="p-3">Tiket</th>
                    <th class="p-3">Jumlah</th>
                    <th class="p-3">Total</th>
                    <th class="p-3">Status</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t">
                        <td class="p-3">{{ $order->ticketType->event->judul }}</td>
                        <td class="p-3">{{ $order->ticketType->nama }}</td>
                        <td class="p-3">{{ $order->jumlah }}</td>
                        <td class="p-3">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold
                                {{ $order->status === 'lunas' ? 'bg-green-100 text-green-800' : ($order->status === 'batal' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="p-3"><a href="{{ route('orders.show', $order) }}" class="text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-4 text-center text-gray-500">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-public-layout>