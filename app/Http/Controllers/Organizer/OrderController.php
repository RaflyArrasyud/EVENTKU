<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Order;

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

        abort_if($order->isGatewayPayment(), 403, 'Pesanan gateway tidak dapat disetujui manual.');

        $attrs = ['status' => 'lunas'];
        if (Order::supportsPaidAt()) {
            $attrs['paid_at'] = now();
        }
        $order->update($attrs);

        $order->issueTickets();

        return back()->with('success', 'Pembayaran disetujui, tiket dibuat.');
    }

    public function reject(Order $order)
    {
        $this->authorizeOwner($order);

        if ($order->status !== 'menunggu') {
            return back()->with('error', 'Pesanan ini sudah diproses.');
        }

        abort_if($order->isGatewayPayment(), 403, 'Pesanan gateway tidak dapat ditolak manual.');

        $order->update(['status' => 'batal']);

        return back()->with('success', 'Pesanan ditolak.');
    }

    private function authorizeOwner(Order $order): void
    {
        abort_if($order->ticketType->event->organizer_id !== auth()->id(), 403);
    }
}