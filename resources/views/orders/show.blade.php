<x-public-layout>
    @php
        $event = $order->ticketType->event;
        $eventStart = $event->tanggal_mulai?->copy()->timezone('Asia/Jakarta');
        $eventEnd = $event->tanggal_selesai?->copy()->timezone('Asia/Jakarta');
        $eventEnded = $eventEnd && now()->greaterThanOrEqualTo($eventEnd);
        $orderedAt = $order->created_at?->copy()->timezone('Asia/Jakarta');
        $paidAt = $order->paid_at?->copy()->timezone('Asia/Jakarta');
        $payDeadline = $order->payment_expires_at?->copy()->timezone('Asia/Jakarta');
    @endphp
    <div class="max-w-4xl mx-auto">
        <a href="{{ route('orders.index') }}" class="text-xs font-bold uppercase tracking-wider text-lime-600 hover:text-lime-700 inline-flex items-center gap-1.5 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Tiket Saya
        </a>

        <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md p-6 sm:p-8">
            <div class="border-b-2 border-slate-100 dark:border-white/10 pb-5">
                <span class="text-xs font-bold uppercase tracking-wider text-lime-600">Detail Transaksi</span>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-1">Pesanan #{{ $order->id }}</h1>
                <p class="text-sm font-semibold text-slate-500 dark:text-neutral-400 mt-1">{{ $order->ticketType->event->judul }}</p>
            </div>

            <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm bg-slate-50/80 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl p-5">
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Jenis Tiket</dt>
                    <dd class="font-bold text-slate-900 dark:text-white text-base mt-0.5">{{ $order->ticketType->nama }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Jumlah Pesanan</dt>
                    <dd class="font-bold text-slate-900 dark:text-white text-base mt-0.5">{{ $order->jumlah }} Tiket</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Total Pembayaran</dt>
                    <dd class="font-black text-slate-900 dark:text-white text-base mt-0.5">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400">Status Pesanan</dt>
                    <dd class="mt-0.5">
                        <span class="inline-block text-xs font-bold px-2.5 py-1 rounded-full
                            {{ $order->status === 'lunas' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : ($order->status === 'batal' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-amber-100 text-amber-800 border border-amber-300') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </dd>
                </div>
            </dl>

            <section class="mt-6" aria-labelledby="transaction-time-title">
                <h2 id="transaction-time-title" class="font-display font-bold text-base text-slate-900 dark:text-white">Waktu transaksi</h2>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-neutral-400">Pesanan dibuat</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">
                            @if ($orderedAt)<time datetime="{{ $orderedAt->toIso8601String() }}">{{ $orderedAt->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>@else — @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-neutral-400">{{ $order->total == 0 ? 'Pendaftaran dikonfirmasi' : 'Pembayaran lunas / dikonfirmasi' }}</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">
                            @if ($paidAt)
                                <time datetime="{{ $paidAt->toIso8601String() }}">{{ $paidAt->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>
                            @elseif ($order->status === 'lunas')
                                Waktu konfirmasi belum tercatat
                            @elseif ($order->status === 'batal')
                                {{ $order->payment_status === 'settlement_review' ? 'Perlu ditinjau pengelola' : 'Pesanan dibatalkan' }}
                            @else
                                Belum dikonfirmasi
                            @endif
                        </dd>
                    </div>
                    @if ($order->isGatewayPayment() && $payDeadline)
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500 dark:text-neutral-400">Batas pembayaran QRIS</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white"><time datetime="{{ $payDeadline->toIso8601String() }}">{{ $payDeadline->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time></dd>
                        <p class="mt-1 text-xs text-slate-500 dark:text-neutral-400">{{ $order->status === 'lunas' ? 'Pembayaran sudah selesai. Batas ini bukan masa berlaku tiket.' : 'Batas ini untuk pembayaran, bukan untuk check-in. Status akhir mengikuti konfirmasi penyedia pembayaran.' }}</p>
                    </div>
                    @endif
                </dl>
            </section>

            <section class="mt-6 border-t border-slate-200 dark:border-white/10 pt-5" aria-labelledby="ticket-validity-title">
                <h2 id="ticket-validity-title" class="font-display font-bold text-base text-slate-900 dark:text-white">Jadwal dan masa berlaku tiket</h2>
                <dl class="mt-3 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-neutral-400">Event mulai</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">@if ($eventStart)<time datetime="{{ $eventStart->toIso8601String() }}">{{ $eventStart->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>@else — @endif</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-neutral-400">Batas berlaku / event selesai</dt>
                        <dd class="mt-1 font-semibold text-slate-900 dark:text-white">@if ($eventEnd)<time datetime="{{ $eventEnd->toIso8601String() }}">{{ $eventEnd->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time>@else Belum ditentukan @endif</dd>
                    </div>
                </dl>
                <p class="mt-3 text-sm text-slate-600 dark:text-neutral-300">
                    @if ($eventEnded)
                        Event sudah selesai. Tiket yang belum digunakan tidak bisa dipakai untuk check-in lagi. Riwayat transaksi tetap tersimpan.
                    @else
                        Setiap tiket berlaku untuk satu kali check-in. Setelah digunakan atau waktu event selesai tercapai, tiket tidak bisa dipakai lagi.
                    @endif
                </p>
            </section>

            @if ($order->status === 'menunggu')
                @php
                    $payConfigured = $paymentConfigured ?? false;
                    $isGateway = method_exists($order, 'isGatewayPayment') ? (bool) $order->isGatewayPayment() : (($order->getAttribute('payment_method') ?? null) === 'midtrans_qris');
                    $pStatus = $isGateway ? ($order->getAttribute('payment_status') ?? null) : null;
                    $pExpires = $isGateway ? ($order->getAttribute('payment_expires_at') ?? null) : null;
                    $qrString = $isGateway ? ($order->getAttribute('qr_string') ?? null) : null;
                    $qrUrl = $isGateway ? ($order->getAttribute('payment_qr_url') ?? null) : null;
                    $expIso = $pExpires instanceof \DateTimeInterface ? $pExpires->toIso8601String() : $pExpires;
                    $expText = $pExpires instanceof \DateTimeInterface ? $pExpires->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i').' WIB' : $pExpires;
                    $payLabels = ['created' => 'Dibuat', 'creating' => 'Menyiapkan QR', 'pending' => 'Menunggu pembayaran', 'settlement' => 'Lunas', 'expire' => 'Kadaluarsa', 'deny' => 'Ditolak', 'cancel' => 'Dibatalkan', 'error' => 'Gagal', 'review' => 'Ditinjau'];
                @endphp
                @if ($isGateway)
                <div class="mt-8 border-t-2 border-slate-200 dark:border-white/10 pt-6">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="font-display font-bold text-lg text-slate-900 dark:text-white">Pembayaran QRIS</h2>
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-neon/15 text-slate-800 dark:text-white border border-neon/40">Pembayaran demo</span>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-neutral-400 mt-1">Tanpa uang sungguhan. Gunakan simulator Midtrans, bukan aplikasi bank.</p>

                    @if (session('error')) <p class="mt-3 p-3 bg-red-50 border border-red-300 text-red-800 text-sm font-semibold rounded-xl">{{ session('error') }}</p> @endif
                    @if ($paymentError ?? session('paymentError')) <p class="mt-3 p-3 bg-red-50 border border-red-300 text-red-800 text-sm font-semibold rounded-xl" role="alert">{{ $paymentError ?? session('paymentError') }}</p> @endif
                    @php $payError = isset($errors) ? $errors->first('payment') : null; @endphp
                    @if ($payError) <p class="mt-3 p-3 bg-red-50 border border-red-300 text-red-800 text-sm font-semibold rounded-xl">{{ $payError }}</p> @endif

                    @if (!$payConfigured)
                        <p class="mt-4 p-4 bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 rounded-xl text-sm font-semibold text-slate-700 dark:text-neutral-300">Pembayaran demo belum diaktifkan. Hubungi pengelola.</p>
                    @else
                        <ol class="mt-4 text-sm text-slate-600 dark:text-neutral-400 space-y-1 list-decimal list-inside">
                            <li>Tekan <strong class="text-slate-900 dark:text-white">Buat kode QR</strong>.</li>
                            <li>Salin tautan QR, buka simulator, tempel tautan, lalu simulasikan pembayaran.</li>
                            <li>Tekan <strong class="text-slate-900 dark:text-white">Periksa status pembayaran</strong>.</li>
                        </ol>
                        <div class="mt-5 grid gap-6 md:grid-cols-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-500 dark:text-neutral-400">Total bayar</p>
                                <p class="font-display font-black text-2xl text-slate-900 dark:text-white mt-0.5">{{ $order->total > 0 ? 'Rp '.number_format($order->total, 0, ',', '.') : 'Gratis' }}</p>
                                <p class="text-sm text-slate-600 dark:text-neutral-400 mt-2">Status pembayaran: <strong class="text-slate-900 dark:text-white">{{ $payLabels[$pStatus] ?? ($pStatus ?? 'Menunggu QR') }}</strong></p>
                                @if ($pExpires)
                                    <p class="text-sm text-slate-600 dark:text-neutral-400 mt-1 flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <span id="pay-countdown" data-expires="{{ $expIso }}">Batas bayar: {{ $expText }}</span>
                                    </p>
                                @endif
                                <div class="mt-4 flex flex-wrap gap-2" x-data="{ busy: false }">
                                    @if (!$qrString && Route::has('payments.start'))
                                        <form method="POST" action="{{ route('payments.start', $order) }}" @submit="if (busy) { $event.preventDefault(); } else { busy = true; }">
                                            @csrf
                                            <button :disabled="busy" class="bg-neon text-deep font-bold text-sm px-5 py-2.5 rounded-xl hover:opacity-90 transition disabled:opacity-60">Buat kode QR</button>
                                        </form>
                                    @endif
                                    @if (Route::has('payments.status'))
                                        <button type="button" id="pay-check-btn" data-url="{{ route('payments.status', $order) }}" class="bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-sm px-5 py-2.5 rounded-xl hover:opacity-90 transition">Periksa status pembayaran</button>
                                    @endif
                                </div>
                                <p id="pay-msg" class="text-sm font-semibold text-slate-600 dark:text-neutral-400 mt-2" role="status"></p>
                            </div>
                            <div>
                                @if ($qrString)
                                    <div class="bg-white rounded-xl border border-slate-300 p-4 block w-full max-w-[272px] mx-auto text-center">
                                        <div class="pay-qr">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(240)->margin(2)->errorCorrection('M')->generate($qrString) !!}</div>
                                        <span class="text-xs font-bold text-slate-600 mt-2 block">Tempel tautan di simulator</span>
                                    </div>
                                    <style>.pay-qr svg { width: 100% !important; max-width: 240px; height: auto !important; aspect-ratio: 1 / 1; margin-inline: auto; display: block; }</style>
                                @endif
                                 @if ($qrUrl)
                                     <label for="payment-qr-link" class="mt-3 block text-xs font-semibold text-slate-600 dark:text-neutral-300">Tautan QR untuk simulator</label>
                                     <input id="payment-qr-link" type="text" readonly value="{{ $qrUrl }}" onclick="this.select()" class="mt-1 w-full min-w-0 rounded-lg border-slate-300 dark:border-line bg-white dark:bg-deep text-slate-900 dark:text-paper px-3 py-2 text-xs focus:border-neon focus:ring-neon">
                                     <div class="mt-3 flex flex-wrap gap-2">
                                        <button type="button" id="copy-qr-url" data-url="{{ $qrUrl }}" class="text-xs font-bold px-3 py-2 rounded-lg border border-slate-300 dark:border-line text-slate-700 dark:text-neutral-200 hover:bg-slate-50 dark:hover:bg-white/10 transition">Salin tautan QR</button>
                                        <a href="https://simulator.sandbox.midtrans.com/v2/qris/index" target="_blank" rel="noopener" class="text-xs font-bold px-3 py-2 rounded-lg border border-slate-300 dark:border-line text-slate-700 dark:text-neutral-200 hover:bg-slate-50 dark:hover:bg-white/10 transition">Buka simulator Midtrans</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <script>
                        (function () {
                            var token = document.querySelector('meta[name="csrf-token"]');
                            token = token ? token.getAttribute('content') : '';
                            var exp = document.getElementById('pay-countdown');
                            if (exp && exp.dataset.expires) {
                                var end = new Date(exp.dataset.expires).getTime();
                                if (!isNaN(end)) {
                                    var t = setInterval(function () {
                                        var left = end - Date.now();
                                        if (left <= 0) { clearInterval(t); exp.textContent = 'Batas waktu habis. Tekan Periksa status pembayaran.'; return; }
                                        var m = Math.floor(left / 60000), s = Math.floor((left % 60000) / 1000);
                                        exp.textContent = 'Sisa waktu bayar: ' + m + ' mnt ' + (s < 10 ? '0' : '') + s + ' dtk';
                                    }, 1000);
                                }
                            }
                            var btn = document.getElementById('pay-check-btn');
                            var msg = document.getElementById('pay-msg');
                            if (btn) btn.addEventListener('click', function () {
                                if (btn.dataset.busy === '1') return;
                                btn.dataset.busy = '1';
                                fetch(btn.dataset.url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
                                    .then(function (res) {
                                        if (res.status === 401 || res.status === 419) { msg.textContent = 'Sesi berakhir. Muat ulang halaman lalu coba lagi.'; return null; }
                                        return res.json();
                                    })
                                    .then(function (data) {
                                        if (!data) return;
                                        var ps = data.payment_status || data.status || '';
                                        if (ps === 'settlement' || ps === 'lunas' || ps === 'expire' || ps === 'cancel' || ps === 'deny' || ps === 'batal') { location.reload(); return; }
                                        msg.textContent = data.message || ('Status: ' + ps);
                                    })
                                    .catch(function () { msg.textContent = 'Tidak dapat memeriksa status. Coba lagi.'; })
                                    .finally(function () { btn.dataset.busy = ''; });
                            });
                            var copy = document.getElementById('copy-qr-url');
                            if (copy) copy.addEventListener('click', function () {
                                var u = copy.dataset.url || '';
                                function done(ok) { copy.textContent = ok ? 'Tautan disalin' : 'Coba salin lagi'; msg.textContent = ok ? 'Tautan QR disalin. Tempel di simulator Midtrans.' : 'Gagal menyalin otomatis. Pilih teks pada kolom tautan QR lalu salin manual.'; }
                                if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(u).then(function () { done(true); }, function () { done(false); }); }
                                else { try { var i = document.createElement('input'); i.value = u; document.body.appendChild(i); i.select(); var ok = document.execCommand('copy'); i.remove(); done(ok); } catch (e) { done(false); } }
                            });
                        })();
                        </script>
                    @endif
                </div>
                @else
                <div class="mt-8 border-t-2 border-slate-200 dark:border-white/10 pt-6">
                    <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200 mb-2">Instruksi Pembayaran</h2>
                    <p class="text-sm text-slate-600 dark:text-neutral-400 leading-relaxed font-medium">
                        Sejumlah <strong class="text-slate-900 dark:text-white font-black">Rp {{ number_format($order->total, 0, ',', '.') }}</strong> tercatat untuk pesanan ini.
                        <br><span class="inline-block mt-1 bg-slate-100 dark:bg-white/5 border border-slate-300 dark:border-line px-3 py-1.5 rounded-lg font-bold text-slate-900 dark:text-white">BCA 1234567890 a.n. EventKu — rekening demo, jangan transfer uang sungguhan</span>
                        <br><span class="text-xs text-slate-500 dark:text-neutral-400 mt-1 block">Alur demo: unggah tangkapan layar apa pun sebagai bukti untuk latihan verifikasi organizer di bawah ini:</span>
                    </p>

                    @if ($order->bukti_bayar)
                        <div class="mt-4 p-4 bg-emerald-50 border border-emerald-300 rounded-xl">
                            <p class="text-xs font-bold text-emerald-800 flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Bukti transfer sudah diunggah. Menunggu verifikasi dari organizer.
                            </p>
                            <img src="{{ route('orders.proof', $order) }}" alt="Bukti bayar" class="mt-3 max-h-64 rounded-xl border-2 border-slate-300 dark:border-line shadow-sm">
                        </div>
                    @endif

                    <form method="POST" action="{{ route('orders.bukti', $order) }}" enctype="multipart/form-data" class="mt-5 bg-slate-50 dark:bg-white/5 p-5 rounded-xl border border-slate-200 dark:border-white/10">
                        @csrf
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Unggah File Bukti</label>
                        <input type="file" name="bukti" accept="image/*" class="w-full text-sm text-slate-600 dark:text-neutral-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-neutral-800 file:cursor-pointer">
                        @error('bukti') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
                        <button class="mt-4 bg-ink text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-xl hover:bg-neutral-800 shadow-md transition">
                            {{ $order->bukti_bayar ? 'Kirim Ulang Bukti' : 'Kirim Bukti Pembayaran' }}
                        </button>
                    </form>
                </div>
                @endif
            @endif

            @if ($order->status === 'batal')
                <div class="mt-8 border-t-2 border-slate-200 dark:border-white/10 pt-6">
                    @if ($order->payment_status === 'settlement_review')
                    <h2 class="font-display font-bold text-lg text-slate-900 dark:text-white">Pembayaran perlu ditinjau</h2>
                    <p class="text-sm text-slate-600 dark:text-neutral-400 mt-1">Pembayaran tercatat setelah pesanan dibatalkan. Tiket belum diterbitkan. Hubungi pengelola melalui Bantuan dengan nomor pesanan ini; jangan membayar ulang.</p>
                    @else
                    <h2 class="font-display font-bold text-lg text-slate-900 dark:text-white">Pesanan dibatalkan</h2>
                    <p class="text-sm text-slate-600 dark:text-neutral-400 mt-1">Waktu pembayaran habis atau pesanan dibatalkan. Kode QR pembayaran ini sudah tidak berlaku.</p>
                    @if (Route::has('events.show') && $order->ticketType && $order->ticketType->event)
                        <a href="{{ route('events.show', $order->ticketType->event) }}" class="mt-3 inline-block bg-neon text-deep font-bold text-sm px-5 py-2.5 rounded-xl hover:opacity-90 transition">Lihat event dan pesan lagi</a>
                    @endif
                    @endif
                </div>
            @endif

            @if ($order->status === 'lunas')
                <div class="mt-8 border-2 border-slate-300 dark:border-line rounded-2xl overflow-hidden shadow-sm">
                    <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b-2 border-slate-300 dark:border-line flex flex-wrap gap-3 items-center justify-between">
                        <div>
                            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Tiket Kamu</h2>
                            <p class="text-xs font-semibold text-slate-500 dark:text-neutral-400 mt-0.5">{{ $eventEnded ? 'Event selesai. Riwayat tiket tetap dapat dilihat.' : 'Tunjukkan QR code ini kepada petugas saat check-in di lokasi.' }}</p>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-300 rounded-full">
                            {{ count($order->tickets) }} Tiket Terbit
                        </span>
                    </div>

                    <div class="p-6 bg-slate-50/50 dark:bg-white/5 space-y-4">
                        @foreach ($order->tickets as $ticket)
                            @php
                                $ticketExpired = $ticket->status === 'aktif' && $eventEnded;
                                $ticketLabel = $ticket->status === 'terpakai' ? 'Terpakai' : ($ticketExpired ? 'Kedaluwarsa' : ucfirst($ticket->status));
                            @endphp
                            <div class="bg-white dark:bg-card border-2 border-slate-300 dark:border-line rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-sm hover:border-lime-400 transition">
                                <div class="text-center sm:text-left">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-neutral-400 block">Kode Unik Tiket</span>
                                    <span class="font-mono font-black text-lg text-slate-900 dark:text-white tracking-widest block mt-0.5">{{ $ticket->kode }}</span>
                                    
                                    <div class="mt-2.5 flex items-center gap-2 justify-center sm:justify-start">
                                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full {{ $ticket->status === 'aktif' && !$ticketExpired ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-200 text-slate-700 border border-slate-300' }}">
                                            Status: {{ $ticketLabel }}
                                        </span>

                                        @if ($ticket->status === 'terpakai')
                                            <a href="{{ route('tickets.certificate', $ticket) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-lime-600 bg-lime-50 border border-lime-300 hover:bg-lime-100 px-3 py-1 rounded-lg transition shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                Lihat E-Sertifikat
                                            </a>
                                        @endif
                                    </div>
                                    @if ($ticket->status === 'terpakai' && $ticket->waktu_checkin)
                                        <p class="mt-2 text-xs text-slate-600 dark:text-neutral-400">Check-in: <time datetime="{{ $ticket->waktu_checkin->toIso8601String() }}">{{ $ticket->waktu_checkin->copy()->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y, H:i') }} WIB</time></p>
                                    @elseif ($ticketExpired)
                                        <p class="mt-2 text-xs text-slate-600 dark:text-neutral-400">Masa berlaku berakhir saat event selesai.</p>
                                    @endif
                                </div>

                                @if ($ticket->status === 'aktif' && !$ticketExpired)
                                <div class="p-3 bg-white border-2 border-slate-300 rounded-xl shadow-sm flex flex-col items-center" data-checkin-qr>
                                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(110)->generate($ticket->kode) !!}
                                    <span class="text-[10px] font-bold text-slate-600 dark:text-slate-600 uppercase tracking-wider mt-1.5">Scan Check-in</span>
                                </div>
                                @else
                                <p class="text-xs font-semibold text-slate-500 dark:text-neutral-400 text-center sm:text-right">{{ $ticketExpired ? 'QR check-in sudah tidak berlaku' : 'QR sudah digunakan' }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
