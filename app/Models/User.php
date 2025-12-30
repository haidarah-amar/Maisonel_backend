<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use App\Models\Appartment;
use App\Models\Order;
use App\Models\Rating;
use App\Models\CreditCard;

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
        'telegram_chat_id',
        'verification_code',
        'otp_code',
        'otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'otp_code',
        'otp_expires_at',
    ];
    /**
     * Normalize and Validate Syrian Phone Number
     * Expected format: 09xxxxxxxx or +9639xxxxxxxx
     */
    public static function validateSyrianNumber($phone)
    {
        // Remove spaces and dashes
        $phone = preg_replace('/[\s\-]/', '', $phone);

        // Regex for Syrian Mobile (Starts with +9639 or 009639 or 09)
        // Total digits excluding country code is 9 (e.g. 944 123 456)
        if (preg_match('/^(?:\+963|00963|0)?(9\d{8})$/', $phone, $matches)) {
            return '+963' . $matches[1]; // Standardize to E.164 format
        }

        return false;
    }

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
public function creditCards()
{
    return $this->hasMany(CreditCard::class);
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
