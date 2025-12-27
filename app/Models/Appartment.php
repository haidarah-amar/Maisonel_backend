<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appartment extends Model
{

    protected $guarded = [];
    protected $casts = [
        'image_url' => 'array',
    ];

    

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function favoritedByUsers()
{
    return $this->belongsToMany(
        User::class,
        'favorite'
    );
}



    public function orders()
    {
        return $this->hasMany(Order::class , 'appartment_id');
    }

    public function ratings()
{
    return $this->hasMany(Rating::class);
}

}
