<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['ticketType.event', 'user'])
            ->whereHas('ticketType.event', fn ($q) => $q->where('organizer_id', auth()->id()))
            ->latest()
            ->paginate(15);

        return view('organizer.orders.index', compact('orders'));
    }

    public function approve(Order $order)
    {
        $this->authorizeOwner($order);

        if ($order->status !== 'menunggu') {
            return back()->with('error', 'Pesanan ini sudah diproses.');
        }

        $order->update(['status' => 'lunas']);

        if ($order->tickets()->count() === 0) {
            for ($i = 0; $i < $order->jumlah; $i++) {
                $order->tickets()->create([
                    'kode' => strtoupper(Str::random(10)),
                    'status' => 'aktif',
                ]);
            }
        }

        return back()->with('success', 'Pembayaran disetujui, tiket dibuat.');
    }

    public function reject(Order $order)
    {
        $this->authorizeOwner($order);

        if ($order->status !== 'menunggu') {
            return back()->with('error', 'Pesanan ini sudah diproses.');
        }

        $order->update(['status' => 'batal']);

        return back()->with('success', 'Pesanan ditolak.');
    }

    private function authorizeOwner(Order $order): void
    {
        abort_if($order->ticketType->event->organizer_id !== auth()->id(), 403);
    }
}