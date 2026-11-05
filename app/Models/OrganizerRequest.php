<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerRequest extends Model
{
    protected $fillable = ['user_id', 'alasan', 'kontak', 'status', 'seen_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
