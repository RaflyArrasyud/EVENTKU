<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800">
    <div class="min-h-screen grid lg:grid-cols-2">

        {{-- Panel kiri: branding (hanya tampil di layar lebar) --}}
        <div class="hidden lg:flex relative overflow-hidden flex-col justify-between p-12 text-white bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700">
            <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-32 -left-16 h-96 w-96 rounded-full bg-fuchsia-400/20 blur-3xl"></div>

            <a href="{{ route('home') }}" class="relative text-2xl font-extrabold">🎟️ {{ config('app.name') }}</a>

            <div class="relative">
                <h2 class="text-4xl font-extrabold leading-tight">
                    Temukan dan ikuti event<br>favoritmu dengan mudah
                </h2>
                <p class="mt-4 text-white/80 max-w-md">
                    Beli tiket, dapatkan QR code, dan check-in cepat di pintu masuk.
                </p>
                <ul class="mt-8 space-y-3 text-sm">
                    <li class="flex items-center gap-3"><span class="h-8 w-8 rounded-full bg-white/15 flex items-center justify-center">🎫</span> Tiket digital langsung tersedia</li>
                    <li class="flex items-center gap-3"><span class="h-8 w-8 rounded-full bg-white/15 flex items-center justify-center">📱</span> QR check-in cepat dan aman</li>
                    <li class="flex items-center gap-3"><span class="h-8 w-8 rounded-full bg-white/15 flex items-center justify-center">📊</span> Dashboard untuk organizer</li>
                </ul>
            </div>

            <p class="relative text-xs text-white/60">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
        </div>

        {{-- Panel kanan: form --}}
        <div class="flex flex-col justify-center bg-slate-50 px-4 py-10 sm:px-12">
            <a href="{{ route('home') }}" class="lg:hidden mb-6 text-center text-2xl font-extrabold text-indigo-600">🎟️ {{ config('app.name') }}</a>

            <div class="w-full max-w-md mx-auto bg-white rounded-3xl shadow-xl border border-slate-100 p-8">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-center text-sm text-slate-500 hover:text-indigo-600 transition">&larr; Kembali ke beranda</a>
        </div>
    </div>
</body>
</html>