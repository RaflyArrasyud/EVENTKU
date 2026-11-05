<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpMessage extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'isi', 'read_at'];

    public function ticket()
    {
        return $this->belongsTo(HelpTicket::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
