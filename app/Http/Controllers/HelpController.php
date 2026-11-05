<?php

namespace App\Http\Controllers;

use App\Models\HelpMessage;
use App\Models\HelpTicket;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'topik' => 'required|string|max:255',
            'isi' => 'required|string|max:2000',
        ]);

        $ticket = HelpTicket::create([
            'user_id' => auth()->id(),
            'topik' => $request->topik,
            'status' => 'terbuka',
        ]);

        $msg = $ticket->messages()->create([
            'user_id' => auth()->id(),
            'isi' => $request->isi,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ticket_id' => $ticket->id, 'message' => $this->format($msg)], 201);
        }

        return back()->with('success', 'Pesan bantuan terkirim.');
    }

    public function messages(Request $request)
    {
        $since = (int) $request->query('since_id', 0);

        $msgs = HelpMessage::with('user')
            ->whereHas('ticket', fn ($q) => $q->where('user_id', auth()->id()))
            ->when($since > 0, fn ($q) => $q->where('id', '>', $since))
            ->orderBy('id')
            ->take(100)
            ->get()
            ->map(fn ($m) => $this->format($m));

        HelpMessage::whereNull('read_at')
            ->whereHas('ticket', fn ($q) => $q->where('user_id', auth()->id()))
            ->whereHas('user', fn ($q) => $q->where('role', 'admin'))
            ->update(['read_at' => now()]);

        return response()->json($msgs);
    }

    private function format(HelpMessage $m): array
    {
        return [
            'id' => $m->id,
            'isi' => $m->isi,
            'dari_admin' => (bool) ($m->user && $m->user->role === 'admin'),
            'dibaca' => $m->read_at !== null,
            'created' => $m->created_at?->toDateTimeString(),
        ];
    }
}
