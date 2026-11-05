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
            ->get();

        $stats = [
            'event' => Event::where('status', 'terbit')->count(),
            'organizer' => User::where('role', 'organizer')->count(),
            'tiket' => Ticket::count(),
        ];

        $speakers = Event::where('status', 'terbit')
            ->whereNotNull('pembicara')
            ->where('pembicara', '!=', '')
            ->orderBy('tanggal_mulai')
            ->get(['pembicara', 'peran_pembicara', 'foto_pembicara'])
            ->unique(fn ($e) => mb_strtolower(trim($e->pembicara)))
            ->values()
            ->map(fn ($e) => [
                'nama' => $e->pembicara,
                'peran' => $e->peran_pembicara,
                'foto' => $e->foto_pembicara ? asset('storage/'.$e->foto_pembicara) : null,
            ])
            ->all();

        $liveOnline = Event::where('status', 'terbit')
            ->where('tipe', 'online')
            ->where('tanggal_mulai', '<=', now())
            ->where('tanggal_selesai', '>=', now())
            ->orderBy('tanggal_mulai')
            ->get(['id', 'judul', 'tanggal_mulai', 'tanggal_selesai']);

        $nextOnline = Event::where('status', 'terbit')
            ->where('tipe', 'online')
            ->where('tanggal_mulai', '>', now())
            ->orderBy('tanggal_mulai')
            ->first(['id', 'judul', 'tanggal_mulai', 'tanggal_selesai']);

        return view('home', compact('events', 'stats', 'speakers', 'liveOnline', 'nextOnline'));
    }
}