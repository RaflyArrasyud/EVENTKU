@php $e = $event ?? null; @endphp

<div class="space-y-6">
    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Judul Event</label>
        <input type="text" name="judul" value="{{ old('judul', $e?->judul) }}" placeholder="Contoh: Workshop UI/UX Modern 2026"
               class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
        @error('judul') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Deskripsi Lengkap</label>
        <textarea name="deskripsi" rows="5" placeholder="Tuliskan latar belakang, agenda, pembicara, dan keuntungan mengikuti event..."
                  class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-medium focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">{{ old('deskripsi', $e?->deskripsi) }}</textarea>
        @error('deskripsi') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Kategori</label>
            <input type="text" name="kategori" value="{{ old('kategori', $e?->kategori) }}" placeholder="Teknologi, Bisnis, Desain..."
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            @error('kategori') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Tipe Penyelenggaraan</label>
            <select name="tipe" class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
                <option value="online" @selected(old('tipe', $e?->tipe) === 'online')>Online (Webinar)</option>
                <option value="offline" @selected(old('tipe', $e?->tipe) === 'offline')>Offline (Seminar)</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Lokasi Fisik atau Link Meeting Virtual</label>
        <input type="text" name="lokasi" value="{{ old('lokasi', $e?->lokasi) }}" placeholder="Contoh: Gedung Serbaguna Jakarta / Google Meet / Zoom"
               class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
        @error('lokasi') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Waktu Mulai</label>
            <input type="datetime-local" name="tanggal_mulai"
                   value="{{ old('tanggal_mulai', $e?->tanggal_mulai?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            @error('tanggal_mulai') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Waktu Selesai</label>
            <input type="datetime-local" name="tanggal_selesai"
                   value="{{ old('tanggal_selesai', $e?->tanggal_selesai?->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            @error('tanggal_selesai') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Poster Event (Maksimal 2 MB)</label>
        <input type="file" name="poster" accept="image/*"
               class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-slate-50 dark:bg-white/5 px-4 py-2.5 text-slate-700 dark:text-neutral-300 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-neutral-800 file:cursor-pointer transition">
        @error('poster') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Logo Sponsor (Opsional, Maksimal 2 MB)</label>
        <input type="file" name="logo_sponsor" accept="image/*"
               class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-slate-50 dark:bg-white/5 px-4 py-2.5 text-slate-700 dark:text-neutral-300 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-neutral-800 file:cursor-pointer transition">
        @error('logo_sponsor') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-6">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Nama Pembicara (Opsional)</label>
            <input type="text" name="pembicara" value="{{ old('pembicara', $e?->pembicara) }}" placeholder="Contoh: Dr. Budi Santoso"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            @error('pembicara') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Peran / Jabatan Pembicara (Opsional)</label>
            <input type="text" name="peran_pembicara" value="{{ old('peran_pembicara', $e?->peran_pembicara) }}" placeholder="Contoh: CTO Startup X"
                   class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            @error('peran_pembicara') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Foto Pembicara (Opsional, Maksimal 2 MB)</label>
        <input type="file" name="foto_pembicara" accept="image/*"
               class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-slate-50 dark:bg-white/5 px-4 py-2.5 text-slate-700 dark:text-neutral-300 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-neutral-800 file:cursor-pointer transition">
        @error('foto_pembicara') <p class="text-red-600 text-xs font-bold mt-1.5">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-neutral-300 mb-2">Status Publikasi</label>
        <select name="status" class="w-full rounded-xl border-2 border-slate-300 dark:border-line bg-white dark:bg-card px-4 py-3 text-slate-900 dark:text-white font-semibold focus:border-black dark:focus:border-neon focus:ring-0 shadow-sm transition">
            <option value="draft" @selected(old('status', $e?->status) === 'draft')>Draft (Belum Ditampilkan ke Publik)</option>
            <option value="terbit" @selected(old('status', $e?->status) === 'terbit')>Terbit (Dapat Dilihat & Dipesan)</option>
        </select>
    </div>

    <div class="pt-4 border-t border-slate-200 dark:border-white/10 flex items-center justify-end gap-3">
        <a href="{{ route('organizer.events.index') }}" class="px-5 py-3 rounded-xl border-2 border-slate-300 dark:border-line text-slate-700 dark:text-neutral-300 font-bold text-sm hover:bg-slate-100 dark:hover:bg-white/10 transition">Batal</a>
        <button type="submit" class="bg-ink text-white font-bold text-sm px-7 py-3 rounded-xl hover:bg-neutral-800 shadow-md transition">Simpan Event</button>
    </div>
</div>