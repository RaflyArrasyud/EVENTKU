<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class CertificateController extends Controller
{
    public function show(Ticket $ticket)
    {
        $this->authorizeOwner($ticket);
        $ticket->load('order.user', 'order.ticketType.event');
        abort_if($ticket->status !== 'terpakai', 403);

        return view('certificates.show', compact('ticket'));
    }

    public function verify(string $kode)
    {
        $ticket = Ticket::where('kode', $kode)->firstOrFail();
        $ticket->load('order.user', 'order.ticketType.event');

        return view('certificates.verify', [
            'nama' => $ticket->order->user->name,
            'judul' => $ticket->order->ticketType->event->judul,
            'tanggal' => $ticket->order->ticketType->event->tanggal_mulai,
            'valid' => $ticket->status === 'terpakai',
        ]);
    }

    private function authorizeOwner(Ticket $ticket): void
    {
        abort_if($ticket->order->user_id !== auth()->id(), 403);
    }
}
