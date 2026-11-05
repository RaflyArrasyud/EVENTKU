<?php

namespace App\Services;

use App\Models\Order;
use App\Models\TicketType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QrisPaymentService
{
    public const TERMINAL = ['settlement', 'expire', 'deny', 'cancel'];

    public function __construct(private MidtransClient $client) {}

    public function start(Order $order): array
    {
        if (! Order::paymentConfigured()) {
            return [false, 'Pembayaran QRIS belum dikonfigurasi.'];
        }

        // Writer barrier first (SQLite has no reliable row lock): touch + re-fetch.
        $snapshot = DB::transaction(function () use ($order) {
            Order::whereKey($order->id)->update(['updated_at' => now()]);

            return Order::whereKey($order->id)->first();
        });

        if (! $snapshot || $snapshot->status !== 'menunggu' || ! $snapshot->isGatewayPayment()) {
            if ($snapshot && $snapshot->status === 'lunas') {
                return [false, 'Pesanan sudah lunas.'];
            }

            return [false, 'Pesanan tidak dapat dibayar via QRIS.'];
        }

        // Replay aman: QR sudah ada, jangan charge ulang.
        if ($snapshot->qr_string && $snapshot->payment_status === 'pending' && $snapshot->payment_expires_at?->isFuture()) {
            return [true, null];
        }

        // Any existing attempt: ALWAYS GET remote status first, never re-charge.
        if ($snapshot->gateway_order_id) {
            return $this->resumeExisting($snapshot);
        }

        // Unattempted order past original expiry: release safely (never charged).
        if ($snapshot->payment_expires_at && $snapshot->payment_expires_at->isPast()) {
            Order::whereKey($snapshot->id)
                ->where('status', 'menunggu')
                ->whereNull('gateway_order_id')
                ->where('payment_status', 'created')
                ->update(['status' => 'batal', 'payment_status' => 'expire']);

            return [false, 'Pesanan kedaluwarsa. Silakan buat pesanan baru.'];
        }

        // Atomic claim: only an unattempted created order becomes creating.
        $gid = 'EventKu-'.$snapshot->id.'-'.now()->format('YmdHis');
        $claimed = DB::transaction(function () use ($snapshot, $gid) {
            Order::whereKey($snapshot->id)->update(['updated_at' => now()]);

            return Order::whereKey($snapshot->id)
                ->where('status', 'menunggu')
                ->whereNull('gateway_order_id')
                ->where('payment_status', 'created')
                ->where('payment_expires_at', '>', now())
                ->update(['gateway_order_id' => $gid, 'payment_status' => 'creating']);
        });

        if (! $claimed) {
            $fresh = Order::whereKey($snapshot->id)->first();
            if ($fresh && $fresh->qr_string && $fresh->payment_status === 'pending' && $fresh->payment_expires_at?->isFuture()) {
                return [true, null];
            }
            if ($fresh && $fresh->gateway_order_id) {
                return $this->resumeExisting($fresh);
            }

            return [false, 'Pesanan sedang diproses. Coba lagi.'];
        }

        try {
            [$http, $body] = $this->client->chargeQris(
                $gid,
                (int) $snapshot->total,
                $snapshot->created_at?->timezone('Asia/Jakarta')->format('Y-m-d H:i:s O')
            );
        } catch (\Throwable) {
            // Timeout keeps gateway id: ambiguous, hold for reconciliation.
            Order::whereKey($snapshot->id)->where('payment_status', 'creating')->update(['payment_status' => 'error']);

            return [false, 'Gagal menghubungi penyedia pembayaran. Pesanan tertunda rekonsiliasi, coba lagi tanpa bayar ulang.'];
        }

        if ($http < 200 || $http >= 300 || (string) ($body['status_code'] ?? '') !== '201'
            || ($body['transaction_status'] ?? '') !== 'pending') {
            Order::whereKey($snapshot->id)->where('payment_status', 'creating')->update(['payment_status' => 'error']);

            return [false, 'Gagal membuat QRIS. Coba lagi.'];
        }

        $qrString = $body['qr_string'] ?? null;
        if (! $qrString) {
            Order::whereKey($snapshot->id)->where('payment_status', 'creating')->update(['payment_status' => 'error']);

            return [false, 'Gagal membuat QRIS. Coba lagi.'];
        }

        $qrUrl = $this->extractQrUrl($body['actions'] ?? null);
        $expiry = $this->resolveExpiry($snapshot, $body);

        // Persist inside writer re-fetch; never override lunas/terminal if webhook won.
        $persisted = DB::transaction(function () use ($snapshot, $gid, $qrString, $qrUrl, $body, $expiry) {
            Order::whereKey($snapshot->id)->update(['updated_at' => now()]);
            $fresh = Order::whereKey($snapshot->id)->first();

            if (! $fresh || $fresh->status !== 'menunggu') {
                return 'terminal';
            }
            if (! in_array($fresh->payment_status, ['creating', 'error'], true)) {
                return 'terminal';
            }
            if ($fresh->gateway_order_id !== $gid) {
                return 'terminal';
            }

            $fresh->update([
                'qr_string' => $qrString,
                'payment_qr_url' => $qrUrl,
                'payment_status' => $body['transaction_status'] ?? 'pending',
                'payment_expires_at' => $expiry,
                'gateway_transaction_id' => $body['transaction_id'] ?? null,
            ]);

            return 'ok';
        });

        if ($persisted === 'terminal') {
            $fresh = Order::whereKey($snapshot->id)->first();
            if ($fresh && $fresh->status === 'lunas') {
                return [true, null];
            }

            return [false, 'Pesanan sudah diproses.'];
        }

        return [true, null];
    }

    private function resumeExisting(Order $snapshot): array
    {
        try {
            [$http, $body] = $this->client->transactionStatus($snapshot->gateway_order_id);
        } catch (\Throwable) {
            return [false, 'Gagal menghubungi penyedia pembayaran. Coba lagi.'];
        }

        // Remote 404 after timeout is ambiguous: hold, no auto release/recharge.
        if ($http === 404) {
            return [false, 'Status pembayaran belum pasti (percobaan tertunda). Jangan bayar ulang dulu, coba lagi.'];
        }

        if ($http < 200 || $http >= 300 || (string) ($body['status_code'] ?? '') !== '200' || ! isset($body['transaction_status'])) {
            return [false, 'Status pembayaran belum tersedia.'];
        }

        $applied = $this->applyRemoteStatus($snapshot, $body);
        $fresh = Order::whereKey($snapshot->id)->first();

        if ($fresh && $fresh->status === 'lunas') {
            return [true, null];
        }
        if ($fresh && $fresh->status !== 'menunggu') {
            return [false, $applied['message'] ?? 'Pesanan sudah diproses.'];
        }
        if ($fresh && $fresh->qr_string && $fresh->payment_status === 'pending' && $fresh->payment_expires_at?->isFuture()) {
            return [true, null];
        }

        return [false, $applied['message'] ?? 'Menunggu pembayaran.'];
    }

    public function refreshFromRemote(Order $order): array
    {
        if (! $order->gateway_order_id) {
            return ['ok' => false, 'message' => 'Pesanan belum memiliki kode gateway.'];
        }

        try {
            [$http, $body] = $this->client->transactionStatus($order->gateway_order_id);
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Gagal menghubungi penyedia pembayaran.'];
        }

        if ($http === 404) {
            return ['ok' => false, 'message' => 'Status pembayaran belum tersedia.'];
        }

        // Authenticated API response required before applying.
        if ($http < 200 || $http >= 300 || (string) ($body['status_code'] ?? '') !== '200' || ! isset($body['transaction_status'])) {
            return ['ok' => false, 'message' => 'Status pembayaran belum tersedia.'];
        }

        $applied = $this->applyRemoteStatus($order, $body);
        if (! ($applied['ok'] ?? false)) {
            return ['ok' => false, 'message' => $applied['message'] ?? 'Ditolak.'];
        }

        return [
            'ok' => true,
            'status' => $order->fresh()->status,
            'payment_status' => $order->fresh()->payment_status,
            'expires_at' => $order->fresh()->payment_expires_at,
            'message' => $applied['message'],
        ];
    }

    public function applyRemoteStatus(Order $order, array $remote): array
    {
        $gid = $order->gateway_order_id;

        if ($gid === null || ($remote['order_id'] ?? null) !== $gid) {
            return ['ok' => false, 'message' => 'Identitas pembayaran tidak sesuai.'];
        }

        if (($remote['payment_type'] ?? null) !== 'qris') {
            return ['ok' => false, 'message' => 'Tipe pembayaran tidak sesuai.'];
        }

        // Currency required: Midtrans status always sends it; missing rejected.
        if (($remote['currency'] ?? null) !== 'IDR') {
            return ['ok' => false, 'message' => 'Mata uang tidak sesuai.'];
        }

        if (! isset($remote['gross_amount']) || ! self::amountEquals($remote['gross_amount'], (int) $order->total)) {
            return ['ok' => false, 'message' => 'Nominal tidak sesuai, diabaikan.'];
        }

        $remoteStatus = (string) ($remote['transaction_status'] ?? '');
        if (! in_array($remoteStatus, ['pending', 'settlement', 'expire', 'deny', 'cancel'], true)) {
            return ['ok' => false, 'message' => 'Status tidak dikenal.'];
        }

        // QRIS settles only via settlement, never capture.
        if ($remoteStatus === 'capture') {
            return ['ok' => false, 'message' => 'Capture bukan status QRIS.'];
        }

        if ($remoteStatus === 'settlement') {
            $fraud = $remote['fraud_status'] ?? null;
            if ($fraud !== null && $fraud !== 'accept') {
                return ['ok' => false, 'message' => 'Status fraud tidak valid.'];
            }
        }

        return DB::transaction(function () use ($order, $remoteStatus, $remote) {
            // Writer barrier first (matches checkout serialization; on SQLite
            // this serializes globally across ticket types).
            TicketType::whereKey($order->ticket_type_id)->update(['updated_at' => now()]);
            Order::whereKey($order->id)->update(['updated_at' => now()]);
            $locked = Order::whereKey($order->id)->first();

            if (! $locked) {
                return ['ok' => false, 'message' => 'Pesanan tidak ditemukan.'];
            }

            // Paid is stable: never downgrade status or payment_status.
            if ($locked->status === 'lunas') {
                // Backfill only when provider time is verifiable; never overwrite.
                // Guarded for live pre-migration code sharing (no paid_at column yet).
                $hasPaidAt = Order::supportsPaidAt();
                if ($remoteStatus === 'settlement' && $hasPaidAt && $locked->paid_at === null
                    && ($parsed = self::parseSettlementTime($remote))) {
                    $backfill = ['paid_at' => $parsed];
                    if (is_string($remote['transaction_id'] ?? null) && $remote['transaction_id'] !== '') {
                        $backfill['gateway_transaction_id'] = $remote['transaction_id'];
                    }
                    Order::whereKey($locked->id)->whereNull('paid_at')->update($backfill);
                } elseif ($remoteStatus === 'settlement' && ! ($locked->gateway_transaction_id ?? null)
                    && is_string($remote['transaction_id'] ?? null) && $remote['transaction_id'] !== '') {
                    Order::whereKey($locked->id)->whereNull('gateway_transaction_id')
                        ->update(['gateway_transaction_id' => $remote['transaction_id']]);
                }

                return ['ok' => true, 'message' => 'Pesanan sudah lunas.'];
            }

            if ($locked->status !== 'menunggu') {
                // Late authoritative settlement after local terminal cancel:
                // flag for manual reconciliation, no tickets, no resurrection.
                if ($remoteStatus === 'settlement' && $locked->status === 'batal') {
                    $review = ['payment_status' => 'settlement_review'];
                    if (Order::supportsPaidAt() && $locked->paid_at === null && ($parsed = self::parseSettlementTime($remote))) {
                        $review['paid_at'] = $parsed;
                    }
                    if (! ($locked->gateway_transaction_id ?? null)
                        && is_string($remote['transaction_id'] ?? null) && $remote['transaction_id'] !== '') {
                        $review['gateway_transaction_id'] = $remote['transaction_id'];
                    }
                    Order::whereKey($locked->id)->where('status', 'batal')->update($review);

                    return ['ok' => true, 'message' => 'Pembayaran masuk setelah pembatalan, perlu rekonsiliasi manual.'];
                }

                return ['ok' => true, 'message' => 'Pesanan sudah diproses.'];
            }

            if ($remoteStatus === 'settlement') {
                // Quota already reserved while menunggu: fulfill even past timer.
                $settle = ['status' => 'lunas', 'payment_status' => 'settlement'];
                if (Order::supportsPaidAt()) {
                    $settle['paid_at'] = self::parseSettlementTime($remote) ?? now();
                }
                if (is_string($remote['transaction_id'] ?? null) && $remote['transaction_id'] !== '') {
                    $settle['gateway_transaction_id'] = $remote['transaction_id'];
                }
                $updated = Order::whereKey($locked->id)->where('status', 'menunggu')->update($settle);

                if ($updated) {
                    Order::whereKey($locked->id)->first()?->issueTickets();

                    return ['ok' => true, 'message' => 'Pembayaran lunas, tiket dibuat.'];
                }

                return ['ok' => true, 'message' => 'Pesanan sudah diproses.'];
            }

            if (in_array($remoteStatus, ['expire', 'deny', 'cancel'], true)) {
                Order::whereKey($locked->id)->where('status', 'menunggu')
                    ->update(['status' => 'batal', 'payment_status' => $remoteStatus]);

                return ['ok' => true, 'message' => 'Pembayaran kedaluwarsa/dibatalkan.'];
            }

            $changes = ['payment_status' => $remoteStatus];
            // Recover QR details after a lost charge response when supplied by
            // the authenticated status API. Never fabricate a payable payload.
            if (is_string($remote['qr_string'] ?? null) && $remote['qr_string'] !== '') {
                $changes['qr_string'] = $remote['qr_string'];
            }
            if ($url = $this->extractQrUrl($remote['actions'] ?? null)) {
                $changes['payment_qr_url'] = $url;
            }
            Order::whereKey($locked->id)->where('status', 'menunggu')->update($changes);

            return ['ok' => true, 'message' => 'Menunggu pembayaran.'];
        });
    }

    public function reconcileDue(int $limit = 50): array
    {
        // Check all active remote attempts, including payments completed before
        // their expiry. Old unresolved attempts must not starve newer orders.
        $due = Order::where('payment_method', 'midtrans_qris')
            ->where('status', 'menunggu')
            ->whereNotNull('gateway_order_id')
            ->orderBy('updated_at')
            ->limit(max(1, min($limit, 200)))
            ->get();

        $result = ['checked' => 0, 'settled' => 0, 'released' => 0, 'held' => 0];

        foreach ($due as $order) {
            $order->touch();
            $result['checked']++;
            $out = $this->refreshFromRemote($order);
            $fresh = $order->fresh();
            if (! ($out['ok'] ?? false)) {
                $result['held']++;
                continue;
            }
            if ($fresh->status === 'lunas') {
                $result['settled']++;
            } elseif ($fresh->status === 'batal') {
                $result['released']++;
            } else {
                $result['held']++;
            }
        }

        // Only unattempted created orders with no gateway id are safe to release.
        $released = Order::where('payment_method', 'midtrans_qris')
            ->where('status', 'menunggu')
            ->whereNull('gateway_order_id')
            ->where('payment_status', 'created')
            ->where('payment_expires_at', '<=', now())
            ->update(['status' => 'batal', 'payment_status' => 'expire']);
        $result['released'] += $released;
        $result['checked'] += $released;

        return $result;
    }

    public static function amountEquals(mixed $raw, int $expected): bool
    {
        $str = trim((string) $raw);
        if (! preg_match('/^\d+(\.0+)?$/', $str)) {
            return false;
        }

        return (int) explode('.', $str)[0] === $expected;
    }

    public static function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey, string $serverKey): bool
    {
        return hash_equals(hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey), strtolower($signatureKey));
    }

    // Provider settlement_time parsed in Asia/Jakarta unless offset present,
    // converted to app timezone. Null when absent/unparseable (caller uses now).
    public static function parseSettlementTime(array $remote): mixed
    {
        $raw = $remote['settlement_time'] ?? null;
        if (! is_string($raw) || trim($raw) === '') {
            return null;
        }

        try {
            return Carbon::parse(trim($raw), 'Asia/Jakarta')->timezone(config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function extractQrUrl(mixed $actions): ?string
    {
        if (! is_array($actions)) {
            return null;
        }

        foreach ($actions as $action) {
            $name = $action['name'] ?? '';
            if ($name !== 'generate-qr-code' && $name !== 'generate-qr-code-v2') {
                continue;
            }
            $url = (string) ($action['url'] ?? '');
            $parts = parse_url($url);
            if (($parts['scheme'] ?? '') === 'https' && ($parts['host'] ?? '') === 'api.sandbox.midtrans.com') {
                return $url;
            }
        }

        return null;
    }

    private function resolveExpiry(Order $snapshot, array $body): mixed
    {
        if (isset($body['expiry_time'])) {
            try {
                $parsed = Carbon::parse($body['expiry_time'], 'Asia/Jakarta')->timezone(config('app.timezone'));
                if ($parsed->isFuture()) {
                    return $parsed;
                }
            } catch (\Throwable) {
                // Fall through to original expiry.
            }
        }

        // Original custom_expiry fixed at checkout; never reset from now.
        return $snapshot->payment_expires_at;
    }
}
