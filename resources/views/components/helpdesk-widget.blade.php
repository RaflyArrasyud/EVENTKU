{{-- Helpdesk EventKu: FAQ + form bantuan + thread polling. Null-safe bila endpoint /bantuan belum ada. --}}
<div x-data="helpdeskWidget()" x-init="init({{ auth()->check() && auth()->user()->isAdmin() ? 'true' : 'false' }}, {{ auth()->check() ? 'true' : 'false' }})"
     class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-40 flex flex-col items-end gap-3">
    {{-- Panel --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         class="w-[calc(100vw-2rem)] max-w-[22rem] bg-white dark:bg-card rounded-2xl shadow-2xl border-2 border-slate-300 dark:border-line overflow-hidden origin-bottom-right">
        <div class="bg-ink text-white px-5 py-4 flex items-center justify-between gap-3">
            <div>
                <p class="font-black text-sm">Butuh Bantuan?</p>
                <p class="text-xs text-neutral-400 font-medium">FAQ &amp; chat bantuan EventKu</p>
            </div>
            <button type="button" @click="open = false" aria-label="Tutup bantuan"
                    class="p-1.5 rounded-full hover:bg-white/10 text-neutral-300 hover:text-neon transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto p-5 flex flex-col gap-4">
            {{-- FAQ singkat --}}
            <div class="flex flex-col gap-2">
                <p class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-neutral-400">Pertanyaan Umum</p>
                <details class="rounded-xl border border-slate-200 dark:border-white/10 overflow-hidden">
                    <summary class="px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-neutral-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/10">Lupa password?</summary>
                    <p class="px-4 pb-3 text-sm text-slate-600 dark:text-neutral-400">Klik Login lalu “Lupa password”, masukkan email, dan ikuti tautan reset yang dikirim.</p>
                </details>
                <details class="rounded-xl border border-slate-200 dark:border-white/10 overflow-hidden">
                    <summary class="px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-neutral-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/10">Cara bayar tiket?</summary>
                    <p class="px-4 pb-3 text-sm text-slate-600 dark:text-neutral-400">Pilih tiket lalu order, upload bukti bayar di halaman pesanan, dan tunggu verifikasi organizer.</p>
                </details>
                <details class="rounded-xl border border-slate-200 dark:border-white/10 overflow-hidden">
                    <summary class="px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-neutral-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/10">Tiket hilang?</summary>
                    <p class="px-4 pb-3 text-sm text-slate-600 dark:text-neutral-400">Buka Dashboard lalu Tiket Saya — QR dan kode tiket selalu tampil di sana.</p>
                </details>
                <details class="rounded-xl border border-slate-200 dark:border-white/10 overflow-hidden">
                    <summary class="px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-neutral-200 cursor-pointer hover:bg-slate-50 dark:hover:bg-white/10">Jadi organizer?</summary>
                    <p class="px-4 pb-3 text-sm text-slate-600 dark:text-neutral-400">Daftar dan login, lalu kirim pesan topik “Jadi Organizer” lewat form di bawah — admin akan mempromosikan akunmu.</p>
                </details>
            </div>

            @guest
                <a href="{{ route('login') }}" class="block text-center rounded-xl bg-ink px-5 py-2.5 text-sm font-bold text-white hover:bg-black transition shadow">
                    Login untuk chat bantuan
                </a>
            @endguest

            @auth
                @if (auth()->user()->isAdmin())
                    {{-- Admin: inbox bantuan, tanpa form kirim --}}
                    <a href="{{ Route::has('admin.help.index') ? route('admin.help.index') : '#' }}"
                       class="flex items-center justify-between gap-2 rounded-xl bg-ink px-5 py-3 text-sm font-bold text-white hover:bg-black transition shadow">
                        <span>Buka Inbox Bantuan</span>
                        @if (($helpTerbuka ?? 0) > 0)
                            <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-neon px-1.5 text-xs font-black text-deep">{{ $helpTerbuka > 9 ? '9+' : $helpTerbuka }}</span>
                        @endif
                    </a>
                    <p class="text-xs font-medium text-slate-500 dark:text-neutral-400 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3">
                        Balas pesan peserta dari inbox bantuan.
                    </p>
                @else
                {{-- Form + thread: disembunyikan bila endpoint belum ada --}}
                <div x-show="available" class="flex flex-col gap-3">
                    <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 p-3" x-ref="thread">
                        <template x-for="m in messages" :key="m.id">
                            <div :class="m.dari_admin ? 'self-start bg-neon/15 border-neon/40' : 'self-end bg-white dark:bg-card border-slate-200 dark:border-white/10'"
                                 class="max-w-[85%] rounded-xl border px-3 py-2">
                                <p class="text-sm text-slate-800 dark:text-neutral-200" x-text="m.isi"></p>
                                <p class="mt-1 text-[11px] font-semibold text-slate-400 dark:text-neutral-500" x-text="(m.dari_admin ? 'Admin · ' : 'Kamu · ') + (m.created ?? '')"></p>
                            </div>
                        </template>
                        <p x-show="!messages.length" class="text-sm text-slate-400 dark:text-neutral-500 text-center py-4">Belum ada pesan. Kirim pesan pertamamu di bawah.</p>
                    </div>

                    <form @submit.prevent="kirim()" class="flex flex-col gap-2">
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-neutral-400" for="hd-topik">Topik</label>
                        <select id="hd-topik" x-model="topik" class="rounded-xl border-slate-300 dark:border-line bg-white dark:bg-card text-sm font-medium text-slate-900 dark:text-white focus:border-neon focus:ring-neon">
                            <option class="bg-white dark:bg-card text-slate-900 dark:text-white">Pembayaran</option>
                            <option class="bg-white dark:bg-card text-slate-900 dark:text-white">Tiket</option>
                            <option class="bg-white dark:bg-card text-slate-900 dark:text-white">Akun</option>
                            <option class="bg-white dark:bg-card text-slate-900 dark:text-white">Jadi Organizer</option>
                            <option class="bg-white dark:bg-card text-slate-900 dark:text-white">Lainnya</option>
                        </select>
                        <label class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-neutral-400" for="hd-isi">Pesan</label>
                        <textarea id="hd-isi" x-model="isi" rows="2" required placeholder="Tulis pesanmu..."
                                  class="rounded-xl border-slate-300 dark:border-line bg-white dark:bg-card text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:text-neutral-500 focus:border-neon focus:ring-neon"></textarea>
                        <button :disabled="sending" class="rounded-xl bg-ink px-5 py-2.5 text-sm font-bold text-white hover:bg-black transition shadow disabled:opacity-50"
                                x-text="sending ? 'Mengirim...' : 'Kirim Pesan'"></button>
                        <p x-show="formInfo" x-text="formInfo" class="text-xs font-semibold text-slate-500 dark:text-neutral-400"></p>
                    </form>
                </div>
                <p x-show="!available" class="text-xs font-medium text-slate-500 dark:text-neutral-400 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl px-4 py-3">
                    Layanan chat bantuan belum tersedia — lihat FAQ di atas atau coba lagi nanti.
                </p>
                @endif
            @endauth
        </div>
    </div>

    {{-- Floating button --}}
    <button type="button" @click="open = !open; if (open && isAuth && !isAdmin) { unread = 0; fetchPesan(); }" aria-label="Buka bantuan"
            class="relative flex h-14 w-14 items-center justify-center rounded-full bg-ink text-white shadow-2xl border-2 border-neon/60 hover:bg-black transition-colors">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 21l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        <span x-show="unread > 0 && !open" x-cloak x-text="unread > 9 ? '9+' : unread"
              class="absolute -top-1 -right-1 flex h-6 min-w-6 items-center justify-center rounded-full bg-neon px-1.5 text-xs font-black text-deep shadow"></span>
    </button>
</div>

<script>
    function helpdeskWidget() {
        return {
            open: false, available: true, sending: false, isAdmin: false, isAuth: false,
            topik: 'Pembayaran', isi: '', formInfo: '',
            messages: [], sinceId: 0, unread: 0, timer: null,
            csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; },
            init(isAdmin = false, isAuth = false) {
                this.isAdmin = isAdmin;
                this.isAuth = isAuth;
                // Tamu & admin: tanpa fetch/polling (endpoint butuh auth).
                if (isAdmin || !isAuth) return;
                this.fetchPesan();
                this.timer = setInterval(() => this.fetchPesan(), 8000);
            },
            async fetchPesan() {
                try {
                    const r = await fetch('/bantuan/pesan?since_id=' + this.sinceId, { headers: { 'Accept': 'application/json' } });
                    if (!r.ok) throw new Error('unavailable');
                    const j = await r.json();
                    const list = Array.isArray(j) ? j : (j.messages || []);
                    if (list.length) {
                        this.messages.push(...list);
                        this.sinceId = Math.max(...this.messages.map(m => m.id));
                        if (!this.open) this.unread += list.filter(m => m.dari_admin && !m.dibaca).length;
                        else this.$nextTick(() => this.$refs.thread && (this.$refs.thread.scrollTop = this.$refs.thread.scrollHeight));
                    }
                } catch (e) { this.available = false; }
            },
            async kirim() {
                if (!this.isi.trim() || this.sending) return;
                this.sending = true; this.formInfo = '';
                try {
                    const r = await fetch('/bantuan', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf() },
                        body: JSON.stringify({ topik: this.topik, isi: this.isi.trim() })
                    });
                    if (!r.ok) throw new Error('send-failed');
                    this.isi = '';
                    this.formInfo = 'Pesan terkirim.';
                    await this.fetchPesan();
                } catch (e) {
                    this.available = false;
                    this.formInfo = 'Gagal mengirim — layanan belum tersedia.';
                } finally { this.sending = false; }
            }
        };
    }
</script>