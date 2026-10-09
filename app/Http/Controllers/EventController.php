<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with('ticketTypes')->where('status', 'terbit')
            ->where('tanggal_mulai', '>=', now())
            ->when($request->q, fn ($query, $q) => $query->where('judul', 'like', "%{$q}%"))
            ->when($request->tipe, fn ($query, $t) => $query->where('tipe', $t))
            ->orderBy('tanggal_mulai')
            ->paginate(9)
            ->withQueryString();

        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        abort_if($event->status !== 'terbit', 404);

        $event->load('organizer', 'ticketTypes');

        return view('events.show', compact('event'));
    }
}