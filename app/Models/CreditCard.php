<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $fillable = [
        'user_id',
        'card_holder_name',
        'card_number',
        'expiration_date',
        'cvv',
    ];
}
