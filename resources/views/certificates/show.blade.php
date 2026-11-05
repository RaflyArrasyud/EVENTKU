<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sertifikat - {{ $ticket->order->ticketType->event->judul }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 0; }
        @media print {
            body { background: #ffffff !important; padding: 0 !important; margin: 0 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print { display: none !important; }
            .cert-page { margin: 0 !important; width: 100vw !important; height: 100vh !important; max-width: none !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 24px; background: #e8e4d8; font-family: 'Poppins', Arial, Helvetica, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact;">

    @php
        $event = $ticket->order->ticketType->event;
        $user = $ticket->order->user;
        $nomor_sertifikat = 'CERT/' . $event->tanggal_mulai->format('Y/m') . '/' . $ticket->kode;
        $peran = 'Peserta';
        $jenis_event = ucfirst($event->tipe) . ' ' . $event->kategori;
        $penyelenggara = $event->organizer?->name ?? 'EventKu';
        $kota = $event->lokasi ?? '-';
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tanggal_terbit = now()->format('d') . ' ' . $bulan[(int) now()->format('n')] . ' ' . now()->format('Y');
        $url_verifikasi = $url_verifikasi ?? route('sertifikat.verifikasi', $ticket->kode);
        $tgl_event = $event->tanggal_mulai ? $event->tanggal_mulai->format('d') . ' ' . $bulan[(int) $event->tanggal_mulai->format('n')] . ' ' . $event->tanggal_mulai->format('Y') : '-';
    @endphp

    <div class="no-print" style="max-width: 1120px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: space-between;">
        <a href="javascript:history.back()" style="display: inline-block; font-size: 14px; font-weight: 700; color: #0A0A0A; background: #ffffff; padding: 10px 18px; border: 2px solid #0A0A0A; text-decoration: none;">&larr; Kembali</a>
        <button onclick="window.print()" style="font-size: 14px; font-weight: 700; color: #ffffff; background: #0A0A0A; padding: 10px 24px; border: none; cursor: pointer;">Cetak / Simpan PDF</button>
    </div>

    <div class="cert-page" style="position: relative; width: 100%; max-width: 1120px; aspect-ratio: 297/210; margin: 0 auto; background: #FAFAF7; border: 2px solid #0A0A0A; box-sizing: border-box; overflow: hidden;">
        <div style="position: absolute; top: 8mm; bottom: 8mm; left: 8mm; right: 8mm; border: 2px solid #0A0A0A; pointer-events: none;"></div>

        {{-- Dekorasi flat 4 pojok --}}
        <svg style="position: absolute; top: 8mm; left: 8mm; width: 90px; height: 90px; z-index: 0; pointer-events: none;" viewBox="0 0 90 90"><path d="M0 0 H90 V22 A68 68 0 0 0 22 90 H0 Z" fill="#C6F432"/></svg>
        <svg style="position: absolute; top: 8mm; right: 8mm; width: 110px; height: 90px; z-index: 0; pointer-events: none;" viewBox="0 0 110 90"><circle cx="88" cy="18" r="10" fill="#0A0A0A"/><g fill="#A3D91C"><circle cx="14" cy="14" r="3"/><circle cx="28" cy="14" r="3"/><circle cx="42" cy="14" r="3"/><circle cx="56" cy="14" r="3"/><circle cx="14" cy="28" r="3"/><circle cx="28" cy="28" r="3"/><circle cx="42" cy="28" r="3"/><circle cx="56" cy="28" r="3"/><circle cx="14" cy="42" r="3"/><circle cx="28" cy="42" r="3"/><circle cx="42" cy="42" r="3"/><circle cx="56" cy="42" r="3"/><circle cx="14" cy="56" r="3"/><circle cx="28" cy="56" r="3"/><circle cx="42" cy="56" r="3"/><circle cx="56" cy="56" r="3"/></g></svg>
        <svg style="position: absolute; bottom: 8mm; left: 8mm; width: 90px; height: 90px; z-index: 0; pointer-events: none;" viewBox="0 0 90 90"><path d="M45 12 V78 M12 45 H78" stroke="#0A0A0A" stroke-width="10"/><circle cx="45" cy="45" r="9" fill="#C6F432"/></svg>
        <svg style="position: absolute; bottom: 8mm; right: 8mm; width: 90px; height: 90px; z-index: 0; pointer-events: none;" viewBox="0 0 90 90"><path d="M90 90 L90 20 L20 90 Z" fill="#A3D91C"/><circle cx="30" cy="62" r="8" fill="#0A0A0A"/></svg>

        <div style="position: relative; z-index: 1; height: 100%; padding: 34px 70px 30px; box-sizing: border-box; display: flex; flex-direction: column;">

            {{-- Header --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                <div style="font-size: 24px; font-weight: 800; color: #0A0A0A; letter-spacing: -0.5px;">EventKu<span style="color: #A3D91C;">.</span></div>
                <div style="font-size: 11px; font-weight: 600; letter-spacing: 2px; color: #3F3F46; border: 1.5px solid #0A0A0A; padding: 6px 14px; margin-top: 92px;">{{ $penyelenggara }}</div>
            </div>

            {{-- Judul --}}
            <div style="text-align: center; margin-top: 10px;">
                <div style="font-size: 44px; font-weight: 800; letter-spacing: 14px; color: #0A0A0A; text-indent: 14px;">SERTIFIKAT</div>
                <div style="font-size: 12px; font-weight: 600; letter-spacing: 3px; color: #3F3F46; margin-top: 6px;">Nomor: {{ $nomor_sertifikat }}</div>
            </div>

            {{-- Nama --}}
            <div style="text-align: center; margin: 0; padding: 4px 0; min-height: 0; overflow: hidden; flex: 1; display: flex; flex-direction: column; justify-content: center;">
                <p style="font-size: 12px; letter-spacing: 4px; color: #3F3F46; margin: 0 0 4px;">DIBERIKAN KEPADA</p>
                <div style="font-size: 32px; font-weight: 600; color: #0A0A0A; line-height: 1.2; overflow-wrap: break-word; word-break: break-word; max-width: 100%; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $user->name }}</div>
                <div style="width: 280px; max-width: 60%; height: 5px; background: #C6F432; margin: 6px auto 8px;"></div>
                <div style="font-size: 15px; font-weight: 700; color: #0A0A0A;">Sebagai: {{ $peran }}</div>
                <div style="font-size: 13px; color: #3F3F46; margin-top: 6px; line-height: 1.6; overflow-wrap: break-word; word-break: break-word; max-width: 640px; margin-left: auto; margin-right: auto; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;">
                    {{ $jenis_event }}<br>
                    <strong style="color: #0A0A0A;">&ldquo;{{ $event->judul }}&rdquo;</strong><br>
                    Diselenggarakan oleh {{ $penyelenggara }}<br>
                    {{ $tgl_event }}
                </div>
            </div>

            {{-- Footer 3 kolom --}}
            <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;">
                <div style="text-align: center; min-width: 170px;">
                    <div style="font-size: 12px; font-weight: 600; color: #0A0A0A; margin-bottom: 36px;">{{ $penyelenggara }}</div>
                    <div style="border-top: 2px solid #0A0A0A; padding-top: 4px; font-size: 12px; font-weight: 700; color: #0A0A0A;">Ketua Panitia</div>
                </div>
                <div style="text-align: center;">
                    @if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
                        <div style="width: 132px; height: 132px; background: #ffffff; padding: 6px; box-sizing: border-box; margin: 0 auto; overflow: hidden;">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->margin(1)->errorCorrection('M')->generate($url_verifikasi) !!}</div>
                    @else
                        <div style="width: 132px; height: 132px; border: 2px solid #0A0A0A; background: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #0A0A0A; text-align: center; padding: 8px; box-sizing: border-box; margin: 0 auto;">{{ $ticket->kode }}</div>
                    @endif
                    <div style="font-size: 10px; color: #3F3F46; margin-top: 4px;">Pindai untuk verifikasi</div>
                </div>
                <div style="text-align: center; min-width: 170px;">
                    <div style="font-size: 12px; color: #3F3F46; margin-bottom: 36px;">{{ $kota }}, {{ $tanggal_terbit }}</div>
                    <div style="border-top: 2px solid #0A0A0A; padding-top: 4px; font-size: 12px; font-weight: 700; color: #0A0A0A;">Penyelenggara</div>
                </div>
            </div>

            <div style="font-size: 9px; color: #3F3F46; margin-top: 8px; text-align: left; overflow-wrap: break-word;">
                Kode: {{ $ticket->kode }} &nbsp;|&nbsp; {{ $url_verifikasi }}
            </div>

        </div>
    </div>

</body>
</html>
