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
}