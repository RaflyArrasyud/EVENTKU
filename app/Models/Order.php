<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'ticket_type_id', 'jumlah', 'total', 'status', 'bukti_bayar',
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

    // Membuat tiket (satu per jumlah pesanan), hanya jika belum pernah dibuat
    public function issueTickets(): void
    {
        if ($this->tickets()->exists()) {
            return;
        }

        for ($i = 0; $i < $this->jumlah; $i++) {
            $this->tickets()->create([
                'kode' => strtoupper(Str::random(12)),
            ]);
        }
    }
}