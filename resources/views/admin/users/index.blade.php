<x-public-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Kelola User</h1>
        <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Angkat peserta menjadi organizer atau kelola peran akun.</p>
    </div>

    @if (session('error')) <div class="mb-6 p-4 bg-red-50 border-2 border-red-300 text-red-800 font-semibold rounded-2xl">{{ session('error') }}</div> @endif

    @php $reqs = $requests ?? collect(); @endphp
    <section class="mb-6 bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Pengajuan masuk</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Nama</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">Alasan</th>
                        <th class="py-4 px-6">Kontak</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($reqs as $r)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $r->user->name ?? '-' }}</td>
                            <td class="py-4 px-6 font-medium text-slate-600 dark:text-neutral-400 whitespace-nowrap">{{ $r->user->email ?? '-' }}</td>
                            <td class="py-4 px-6 text-slate-700 dark:text-neutral-300">{{ $r->alasan ?: '-' }}</td>
                            <td class="py-4 px-6 font-medium text-slate-600 dark:text-neutral-400 whitespace-nowrap">{{ $r->kontak ?: '-' }}</td>
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="POST" action="{{ route('admin.organizer-requests.approve', $r) }}">
                                        @csrf
                                        <button class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 hover:bg-emerald-100 px-3 py-1.5 rounded-lg transition">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.organizer-requests.reject', $r) }}" onsubmit="return confirm('Tolak pengajuan ini?')">
                                        @csrf
                                        <button class="text-xs font-bold text-red-700 bg-red-50 border border-red-300 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">Tolak</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Tidak ada pengajuan menunggu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line shadow-md rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/80 dark:bg-white/5 border-b-2 border-slate-300 dark:border-line text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300">
                    <tr>
                        <th class="py-4 px-6">Nama</th>
                        <th class="py-4 px-6">Email</th>
                        <th class="py-4 px-6">Role</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y border-slate-200 dark:border-white/10">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-white/10 transition-colors">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $u->name }}</td>
                            <td class="py-4 px-6 font-medium text-slate-600 dark:text-neutral-400 whitespace-nowrap">{{ $u->email }}</td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full
                                    {{ $u->role === 'admin' ? 'bg-violet-100 text-violet-800 border border-violet-300' : ($u->role === 'organizer' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-200 text-slate-700 border border-slate-300') }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap text-right">
                                @if ($u->id === auth()->id())
                                    <span class="text-xs font-medium text-slate-400 dark:text-neutral-500">Akun sendiri</span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="inline-flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="text-xs font-semibold border border-slate-300 dark:border-line rounded-lg px-2.5 py-1.5 bg-white dark:bg-card">
                                            @foreach (['peserta', 'organizer', 'admin'] as $role)
                                                <option value="{{ $role }}" @selected($u->role === $role)>{{ ucfirst($role) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="text-xs font-bold text-ink dark:text-white bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line hover:bg-slate-200 dark:hover:bg-white/20 px-3 py-1.5 rounded-lg transition">Simpan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-12 px-6 text-center text-slate-500 dark:text-neutral-400 font-medium">Belum ada user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</x-public-layout>