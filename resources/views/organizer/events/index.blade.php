<x-public-layout>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Event Saya</h1>
        <a href="{{ route('organizer.events.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">+ Buat Event</a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="p-3">Judul</th>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($events as $event)
                    <tr class="border-t">
                        <td class="p-3">{{ $event->judul }}</td>
                        <td class="p-3">{{ $event->tanggal_mulai->format('d/m/Y H:i') }}</td>
                        <td class="p-3">{{ ucfirst($event->status) }}</td>
                        <td class="p-3 flex gap-3">
                            <a href="{{ route('organizer.events.ticket-types.index', $event) }}" class="text-green-700 hover:underline">Kelola Tiket</a>
                            <a href="{{ route('organizer.events.edit', $event) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('organizer.events.destroy', $event) }}"
                                  onsubmit="return confirm('Hapus event ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-4 text-center text-gray-500">Belum ada event.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $events->links() }}</div>
</x-public-layout>