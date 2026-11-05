<x-public-layout>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-widest text-neon">Organizer</p>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-1">Check-in Tiket</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 mt-1">Pindai QR tiket peserta atau masukkan kode secara manual.</p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border-2 border-emerald-300 text-emerald-800 font-semibold rounded-2xl">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border-2 border-red-300 text-red-800 font-semibold rounded-2xl">{{ session('error') }}</div>
        @endif

        {{-- Kartu scanner --}}
        <div class="bg-ink text-white rounded-2xl shadow-md overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5">
                <h2 class="font-bold text-sm tracking-wide">SCAN QR TIKET</h2>
                <span id="scan-status" class="text-xs font-bold px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-neutral-300">Kamera belum aktif</span>
            </div>

            <div class="px-6 pt-4 flex flex-col sm:flex-row gap-3">
                <select id="camera-select" class="flex-1 bg-white/10 border border-white/15 rounded-xl px-4 py-2.5 text-sm font-medium text-white focus:border-neon focus:ring-neon">
                    <option value="" class="text-slate-900">Memuat daftar kamera...</option>
                </select>
                <div class="flex gap-2">
                    <button type="button" id="btn-start" class="bg-neon text-deep text-sm font-bold px-5 py-2.5 rounded-xl hover:brightness-110 transition">Mulai Scan</button>
                    <button type="button" id="btn-stop" class="text-sm font-bold px-5 py-2.5 rounded-xl border border-white/20 text-neutral-300 hover:border-neon hover:text-neon transition" disabled>Berhenti</button>
                </div>
            </div>

            <div class="p-6">
                <div class="relative rounded-2xl overflow-hidden border-2 border-dashed border-white/20 bg-black/60 aspect-video flex items-center justify-center">
                    <div id="reader" class="w-full [&_video]:rounded-2xl"></div>
                    <div id="reader-placeholder" class="absolute inset-0 flex flex-col items-center justify-center gap-2 text-center px-6 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7V5a2 2 0 012-2h2M17 3h2a2 2 0 012 2v2M21 17v2a2 2 0 01-2 2h-2M7 21H5a2 2 0 01-2-2v-2M3 12h18" /></svg>
                        <p class="text-sm font-semibold text-neutral-300">Arahkan QR tiket ke kamera</p>
                        <p class="text-xs text-neutral-500">Hasil pindaian otomatis mengisi kode &amp; check-in</p>
                    </div>
                </div>
                <p id="cam-error" class="hidden mt-3 text-sm font-semibold text-red-300 bg-red-500/10 border border-red-400/40 rounded-xl px-4 py-3"></p>
            </div>
        </div>

        {{-- Divider --}}
        <div class="flex items-center gap-4 my-6">
            <span class="flex-1 h-px bg-slate-300 dark:bg-white/10"></span>
            <span class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-neutral-400">Atau Input Manual</span>
            <span class="flex-1 h-px bg-slate-300 dark:bg-white/10"></span>
        </div>

        {{-- Form manual (kontrak: POST organizer.checkin.store, field kode) --}}
        <form id="checkin-form" method="POST" action="{{ route('organizer.checkin.store') }}" class="bg-white dark:bg-card p-6 rounded-2xl shadow-md border-2 border-slate-300 dark:border-line">
            @csrf
            <label for="kode-input" class="block text-xs font-black uppercase tracking-widest text-slate-500 dark:text-neutral-400 mb-2">Kode Tiket</label>
            <div class="flex flex-col sm:flex-row gap-3">
                <input id="kode-input" type="text" name="kode" value="{{ old('kode') }}" placeholder="cth: EVT-ABC123" autocomplete="off"
                       class="flex-1 border-2 border-slate-300 dark:border-line rounded-xl px-4 py-2.5 font-mono uppercase text-sm focus:border-neon focus:ring-neon">
                <button class="bg-ink text-white px-6 py-2.5 rounded-xl font-bold text-sm hover:bg-black transition w-full sm:w-auto">Check-in</button>
            </div>
            @error('kode')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
            <p class="mt-3 text-xs text-slate-500 dark:text-neutral-400 font-medium">Bisa ditempel dari hasil pindaian URL QR — sistem mengambil kode di ujungnya.</p>
        </form>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        (function () {
            const select = document.getElementById('camera-select');
            const statusEl = document.getElementById('scan-status');
            const errEl = document.getElementById('cam-error');
            const btnStart = document.getElementById('btn-start');
            const btnStop = document.getElementById('btn-stop');
            const placeholder = document.getElementById('reader-placeholder');
            const kodeInput = document.getElementById('kode-input');
            const form = document.getElementById('checkin-form');
            let qr = null, scanning = false, lastCode = '', lastAt = 0;

            function setStatus(text, active) {
                statusEl.textContent = text;
                statusEl.className = 'text-xs font-bold px-3 py-1.5 rounded-full border ' + (active
                    ? 'bg-neon/15 border-neon text-neon'
                    : 'bg-white/10 border-white/15 text-neutral-300');
            }
            function showError(msg) {
                errEl.textContent = msg;
                errEl.classList.remove('hidden');
            }
            function hideError() { errEl.classList.add('hidden'); }

            function loadCameras() {
                if (typeof Html5Qrcode === 'undefined') {
                    select.innerHTML = '<option value="" class="text-slate-900">Gagal memuat pustaka kamera</option>';
                    showError('Pustaka pemindai gagal dimuat. Periksa koneksi lalu muat ulang, atau pakai input manual di bawah.');
                    return;
                }
                Html5Qrcode.getCameras().then(devices => {
                    select.innerHTML = '';
                    if (!devices || !devices.length) {
                        select.innerHTML = '<option value="" class="text-slate-900">Tidak ada kamera ditemukan</option>';
                        showError('Tidak ada kamera ditemukan di perangkat ini. Silakan pakai input manual di bawah.');
                        return;
                    }
                    devices.forEach((d, i) => {
                        const o = document.createElement('option');
                        o.value = d.id;
                        o.textContent = d.label || ('Kamera ' + (i + 1));
                        o.className = 'text-slate-900';
                        select.appendChild(o);
                    });
                }).catch(() => {
                    select.innerHTML = '<option value="" class="text-slate-900">Izin kamera ditolak / tidak tersedia</option>';
                    showError('Izin kamera ditolak atau tidak tersedia. Klik ikon kamera di address bar untuk mengizinkan, atau pakai input manual di bawah.');
                    setStatus('Kamera diblokir', false);
                });
            }

            async function start() {
                hideError();
                if (scanning || typeof Html5Qrcode === 'undefined') return;
                const camId = select.value;
                if (!camId) { showError('Pilih kamera dulu, atau pakai input manual di bawah.'); return; }
                qr = qr || new Html5Qrcode('reader');
                setStatus('Meminta izin kamera...', false);
                try {
                    await qr.start({ deviceId: { exact: camId } }, { fps: 10, qrbox: { width: 250, height: 250 } },
                        onScan, () => {});
                    scanning = true;
                    placeholder.style.display = 'none';
                    btnStart.disabled = true;
                    btnStop.disabled = false;
                    setStatus('Memindai...', true);
                } catch (e) {
                    const name = (e && e.name) || '';
                    showError(name === 'NotAllowedError'
                        ? 'Izin kamera ditolak. Izinkan akses kamera di browser, lalu coba lagi — atau pakai input manual di bawah.'
                        : 'Kamera gagal dibuka (' + (e && e.message ? e.message : 'perangkat sibuk') + '). Pakai input manual di bawah.');
                    setStatus('Kamera gagal dibuka', false);
                }
            }
            async function stop() {
                if (!qr || !scanning) return;
                try { await qr.stop(); } catch (e) {}
                scanning = false;
                placeholder.style.display = '';
                btnStart.disabled = false;
                btnStop.disabled = true;
                setStatus('Kamera berhenti', false);
            }

            function onScan(text) {
                const now = Date.now();
                if (text === lastCode && now - lastAt < 2500) return;
                lastCode = text; lastAt = now;
                kodeInput.value = (text || '').trim();
                setStatus('Terdeteksi: ' + kodeInput.value.slice(0, 24), true);
                stop().then(() => form.submit());
            }

            btnStart.addEventListener('click', start);
            btnStop.addEventListener('click', stop);
            select.addEventListener('change', () => { if (scanning) stop().then(start); });
            loadCameras();
        })();
    </script>
</x-public-layout>