@php $e = $event ?? null; @endphp

<div class="space-y-4">
    <div>
        <label class="block text-sm font-medium">Judul</label>
        <input type="text" name="judul" value="{{ old('judul', $e?->judul) }}" class="w-full border rounded px-3 py-2">
        @error('judul') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Deskripsi</label>
        <textarea name="deskripsi" rows="5" class="w-full border rounded px-3 py-2">{{ old('deskripsi', $e?->deskripsi) }}</textarea>
        @error('deskripsi') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Kategori</label>
            <input type="text" name="kategori" value="{{ old('kategori', $e?->kategori) }}" placeholder="Teknologi, Bisnis, ..." class="w-full border rounded px-3 py-2">
            @error('kategori') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Tipe</label>
            <select name="tipe" class="w-full border rounded px-3 py-2">
                <option value="online" @selected(old('tipe', $e?->tipe) === 'online')>Online (Webinar)</option>
                <option value="offline" @selected(old('tipe', $e?->tipe) === 'offline')>Offline (Seminar)</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Lokasi atau Link Meeting</label>
        <input type="text" name="lokasi" value="{{ old('lokasi', $e?->lokasi) }}" class="w-full border rounded px-3 py-2">
        @error('lokasi') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium">Mulai</label>
            <input type="datetime-local" name="tanggal_mulai"
                   value="{{ old('tanggal_mulai', $e?->tanggal_mulai?->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 py-2">
            @error('tanggal_mulai') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium">Selesai</label>
            <input type="datetime-local" name="tanggal_selesai"
                   value="{{ old('tanggal_selesai', $e?->tanggal_selesai?->format('Y-m-d\TH:i')) }}" class="w-full border rounded px-3 py-2">
            @error('tanggal_selesai') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium">Poster (maks 2 MB)</label>
        <input type="file" name="poster" accept="image/*" class="w-full">
        @error('poster') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium">Status</label>
        <select name="status" class="w-full border rounded px-3 py-2">
            <option value="draft" @selected(old('status', $e?->status) === 'draft')>Draft</option>
            <option value="terbit" @selected(old('status', $e?->status) === 'terbit')>Terbit</option>
        </select>
    </div>

    <button class="bg-indigo-600 text-white px-5 py-2 rounded hover:bg-indigo-700">Simpan</button>
</div>