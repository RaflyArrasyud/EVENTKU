<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'organizer_id', 'judul', 'deskripsi', 'kategori', 'tipe',
        'lokasi', 'tanggal_mulai', 'tanggal_selesai', 'poster', 'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }
}