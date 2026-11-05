<?php

namespace App\Http\Controllers;

use App\Models\OrganizerRequest;
use Illuminate\Http\Request;

class OrganizerRequestController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'alasan' => 'nullable|string|max:1000',
            'kontak' => 'nullable|string|max:255',
        ]);

        $existing = OrganizerRequest::where('user_id', auth()->id())->first();

        if ($existing && $existing->status === 'diajukan') {
            return back()->with('error', 'Pengajuanmu masih menunggu verifikasi admin.');
        }

        if ($existing && $existing->status === 'disetujui') {
            return back()->with('error', 'Pengajuanmu sudah disetujui.');
        }

        OrganizerRequest::updateOrCreate(
            ['user_id' => auth()->id()],
            ['alasan' => $request->alasan, 'kontak' => $request->kontak, 'status' => 'diajukan']
        );

        return back()->with('success', 'Pengajuan organizer terkirim, tunggu verifikasi admin.');
    }
}
