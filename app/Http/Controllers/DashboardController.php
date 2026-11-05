<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\DB;

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

            $revenue = $this->revenueData($ticketTypeIds);

            return view('dashboard', array_merge(
                compact('user', 'stats', 'recentOrders', 'myEvents'), $revenue
            ));
        }

        if ($user->isAdmin()) {
            // Basis tampilan sama seperti peserta (kontrak view tetap), plus data revenue global.
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

            $revenue = $this->revenueData();
            $revenue['topOrganizers'] = \App\Models\User::where('role', 'organizer')
                ->select('users.id', 'users.name', DB::raw('COALESCE(SUM(CASE WHEN orders.status = "lunas" THEN orders.total ELSE 0 END), 0) as pendapatan'))
                ->leftJoin('events', 'events.organizer_id', '=', 'users.id')
                ->leftJoin('ticket_types', 'ticket_types.event_id', '=', 'events.id')
                ->leftJoin('orders', 'orders.ticket_type_id', '=', 'ticket_types.id')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('pendapatan')
                ->take(5)
                ->get();

            return view('dashboard', array_merge(
                compact('user', 'stats', 'featured', 'recentOrders'), $revenue
            ));
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
        $organizerRequest = \App\Models\OrganizerRequest::where('user_id', $user->id)->first();

        if ($organizerRequest && $organizerRequest->status !== 'diajukan' && ! $organizerRequest->seen_at) {
            $organizerRequest->update(['seen_at' => now()]);
        }

        return view('dashboard', compact('user', 'stats', 'featured', 'recentOrders', 'organizerRequest'));
    }

    private function revenueData($ticketTypeIds = null): array
    {
        $scoped = fn ($q) => $ticketTypeIds === null
            ? $q
            : $q->whereIn('orders.ticket_type_id', $ticketTypeIds instanceof \Illuminate\Support\Collection ? $ticketTypeIds->all() : $ticketTypeIds);

        $revStats = [
            'pendapatan' => (int) Order::query()->tap($scoped)->where('status', 'lunas')->sum('total'),
            'terjual' => (int) Order::query()->tap($scoped)->where('status', '!=', 'batal')->sum('jumlah'),
            'menunggu' => (int) Order::query()->tap($scoped)->where('status', 'menunggu')->count(),
            'event' => $ticketTypeIds === null
                ? \App\Models\Event::count()
                : \App\Models\Event::whereIn('id', TicketType::whereIn('id', $ticketTypeIds)->pluck('event_id'))->count(),
        ];

        $daily = Order::query()->tap($scoped)
            ->select(DB::raw('DATE(created_at) as hari'), DB::raw('SUM(total) as total'))
            ->where('status', 'lunas')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->groupBy('hari')
            ->orderBy('hari')
            ->pluck('total', 'hari');

        $revDaily = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i)->format('Y-m-d');
            $revDaily[] = ['label' => $day, 'total' => (int) ($daily[$day] ?? 0)];
        }

        $revPerEvent = Order::query()->tap($scoped)
            ->select('events.judul', DB::raw('SUM(orders.jumlah) as terjual'))
            ->join('ticket_types', 'ticket_types.id', '=', 'orders.ticket_type_id')
            ->join('events', 'events.id', '=', 'ticket_types.event_id')
            ->where('orders.status', '!=', 'batal')
            ->groupBy('events.id', 'events.judul')
            ->orderByDesc('terjual')
            ->take(8)
            ->get();

        $revStatus = Order::query()->tap($scoped)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all() + ['lunas' => 0, 'menunggu' => 0, 'batal' => 0];

        return compact('revStats', 'revDaily', 'revPerEvent', 'revStatus');
    }
}