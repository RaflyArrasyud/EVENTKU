<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpTicket;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        $tickets = HelpTicket::with(['user', 'messages.user'])
            ->where('status', 'terbuka')
            ->latest()
            ->paginate(15);

        return view('admin.help.index', compact('tickets'));
    }

    public function reply(Request $request, HelpTicket $ticket)
    {
        $request->validate(['isi' => 'required|string|max:2000']);
        abort_if($ticket->status !== 'terbuka', 400, 'Tiket sudah ditutup.');

        $ticket->messages()->create(['user_id' => auth()->id(), 'isi' => $request->isi]);

        return back()->with('success', 'Balasan terkirim.');
    }

    public function close(HelpTicket $ticket)
    {
        $ticket->update(['status' => 'ditutup']);

        return back()->with('success', 'Tiket ditutup.');
    }
}
