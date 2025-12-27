<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'password',
        'birth_date',
        'photo',
        'id_document',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Appartments owned by this user
    public function ownerAppartments()
    {
        return $this->hasMany(Appartment::class, 'owner_id');
    }

    /**
     * Appartments rented by this user (orders).
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }
    
    public function favoriteAppartments()
{
    return $this->belongsToMany(
        Appartment::class,
        'favorite'
    )->withTimestamps();
}
public function ratings()
{
    return $this->hasMany(Rating::class);
}

    protected $casts = [
        // 'email_verified_at' => 'datetime',
        // 'password' => 'hashed',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
