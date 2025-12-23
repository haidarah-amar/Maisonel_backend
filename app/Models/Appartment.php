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
    /**
     * The current renter/tenant of the appartment (nullable).
     */
    public function renter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
