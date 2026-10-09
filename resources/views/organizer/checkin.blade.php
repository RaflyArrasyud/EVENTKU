<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Check-in Peserta</h1>

    @if (session('success')) <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div> @endif

    <form method="POST" action="{{ route('organizer.checkin.store') }}" class="bg-white p-6 rounded-lg shadow flex gap-3 max-w-xl">
        @csrf
        <input type="text" name="kode" placeholder="Masukkan kode tiket" autofocus
               class="flex-1 border rounded px-3 py-2 font-mono uppercase">
        <button class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Check-in</button>
    </form>
</x-public-layout>