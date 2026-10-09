<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isOrganizer()) {
            $eventIds = $user->events()->pluck('id');
            $ticketTypeIds = TicketType::whereIn('event_id', $eventIds)->pluck('id');
            $orders = fn () => Order::whereIn('ticket_type_id', $ticketTypeIds);

            $stats = [
                'event_aktif' => $user->events()->where('status', 'terbit')->where('tanggal_selesai', '>=', now())->count(),
                'terjual' => (int) $orders()->where('status', 'lunas')->sum('jumlah'),
                'pendapatan' => (int) $orders()->where('status', 'lunas')->sum('total'),
                'menunggu' => $orders()->where('status', 'menunggu')->count(),
                'kuota' => (int) TicketType::whereIn('event_id', $eventIds)->sum('kuota'),
            ];

            $recentOrders = $orders()->with('user', 'ticketType.event')->latest()->take(5)->get();
            $myEvents = $user->events()->latest()->take(3)->get();

            return view('dashboard', compact('user', 'stats', 'recentOrders', 'myEvents'));
        }

        // Peserta (dan admin sementara)
        $orders = $user->orders()->with('ticketType.event')->latest()->get();

        $activeTickets = Ticket::with('order.ticketType.event')
            ->whereHas('order', fn ($q) => $q->where('user_id', $user->id)->where('status', 'lunas'))
            ->where('status', 'aktif')
            ->latest()
            ->get();

        $stats = [
            'tiket_aktif' => $activeTickets->count(),
            'event_terdaftar' => $orders->where('status', '!=', 'batal')->pluck('ticketType.event_id')->unique()->count(),
            'total' => (int) $orders->where('status', 'lunas')->sum('total'),
        ];

        $featured = $activeTickets->first();
        $recentOrders = $orders->take(3);

        return view('dashboard', compact('user', 'stats', 'featured', 'recentOrders'));
    }
}