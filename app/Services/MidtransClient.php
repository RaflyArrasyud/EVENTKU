<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MidtransClient
{
    public function chargeQris(string $gatewayOrderId, int $grossAmount, ?string $orderTime = null): array
    {
        if (! \App\Models\Order::paymentConfigured()) {
            throw new \RuntimeException('Sandbox payment is not configured.');
        }
        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $gatewayOrderId,
                'gross_amount' => $grossAmount,
            ],
            'custom_expiry' => [
                'expiry_duration' => (int) config('midtrans.expiry_duration', 15),
                'unit' => config('midtrans.expiry_unit', 'minute'),
            ],
        ];

        if ($orderTime) {
            $payload['custom_expiry']['order_time'] = $orderTime;
        }

        $response = Http::withBasicAuth((string) config('midtrans.server_key'), '')
            ->withOptions(['verify' => config('midtrans.ca_bundle') ?: true])
            ->acceptJson()
            ->timeout(20)
            ->post('https://api.sandbox.midtrans.com/v2/charge', $payload);

        return [$response->status(), $response->json() ?? []];
    }

    public function transactionStatus(string $gatewayOrderId): array
    {
        if (! \App\Models\Order::paymentConfigured()) {
            throw new \RuntimeException('Sandbox payment is not configured.');
        }
        $response = Http::withBasicAuth((string) config('midtrans.server_key'), '')
            ->withOptions(['verify' => config('midtrans.ca_bundle') ?: true])
            ->acceptJson()
            ->timeout(20)
            ->get('https://api.sandbox.midtrans.com/v2/'.rawurlencode($gatewayOrderId).'/status');

        return [$response->status(), $response->json() ?? []];
    }
}
