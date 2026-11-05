<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpTicket extends Model
{
    protected $fillable = ['user_id', 'topik', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(HelpMessage::class, 'ticket_id')->oldest();
    }
}
