<x-public-layout>
    <a href="{{ route('orders.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Tiket Saya</a>

    <div class="bg-white rounded-lg shadow p-6 mt-2">
        <h1 class="text-2xl font-bold">Pesanan #{{ $order->id }}</h1>
        <p class="text-gray-500 mt-1">{{ $order->ticketType->event->judul }}</p>

        <dl class="mt-4 grid sm:grid-cols-2 gap-2 text-sm">
            <div><dt class="text-gray-500">Jenis tiket</dt><dd class="font-medium">{{ $order->ticketType->nama }}</dd></div>
            <div><dt class="text-gray-500">Jumlah</dt><dd class="font-medium">{{ $order->jumlah }}</dd></div>
            <div><dt class="text-gray-500">Total</dt><dd class="font-medium">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</dd></div>
            <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ ucfirst($order->status) }}</dd></div>
        </dl>

        @if ($order->status === 'menunggu')
            <div class="mt-6 border-t pt-4">
                <h2 class="font-semibold mb-2">Pembayaran</h2>
                <p class="text-sm text-gray-600">
                    Transfer <strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong> ke rekening
                    <strong>BCA 1234567890 a.n. EventKu</strong> (contoh, ganti sesuai kebutuhan), lalu unggah bukti transfer.
                </p>

                @if ($order->bukti_bayar)
                    <p class="mt-3 text-sm text-green-700">Bukti sudah terkirim, menunggu verifikasi organizer.</p>
                    <img src="{{ asset('storage/'.$order->bukti_bayar) }}" alt="Bukti bayar" class="mt-2 max-h-64 rounded border">
                @endif

                <form method="POST" action="{{ route('orders.bukti', $order) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="file" name="bukti" accept="image/*">
                    @error('bukti') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    <button class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        {{ $order->bukti_bayar ? 'Kirim ulang bukti' : 'Kirim bukti' }}
                    </button>
                </form>
            </div>
        @endif

        @if ($order->status === 'lunas')
            <div class="mt-6 border-t pt-4">
                <h2 class="font-semibold mb-2">Tiket kamu</h2>
                <ul class="space-y-2">
                    @foreach ($order->tickets as $ticket)
                        <li class="border rounded p-3 flex items-center justify-between gap-4">
                            <div>
                                <span class="font-mono tracking-wider block">{{ $ticket->kode }}</span>
                                <span class="text-xs px-2 py-1 rounded inline-block mt-1 {{ $ticket->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </div>
                            <div>{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate($ticket->kode) !!}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-public-layout>