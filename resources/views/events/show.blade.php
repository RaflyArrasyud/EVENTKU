<x-public-layout>
    <div class="bg-white dark:bg-card rounded-lg shadow overflow-hidden">
        @if ($event->poster)
            <img src="{{ asset('storage/'.$event->poster) }}" alt="" class="w-full max-h-96 object-cover">
        @endif
        <div class="p-6">
            <span class="text-xs uppercase text-lime-600 font-semibold">{{ $event->kategori }} &bull; {{ $event->tipe }}</span>
            <h1 class="text-3xl font-bold mt-1">{{ $event->judul }}</h1>
            <p class="text-gray-500 dark:text-neutral-400 mt-2">
                {{ $event->tanggal_mulai->format('d M Y, H:i') }} -
                {{ $event->tanggal_selesai->format('d M Y, H:i') }}
            </p>
            <p class="mt-1 text-gray-500 dark:text-neutral-400">Lokasi/Link: {{ $event->lokasi }}</p>
            <p class="mt-1 text-gray-500 dark:text-neutral-400">Penyelenggara: {{ $event->organizer->name }}</p>
            <div class="mt-4 whitespace-pre-line">{{ $event->deskripsi }}</div>

            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-3">Tiket</h2>

                @if ($errors->has('jumlah'))
                    <div class="mb-3 p-3 bg-red-100 text-red-800 rounded">{{ $errors->first('jumlah') }}</div>
                @endif

                @forelse ($event->ticketTypes as $tt)
                    @php $sisa = $tt->sisa(); @endphp
                    <div class="border border-slate-200 dark:border-white/10 rounded-xl p-4 mb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <p class="font-medium text-slate-800 dark:text-neutral-200">{{ $tt->nama }}</p>
                            <p class="text-lime-600 font-semibold">
                                {{ $tt->harga > 0 ? 'Rp '.number_format($tt->harga, 0, ',', '.') : 'Gratis' }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-neutral-400">Sisa kuota: {{ $sisa }}</p>
                        </div>

                        @auth
                            @if ($sisa > 0)
                                <form method="POST" action="{{ route('orders.store', $tt) }}" class="flex items-center gap-2 w-full sm:w-auto" x-data="{ submitting: false }" @submit="submitting = true">
                                    @csrf
                                    <input type="hidden" name="checkout_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                                    <input type="number" name="jumlah" value="1" min="1" max="5"
                                           class="border border-slate-300 dark:border-line bg-white dark:bg-card text-slate-900 dark:text-white rounded-lg px-2 py-1.5 w-16 text-center">
                                    <button type="submit" :disabled="submitting" class="bg-ink text-white px-5 py-2 rounded-lg hover:bg-neutral-800 transition flex-1 sm:flex-initial font-semibold text-sm disabled:opacity-60 disabled:cursor-not-allowed"><span x-text="submitting ? 'Memproses…' : 'Beli'">Beli</span></button>
                                </form>
                            @else
                                <span class="text-red-600 font-medium">Habis</span>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="text-lime-600 hover:underline text-sm font-semibold">Login untuk membeli</a>
                        @endauth
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-neutral-400">Tiket belum tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-public-layout>
