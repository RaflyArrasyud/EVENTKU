{{-- Grafik pendapatan: null-safe, empty-state bila data kosong. Warna tema hitam + #7FB069. --}}
<section class="mt-6 grid gap-6 lg:grid-cols-2">
    <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden lg:col-span-2">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Pemasukan 30 Hari</h2>
        </div>
        <div class="p-6">
            @if ($hasDaily)
                <div class="relative h-64"><canvas id="chart-daily"></canvas></div>
            @else
                <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-10 text-center">Belum ada pemasukan 30 hari terakhir.</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Tiket per Event (Top 8)</h2>
        </div>
        <div class="p-6">
            @if ($hasPerEvent)
                <div class="relative h-64"><canvas id="chart-perevent"></canvas></div>
            @else
                <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-10 text-center">Belum ada penjualan per event.</p>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-card rounded-2xl border-2 border-slate-300 dark:border-line shadow-md overflow-hidden">
        <div class="bg-slate-100/80 dark:bg-white/5 px-6 py-4 border-b border-slate-300 dark:border-line">
            <h2 class="font-black text-base uppercase tracking-wider text-slate-800 dark:text-neutral-200">Status Pesanan</h2>
        </div>
        <div class="p-6">
            @if ($hasStatus)
                <div class="relative h-64"><canvas id="chart-status"></canvas></div>
            @else
                <p class="text-sm font-medium text-slate-500 dark:text-neutral-400 py-10 text-center">Belum ada data status pesanan.</p>
            @endif
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        if (typeof Chart === 'undefined') return;
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
        Chart.defaults.color = '#64748b';
        const NEON = '#7FB069', INK = '#111111';

        const daily = @json($revDaily);
        if (document.getElementById('chart-daily') && daily.length) {
            const ctx = document.getElementById('chart-daily').getContext('2d');
            const grad = ctx.createLinearGradient(0, 0, 0, 256);
            grad.addColorStop(0, 'rgba(127,176,105,.35)');
            grad.addColorStop(1, 'rgba(127,176,105,0)');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: daily.map(d => d.label),
                    datasets: [{ label: 'Pemasukan (Rp)', data: daily.map(d => d.total), borderColor: NEON, backgroundColor: grad, fill: true, tension: .35, pointRadius: 0, pointHitRadius: 12, borderWidth: 2.5 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' Rp ' + Number(c.parsed.y).toLocaleString('id-ID') } } },
                    scales: { x: { ticks: { maxTicksLimit: 8, maxRotation: 0 } }, y: { beginAtZero: true, ticks: { callback: v => v >= 1e6 ? (v / 1e6) + ' jt' : (v >= 1e3 ? (v / 1e3) + ' rb' : v) } } }
                }
            });
        }

        const perEvent = @json($revPerEvent->map(fn ($r) => ['judul' => $r->judul, 'terjual' => (int) $r->terjual])->values());
        if (document.getElementById('chart-perevent') && perEvent.length) {
            new Chart(document.getElementById('chart-perevent'), {
                type: 'bar',
                data: {
                    labels: perEvent.map(r => r.judul.length > 22 ? r.judul.slice(0, 22) + '…' : r.judul),
                    datasets: [{ label: 'Tiket terjual', data: perEvent.map(r => r.terjual), backgroundColor: NEON, borderRadius: 8, maxBarThickness: 36 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }

        const status = @json([(int) ($revStatus['lunas'] ?? 0), (int) ($revStatus['menunggu'] ?? 0), (int) ($revStatus['batal'] ?? 0)]);
        if (document.getElementById('chart-status') && status.some(v => v > 0)) {
            new Chart(document.getElementById('chart-status'), {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Menunggu', 'Batal'],
                    datasets: [{ data: status, backgroundColor: [NEON, '#f59e0b', '#ef4444'], borderColor: '#fff', borderWidth: 3 }]
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { weight: 'bold' } } } } }
            });
        }
    })();
</script>