<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $requests = \App\Models\OrganizerRequest::with('user')->where('status', 'diajukan')->latest()->get();
        $users = User::latest()->paginate(15);

        return view('admin.users.index', compact('users', 'requests'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:peserta,organizer,admin']);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa mengubah role akun sendiri.');
        }

        if ($user->role === 'admin' && $request->role !== 'admin'
            && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Tidak bisa mencabut admin terakhir.');
        }

        $user->update(['role' => $request->role]);

        return back()->with('success', 'Role '.$user->name.' diubah menjadi '.$user->role.'.');
    }

    public function approveRequest(\App\Models\OrganizerRequest $request)
    {
        abort_if($request->status !== 'diajukan', 400, 'Pengajuan sudah diproses.');

        $request->update(['status' => 'disetujui']);
        $request->user()->update(['role' => 'organizer']);

        return back()->with('success', 'Pengajuan '.$request->user->name.' disetujui, role menjadi organizer.');
    }

    public function rejectRequest(\App\Models\OrganizerRequest $request)
    {
        abort_if($request->status !== 'diajukan', 400, 'Pengajuan sudah diproses.');

        $request->update(['status' => 'ditolak']);

        return back()->with('success', 'Pengajuan '.$request->user->name.' ditolak.');
    }
}
