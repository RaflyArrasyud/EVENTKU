<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = ['order_id', 'kode', 'status', 'waktu_checkin'];

    protected $casts = [
        'waktu_checkin' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Derived expiry only: raw status stays 'aktif', paid orders stay 'lunas'.
    // True only for unused tickets at/after the event finish (inclusive).
    public function isExpired(): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $end = $this->order?->ticketType?->event?->tanggal_selesai;

        return $end ? now()->gte($end) : false;
    }
}