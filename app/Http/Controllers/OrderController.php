<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()
            ->with('ticketType.event')
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function store(Request $request, TicketType $ticketType)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1|max:5',
        ]);

        $event = $ticketType->event;
        abort_if($event->status !== 'terbit', 404);

        if ($event->tanggal_selesai->isPast()) {
            return back()->withErrors(['jumlah' => 'Event ini sudah selesai.']);
        }

        // Transaksi + lockForUpdate: dua pembeli bersamaan tidak bisa
        // sama-sama mengambil kuota terakhir (mencegah overselling)
        $order = DB::transaction(function () use ($ticketType, $data) {
            $locked = TicketType::whereKey($ticketType->id)->lockForUpdate()->first();

            if ($locked->sisa() < $data['jumlah']) {
                return null;
            }

            $total = $locked->harga * $data['jumlah'];

            $order = Order::create([
                'user_id' => auth()->id(),
                'ticket_type_id' => $locked->id,
                'jumlah' => $data['jumlah'],
                'total' => $total,
                'status' => $total == 0 ? 'lunas' : 'menunggu',
            ]);

            if ($order->status === 'lunas') {
                $order->issueTickets();
            }

            return $order;
        });

        if (! $order) {
            return back()->withErrors(['jumlah' => 'Kuota tiket tidak mencukupi.']);
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan berhasil dibuat.');
    }

    public function show(Order $order)
    {
        $this->authorizeOwner($order);
        $order->load('ticketType.event', 'tickets');

        return view('orders.show', compact('order'));
    }

    public function uploadBukti(Request $request, Order $order)
    {
        $this->authorizeOwner($order);
        abort_if($order->status !== 'menunggu', 403);

        $request->validate([
            'bukti' => 'required|image|max:2048',
        ]);

        $order->update([
            'bukti_bayar' => $request->file('bukti')->store('bukti', 'public'),
        ]);

        return back()->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi organizer.');
    }

    private function authorizeOwner(Order $order): void
    {
        abort_if($order->user_id !== auth()->id(), 403);
    }
}