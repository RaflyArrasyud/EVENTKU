<x-public-layout>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if ($event->poster)
            <img src="{{ asset('storage/'.$event->poster) }}" alt="" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6">
            <span class="text-xs uppercase text-indigo-600 font-semibold">{{ $event->kategori }} &bull; {{ $event->tipe }}</span>
            <h1 class="text-3xl font-bold mt-1">{{ $event->judul }}</h1>
            <p class="text-gray-500 mt-2">
                {{ $event->tanggal_mulai->format('d M Y, H:i') }} -
                {{ $event->tanggal_selesai->format('d M Y, H:i') }}
            </p>
            <p class="mt-1 text-gray-500">Lokasi/Link: {{ $event->lokasi }}</p>
            <p class="mt-1 text-gray-500">Penyelenggara: {{ $event->organizer->name }}</p>
            <div class="mt-4 whitespace-pre-line">{{ $event->deskripsi }}</div>

            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-3">Tiket</h2>

                @if ($errors->has('jumlah'))
                    <div class="mb-3 p-3 bg-red-100 text-red-800 rounded">{{ $errors->first('jumlah') }}</div>
                @endif

                @forelse ($event->ticketTypes as $tt)
                    @php $sisa = $tt->sisa(); @endphp
                    <div class="border rounded p-4 mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $tt->nama }}</p>
                            <p class="text-indigo-600 font-semibold">
                                {{ $tt->harga > 0 ? 'Rp '.number_format($tt->harga, 0, ',', '.') : 'Gratis' }}
                            </p>
                            <p class="text-sm text-gray-500">Sisa kuota: {{ $sisa }}</p>
                        </div>

                        @auth
                            @if ($sisa > 0)
                                <form method="POST" action="{{ route('orders.store', $tt) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="jumlah" value="1" min="1" max="5"
                                           class="border rounded px-2 py-1 w-16">
                                    <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Beli</button>
                                </form>
                            @else
                                <span class="text-red-600 font-medium">Habis</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Login untuk membeli</a>
                        @endauth
                    </div>
                @empty
                    <p class="text-gray-500">Tiket belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-public-layout>