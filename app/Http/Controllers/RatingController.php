<?php

namespace App\Http\Controllers;

use App\Models\Appartment;
use App\Models\Order;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request, $orderId)
    {
        $user = Auth::guard('sanctum')->user();


        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found or not eligible for rating'
            ], 404);
        }


        if (Rating::where('order_id', $order->id)->exists()) {
            return response()->json([
                'message' => 'This order has already been rated'
            ], 409);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $rating = Rating::create([
            'user_id' => $user->id,
            'appartment_id' => $order->appartment_id,
            'order_id' => $order->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating
        ], 201);
    }

    public function update(Request $request, $ratingId)
    {
        $user = Auth::guard('sanctum')->user();


        $rating = Rating::where('id', $ratingId)
            ->where('user_id', $user->id)
            ->first();

        if (!$rating) {
            return response()->json([
                'message' => 'Rating not found or not authorized'
            ], 404);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $rating->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Rating updated successfully',
            'rating' => $rating
        ], 200);
    }

    public function index($id)
    {
        $appartment = Appartment::with([
            'ratings.user:id,first_name,last_name'
        ])->find($id);

        if (!$appartment) {
            return response()->json([
                'message' => 'Appartment not found'
            ], 404);
        }

        return response()->json([
            'appartment_id' => $appartment->id,
            'average_rating' => round($appartment->ratings()->avg('rating'), 2),
            'ratings_count' => $appartment->ratings()->count(),
            'ratings' => $appartment->ratings()->get()->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'user' => [
                        'id' => $rating->user->id,
                        'first_name' => $rating->user->first_name,
                        'last_name' => $rating->user->last_name,
                    ],
                    'rating' => $rating->rating,
                    'comment' => $rating->comment,
                    'created_at' => $rating->created_at,
                ];
            }),
        ], 200);
    }

}
