<x-public-layout>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('organizer.events.index') }}" class="text-xs font-bold uppercase tracking-wider text-lime-600 hover:text-lime-700 inline-flex items-center gap-1.5 mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke daftar event
            </a>
            <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Edit Event</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Perbarui detail dan jadwal event Anda.</p>
        </div>

        <form method="POST" action="{{ route('organizer.events.update', $event) }}" enctype="multipart/form-data"
              class="bg-white dark:bg-card p-8 rounded-2xl border-2 border-slate-300 dark:border-line shadow-md">
            @csrf @method('PUT')
            @include('organizer.events._form')
        </form>
    </div>
</x-public-layout>