<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
            'checkout_key' => 'nullable|uuid',
        ]);

        $event = $ticketType->event;
        abort_if($event->status !== 'terbit', 404);

        if ($event->tanggal_selesai->isPast()) {
            return back()->withErrors(['jumlah' => 'Event ini sudah selesai.']);
        }

        if ($ticketType->harga * (int) $data['jumlah'] > 0 && ! Order::paymentConfigured()) {
            return back()->withErrors(['jumlah' => 'Pembayaran QRIS belum dikonfigurasi.']);
        }

        $checkoutKey = $data['checkout_key'] ?? null;

        // Transaksi + first-write serialize: dua pembeli bersamaan tidak bisa
        // sama-sama mengambil kuota terakhir (mencegah overselling).
        // (SQLite lockForUpdate unreliable, jadi pakai write barrier.)
        // Replay check di DALAM transaksi agar race key sama tidak unique error.
        $attempts = 0;
        do {
            try {
                $order = DB::transaction(function () use ($ticketType, $data, $checkoutKey) {
                    TicketType::whereKey($ticketType->id)->update(['updated_at' => now()]);

                    if ($checkoutKey) {
                        $existing = Order::where('checkout_key', $checkoutKey)->first();

                        if ($existing) {
                            abort_if($existing->user_id !== auth()->id(), 403);
                            abort_if(
                                $existing->ticket_type_id !== $ticketType->id || $existing->jumlah !== (int) $data['jumlah'],
                                409
                            );

                            return $existing;
                        }
                    }

                    $locked = TicketType::whereKey($ticketType->id)->first();

                    if ($locked->sisa() < $data['jumlah']) {
                        return null;
                    }

                    $total = $locked->harga * $data['jumlah'];
                    $gateway = $total > 0;

                    $attrs = [
                        'user_id' => auth()->id(),
                        'ticket_type_id' => $locked->id,
                        'jumlah' => $data['jumlah'],
                        'total' => $total,
                        'status' => $total == 0 ? 'lunas' : 'menunggu',
                        'checkout_key' => $checkoutKey ?? (string) Str::uuid(),
                        'payment_method' => $gateway ? 'midtrans_qris' : 'manual',
                        'payment_status' => $gateway ? 'created' : null,
                        'payment_expires_at' => $gateway ? now()->addMinutes((int) config('midtrans.expiry_duration', 15)) : null,
                    ];
                    // Omitted pre-migration on live shared code (no column yet).
                    if ($total == 0 && Order::supportsPaidAt()) {
                        $attrs['paid_at'] = now();
                    }
                    $order = Order::create($attrs);

                    if ($order->status === 'lunas') {
                        $order->issueTickets();
                    }

                    return $order;
                });
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                // Race key unik bersamaan: baca ulang hasil pemenang.
                if ($checkoutKey && str_contains(strtolower($e->getMessage()), 'unique') && ++$attempts < 3) {
                    usleep(50000 * $attempts);
                    continue;
                }
                throw $e;
            }
        } while (true);

        if ($order && $checkoutKey && $order->checkout_key === $checkoutKey && $order->wasRecentlyCreated === false) {
            return redirect()->route('orders.show', $order);
        }

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

        // GET tanpa efek samping remote; hanya lepas reservasi lokal yang
        // belum pernah charge ke gateway (conditional, anti race creating).
        Order::whereKey($order->id)
            ->where('payment_method', 'midtrans_qris')
            ->where('status', 'menunggu')
            ->whereNull('gateway_order_id')
            ->where('payment_status', 'created')
            ->where('payment_expires_at', '<=', now())
            ->update(['status' => 'batal', 'payment_status' => 'expire']);
        $order->refresh();

        return view('orders.show', [
            'order' => $order,
            'paymentConfigured' => Order::paymentConfigured(),
            'paymentError' => session('paymentError'),
        ]);
    }

    public function uploadBukti(Request $request, Order $order)
    {
        $this->authorizeOwner($order);
        abort_if($order->status !== 'menunggu', 403);
        abort_if($order->isGatewayPayment(), 403);

        $request->validate([
            'bukti' => 'required|image|max:2048',
        ]);

        $order->update([
            'bukti_bayar' => $request->file('bukti')->store('bukti', 'public'),
        ]);

        return back()->with('success', 'Bukti pembayaran terkirim. Menunggu verifikasi organizer.');
    }

    public function proof(Order $order)
    {
        $order->loadMissing('ticketType.event');
        $user = auth()->user();
        abort_unless(
            $order->user_id === $user->id
                || $order->ticketType?->event?->organizer_id === $user->id,
            403
        );

        $path = (string) ($order->bukti_bayar ?? '');
        abort_if($path === '', 404);
        abort_if(str_contains($path, '..') || ! str_starts_with($path, 'bukti/'), 404);
        abort_unless(Storage::disk('public')->exists($path), 404);

        $response = response()->file(Storage::disk('public')->path($path));
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private,no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response->setContentDisposition('inline');
    }

    private function authorizeOwner(Order $order): void
    {
        abort_if($order->user_id !== auth()->id(), 403);
    }
}
