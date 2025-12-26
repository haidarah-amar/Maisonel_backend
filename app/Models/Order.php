<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = [
    'pending_changes' => 'array',
];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function appartment()
    {
        return $this->belongsTo(Appartment::class);
    }

}
