<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with('ticketTypes')
            ->where('status', 'terbit')
            ->where('tanggal_mulai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->take(6)
            ->get();

        $stats = [
            'event' => Event::where('status', 'terbit')->count(),
            'organizer' => User::where('role', 'organizer')->count(),
            'tiket' => Ticket::count(),
        ];

        return view('home', compact('events', 'stats'));
    }
}