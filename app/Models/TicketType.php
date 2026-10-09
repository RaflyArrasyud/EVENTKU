<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketType extends Model
{
    protected $fillable = ['event_id', 'nama', 'harga', 'kuota'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Jumlah tiket yang sudah dipesan (order batal tidak dihitung)
    public function terjual(): int
    {
        return (int) $this->orders()->where('status', '!=', 'batal')->sum('jumlah');
    }

    public function sisa(): int
    {
        return max(0, $this->kuota - $this->terjual());
    }
}