<x-public-layout>
    <a href="{{ route('organizer.events.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Event Saya</a>
    <h1 class="text-2xl font-bold mt-2 mb-4">Tiket: {{ $event->judul }}</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
            @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('organizer.events.ticket-types.store', $event) }}"
          class="bg-white p-4 rounded-lg shadow mb-6 grid sm:grid-cols-4 gap-3 items-end">
        @csrf
        <div>
            <label class="block text-sm font-medium">Nama tiket</label>
            <input type="text" name="nama" placeholder="Reguler / VIP" value="{{ old('nama') }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Harga (Rp, 0 = gratis)</label>
            <input type="number" name="harga" min="0" value="{{ old('harga', 0) }}" class="w-full border rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium">Kuota</label>
            <input type="number" name="kuota" min="1" value="{{ old('kuota', 50) }}" class="w-full border rounded px-3 py-2">
        </div>
        <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">+ Tambah</button>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Nama</th>
                    <th class="p-3">Harga</th>
                    <th class="p-3">Kuota</th>
                    <th class="p-3">Terjual</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ticketTypes as $tt)
                    <tr class="border-t">
                        <td class="p-3">{{ $tt->nama }}</td>
                        <td class="p-3">{{ $tt->harga > 0 ? 'Rp '.number_format($tt->harga, 0, ',', '.') : 'Gratis' }}</td>
                        <td class="p-3">{{ $tt->kuota }}</td>
                        <td class="p-3">{{ $tt->terjual ?? 0 }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('organizer.events.ticket-types.destroy', [$event, $tt]) }}"
                                  onsubmit="return confirm('Hapus jenis tiket ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-4 text-center text-gray-500">Belum ada jenis tiket.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-public-layout>