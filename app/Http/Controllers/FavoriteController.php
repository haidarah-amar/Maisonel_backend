<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
     public function toggle($appartmentId)
    {
        $user = Auth::guard('sanctum')->user();

        $exists = $user->favoriteAppartments()
            ->where('appartment_id', $appartmentId)
            ->exists();

        if ($exists) {
            $user->favoriteAppartments()->detach($appartmentId);

            return response()->json([
                'message' => 'Removed from favorites',
                'is_favorite' => false
            ]);
        }

        $user->favoriteAppartments()->attach($appartmentId);

        return response()->json([
            'message' => 'Added to favorites',
            'is_favorite' => true
        ]);
    }

    public function index()
    {
        $user = Auth::guard('sanctum')->user();

        return response()->json([
            'favorites' => $user->favoriteAppartments()->get()
        ]);
    }
}
