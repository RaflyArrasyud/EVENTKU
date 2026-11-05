<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function start(Order $order, MidtransClient $client)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        [$ok, $error] = (new QrisPaymentService($client))->start($order);

        if (! $ok) {
            return back()->with('paymentError', $error);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Kode QRIS dibuat. Selesaikan pembayaran sebelum kedaluwarsa.');
    }

    public function status(Order $order, MidtransClient $client)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        $order = $order->fresh();
        if (! $order->isGatewayPayment()) {
            return response()->json([
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'expires_at' => $order->payment_expires_at,
                'message' => 'Bukan pembayaran gateway.',
            ], 422);
        }

        // Locally paid with known time: no unnecessary remote call.
        // Pre-migration live code has no paid_at column: fall through to
        // verified backfill below (reads stay null-safe via attribute).
        if ($order->status === 'lunas' && $order->paid_at !== null) {
            return response()->json([
                'status' => 'lunas',
                'payment_status' => $order->payment_status,
                'expires_at' => $order->payment_expires_at,
                'message' => 'Pesanan sudah lunas.',
            ]);
        }

        // Pre-migration gateway lunas without paid_at: one verified GET to
        // backfill settlement_time only. Never fabricate now(); network
        // failure or unverified remote keeps paid stable with null paid_at.
        if ($order->status === 'lunas' && $order->isGatewayPayment() && $order->gateway_order_id) {
            try {
                (new QrisPaymentService($client))->refreshFromRemote($order);
            } catch (\Throwable) {
                // Paid stays stable; ignore backfill failure.
            }
            $current = $order->fresh();

            return response()->json([
                'status' => 'lunas',
                'payment_status' => $current->payment_status,
                'expires_at' => $current->payment_expires_at,
                'message' => 'Pesanan sudah lunas.',
            ]);
        }

        if ($order->status === 'lunas') {
            return response()->json([
                'status' => 'lunas',
                'payment_status' => $order->payment_status,
                'expires_at' => $order->payment_expires_at,
                'message' => 'Pesanan sudah lunas.',
            ]);
        }

        $out = (new QrisPaymentService($client))->refreshFromRemote($order);

        if (! ($out['ok'] ?? false)) {
            return response()->json([
                'status' => $order->fresh()->status,
                'payment_status' => $order->fresh()->payment_status,
                'expires_at' => $order->fresh()->payment_expires_at,
                'message' => $out['message'] ?? 'Belum tersedia.',
            ], 202);
        }

        return response()->json($out);
    }

    public function notification(Request $request, MidtransClient $client)
    {
        $data = $request->validate([
            'order_id' => 'required|string',
            'status_code' => 'required|string',
            'gross_amount' => 'required|string',
            'signature_key' => 'required|string',
            'transaction_status' => 'sometimes|string',
            'payment_type' => 'sometimes|string',
            'currency' => 'sometimes|string',
            'fraud_status' => 'sometimes|string',
        ]);

        $serverKey = (string) config('midtrans.server_key');
        if ($serverKey === '' || ! QrisPaymentService::verifySignature(
            $data['order_id'], $data['status_code'], $data['gross_amount'], $data['signature_key'], $serverKey
        )) {
            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $order = Order::where('gateway_order_id', $data['order_id'])->first();
        if (! $order) {
            return response()->json(['message' => 'Unknown order.'], 404);
        }

        // Strict notification identity/amount (canonical, no int truncation).
        if (! QrisPaymentService::amountEquals($data['gross_amount'], (int) $order->total)) {
            return response()->json(['message' => 'Amount mismatch.'], 422);
        }
        if (isset($data['payment_type']) && $data['payment_type'] !== 'qris') {
            return response()->json(['message' => 'Type mismatch.'], 422);
        }
        if (isset($data['currency']) && $data['currency'] !== 'IDR') {
            return response()->json(['message' => 'Currency mismatch.'], 422);
        }

        // Always authoritative refetch on signed event (no direct fraud bypass).
        try {
            [$http, $remote] = $client->transactionStatus($order->gateway_order_id);
        } catch (\Throwable) {
            return response()->json(['message' => 'Upstream unavailable, retry later.'], 500);
        }

        if ($http < 200 || $http >= 300 || (string) ($remote['status_code'] ?? '') !== '200' || ! isset($remote['transaction_status'])) {
            return response()->json(['message' => 'Upstream unavailable, retry later.'], 500);
        }

        $applied = (new QrisPaymentService($client))->applyRemoteStatus($order, $remote);
        if (! ($applied['ok'] ?? false)) {
            return response()->json(['message' => $applied['message'] ?? 'Rejected.'], 422);
        }

        return response()->json(['message' => $applied['message']]);
    }
}
