<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    public function index()
    {
        return view('organizer.checkin');
    }

    public function store(Request $request)
    {
        $request->validate(['kode' => 'required|string']);

        $ticket = Ticket::with('order.ticketType.event')
            ->where('kode', strtoupper(trim($request->kode)))
            ->first();

        if (! $ticket || $ticket->order->ticketType->event->organizer_id !== auth()->id()) {
            return back()->with('error', 'Kode tiket tidak ditemukan.');
        }

        if ($ticket->status !== 'aktif') {
            return back()->with('error', 'Tiket ini sudah dipakai.');
        }

        $ticket->update(['status' => 'terpakai']);

        return back()->with('success', 'Check-in berhasil: '.$ticket->order->ticketType->event->judul
            .' ('.$ticket->order->ticketType->nama.')');
    }
}