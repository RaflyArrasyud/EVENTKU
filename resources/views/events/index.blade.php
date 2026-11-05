<x-public-layout :wide="true">
    <section class="py-8 bg-deep min-h-[60vh]">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <h1 class="text-3xl font-display font-black text-white mb-1">Jelajahi Event</h1>
            <p class="text-neutral-400 mb-6">Temukan seminar dan webinar yang cocok untukmu.</p>

            <form method="GET" action="{{ route('events.index') }}" class="flex flex-col sm:flex-row gap-2 mb-8">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari event..."
                       class="bg-card border-white/10 text-white placeholder-neutral-500 rounded-xl px-4 py-2.5 flex-1 w-full focus:ring-lime-500 focus:border-lime-500">
                <select name="tipe" class="bg-card border-white/10 text-white rounded-xl px-4 py-2.5 w-full sm:w-auto focus:ring-lime-500 focus:border-lime-500">
                    <option value="">Semua tipe</option>
                    <option value="online" @selected(request('tipe') === 'online')>Online</option>
                    <option value="offline" @selected(request('tipe') === 'offline')>Offline</option>
                </select>
                <button class="bg-lime-400 text-deep px-6 py-2.5 rounded-xl font-bold hover:bg-lime-500 transition w-full sm:w-auto">Cari</button>
            </form>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($events as $event)
                    <x-event-card :event="$event" />
                @empty
                    <p class="text-neutral-400 col-span-full">Belum ada event yang cocok.</p>
                @endforelse
            </div>

            <div class="mt-8">{{ $events->links() }}</div>
        </div>
    </section>
</x-public-layout>