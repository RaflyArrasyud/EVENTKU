<x-public-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Inbox Bantuan</h1>
        <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Balas pertanyaan user dan tutup tiket yang selesai.</p>
    </div>

    @if (session('success')) <div class="mb-6 p-4 bg-emerald-50 border-2 border-emerald-300 text-emerald-800 font-semibold rounded-2xl">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="mb-6 p-4 bg-red-50 border-2 border-red-300 text-red-800 font-semibold rounded-2xl">{{ session('error') }}</div> @endif

    <div class="grid gap-6">
        @forelse ($tickets as $t)
            <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
                <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <p class="font-black text-slate-900 dark:text-white">{{ $t->topik }}</p>
                        <p class="text-xs font-medium text-slate-500 dark:text-neutral-400">{{ $t->user->name ?? '-' }} &bull; {{ $t->created_at->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.help.close', $t) }}">
                        @csrf
                        <button class="text-xs font-bold text-slate-600 dark:text-neutral-400 bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line hover:bg-slate-200 dark:hover:bg-white/20 px-3 py-1.5 rounded-lg transition">Tutup</button>
                    </form>
                </div>
                <div class="p-6 space-y-3">
                    @foreach ($t->messages as $m)
                        <div class="flex {{ $m->user && $m->user->role === 'admin' ? 'justify-end' : 'justify-start' }}">
                            <div class="w-fit max-w-[75%] rounded-xl px-4 py-2.5 text-sm {{ $m->user && $m->user->role === 'admin' ? 'bg-emerald-50 border border-emerald-200 text-emerald-900' : 'bg-slate-100 border border-slate-200 text-slate-800' }}">
                                <p class="text-xs font-bold {{ $m->user && $m->user->role === 'admin' ? 'text-emerald-600' : 'text-slate-500 dark:text-neutral-400' }}">{{ $m->user->name ?? '-' }}</p>
                                <p class="mt-0.5">{{ $m->isi }}</p>
                                <p class="mt-1 text-[11px] font-medium opacity-60">{{ $m->created_at?->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                    <form method="POST" action="{{ route('admin.help.reply', $t) }}" class="flex gap-2 pt-2">
                        @csrf
                        <input type="text" name="isi" placeholder="Tulis balasan..." class="flex-1 border border-slate-300 dark:border-line rounded-xl px-4 py-2.5 text-sm">
                        <button class="rounded-xl bg-neon px-5 py-2.5 font-bold text-deep text-sm hover:brightness-110 transition">Balas</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line rounded-2xl p-12 text-center text-slate-500 dark:text-neutral-400 font-medium">Tidak ada tiket terbuka.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $tickets->links() }}</div>
</x-public-layout>