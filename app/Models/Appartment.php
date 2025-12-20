<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appartment extends Model
{
   protected $fillable = [
       'user_id',
       'title',
       'description',
       'price',
       'location',
       'image_url',
   ];
   public function owner()
   {
       return $this->belongsTo(User::class, 'user_id');
   }

   public function requests()
   {
       return $this->morphMany(Requestt::class, 'requestable');
   }
   
}
