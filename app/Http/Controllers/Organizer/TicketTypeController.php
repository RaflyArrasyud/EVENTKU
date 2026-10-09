<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketTypeController extends Controller
{
    public function index(Event $event)
    {
        $this->authorizeOwner($event);

        $ticketTypes = $event->ticketTypes()
            ->withSum(['orders as terjual' => fn ($q) => $q->where('status', '!=', 'batal')], 'jumlah')
            ->get();

        return view('organizer.ticket-types.index', compact('event', 'ticketTypes'));
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeOwner($event);

        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'harga' => 'required|integer|min:0',
            'kuota' => 'required|integer|min:1',
        ]);

        $event->ticketTypes()->create($data);

        return back()->with('success', 'Jenis tiket ditambahkan.');
    }

    public function destroy(Event $event, TicketType $ticketType)
    {
        $this->authorizeOwner($event);
        abort_if($ticketType->event_id !== $event->id, 404);

        if ($ticketType->orders()->exists()) {
            return back()->withErrors(['nama' => 'Tiket ini sudah punya pesanan dan tidak bisa dihapus.']);
        }

        $ticketType->delete();

        return back()->with('success', 'Jenis tiket dihapus.');
    }

    private function authorizeOwner(Event $event): void
    {
        abort_if($event->organizer_id !== auth()->id(), 403);
    }
}