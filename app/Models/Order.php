<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'ticket_type_id', 'jumlah', 'total', 'status', 'bukti_bayar',
        'checkout_key', 'payment_method', 'gateway_order_id', 'payment_status',
        'qr_string', 'payment_qr_url', 'payment_expires_at', 'gateway_transaction_id',
        'paid_at',
    ];

    protected $casts = [
        'payment_expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // Live 8084 shares code pre-migration: skip paid_at writes until
    // 2026_10_11_000007 has run to avoid missing-column errors.
    public static function supportsPaidAt(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn('orders', 'paid_at');
        } catch (\Throwable) {
            return false;
        }
    }

    public function isGatewayPayment(): bool
    {
        return ($this->payment_method ?? 'manual') === 'midtrans_qris';
    }

    public static function paymentConfigured(): bool
    {
        return (bool) config('midtrans.server_key');
    }

    // Membuat tiket (satu per jumlah pesanan), hanya jika belum pernah dibuat
    public function issueTickets(): void
    {
        DB::transaction(function () {
            // Writer barrier first (SQLite has no reliable row lock).
            self::whereKey($this->id)->update(['updated_at' => now()]);
            $fresh = self::whereKey($this->id)->first();

            if (! $fresh || $fresh->status !== 'lunas' || $fresh->tickets()->exists()) {
                return;
            }

            for ($i = 0; $i < $fresh->jumlah; $i++) {
                $fresh->tickets()->create([
                    'kode' => strtoupper(Str::random(12)),
                ]);
            }
        });
    }
}