<x-public-layout>
    <h1 class="text-3xl font-extrabold mb-1">Jelajahi Event</h1>
    <p class="text-slate-500 mb-6">Temukan seminar dan webinar yang cocok untukmu.</p>

    <form method="GET" action="{{ route('events.index') }}" class="flex flex-wrap gap-2 mb-8">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari event..."
               class="border-slate-300 rounded-xl px-4 py-2.5 flex-1 min-w-[200px] focus:ring-indigo-500 focus:border-indigo-500">
        <select name="tipe" class="border-slate-300 rounded-xl px-4 py-2.5 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Semua tipe</option>
            <option value="online" @selected(request('tipe') === 'online')>Online</option>
            <option value="offline" @selected(request('tipe') === 'offline')>Offline</option>
        </select>
        <button class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-indigo-700 transition">Cari</button>
    </form>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($events as $event)
            <x-event-card :event="$event" />
        @empty
            <p class="text-slate-500 col-span-full">Belum ada event yang cocok.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $events->links() }}</div>
</x-public-layout>