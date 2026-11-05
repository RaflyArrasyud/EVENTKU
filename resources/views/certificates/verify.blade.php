<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Sertifikat - EventKu</title>
    @vite(['resources/css/app.css'])
</head>
<body style="margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #0A0A0A; font-family: ui-sans-serif, system-ui, sans-serif; padding: 24px; box-sizing: border-box;">
    <div style="width: 100%; max-width: 520px; background: #ffffff; border: 3px solid #7FB069; padding: 40px 36px; text-align: center; box-sizing: border-box;">
        <div style="font-size: 22px; font-weight: 800; color: #0A0A0A;">EventKu<span style="color: #7FB069;">.</span></div>
        <div style="font-size: 11px; font-weight: 700; letter-spacing: 3px; color: #3F3F46; margin-top: 4px;">VERIFIKASI SERTIFIKAT</div>

        <div style="display: inline-block; margin: 20px 0 8px; font-size: 13px; font-weight: 800; padding: 8px 20px; border: 2px solid {{ $valid ? '#7FB069' : '#0A0A0A' }}; background: {{ $valid ? '#7FB069' : '#ffffff' }}; color: {{ $valid ? '#ffffff' : '#0A0A0A' }};">
            {{ $valid ? 'SERTIFIKAT VALID' : 'BELUM VALID — TIKET BELUM CHECK-IN' }}
        </div>

        <div style="font-size: 26px; font-weight: 700; color: #0A0A0A; margin-top: 12px;">{{ $nama }}</div>
        <div style="width: 120px; height: 4px; background: #7FB069; margin: 10px auto;"></div>
        <div style="font-size: 15px; font-weight: 700; color: #0A0A0A;">&ldquo;{{ $judul }}&rdquo;</div>
        <div style="font-size: 13px; color: #3F3F46; margin-top: 6px;">{{ $tanggal ? $tanggal->format('d M Y') : '-' }}</div>

        <a href="{{ route('home') }}" style="display: inline-block; margin-top: 24px; font-size: 13px; font-weight: 700; color: #0A0A0A; text-decoration: none; border-bottom: 2px solid #7FB069;">&larr; Kembali ke Beranda</a>
    </div>
</body>
</html>
