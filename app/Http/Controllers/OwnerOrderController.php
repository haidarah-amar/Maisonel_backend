<?php

namespace App\Http\Controllers;

use App\Models\Appartment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerOrderController extends Controller
{
    public function index()
{
    $owner = Auth::guard('sanctum')->user();

    if (! $owner) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $orders = Order::whereHas('appartment', function ($query) use ($owner) {
            $query->where('owner_id', $owner->id);
        })->get();

    return response()->json([
        'orders' => $orders
    ]);
}

public function show($id)
{
    $owner = Auth::guard('sanctum')->user();

    if (! $owner) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $order = Order::where('id', $id)
        ->whereHas('appartment', function ($query) use ($owner) {
            $query->where('owner_id', $owner->id);
        })
        ->first();

    if (! $order) {
        return response()->json([
            'message' => 'Order not found or you do not have access to this order'
        ], 404);
    }

    return response()->json([
        'message' => 'Order retrieved successfully',
        'order'   => $order
    ], 200);
}

public function approve($id)
{
    $owner = Auth::guard('sanctum')->user();

    $order = Order::where('id', $id)
        ->whereHas('appartment', function ($q) use ($owner) {
            $q->where('owner_id', $owner->id);
        })
        ->where('status', 'pending')
        ->firstOrFail();

    // confirm the order
    $order->update([
        'status' => 'confirmed'
    ]);

    // reject conflicting pending orders
    Order::where('appartment_id', $order->appartment_id)
        ->where('status', 'pending')
        ->where('id', '!=', $order->id)
        ->where(function ($query) use ($order) {
            $query->where('check_in_date', '<', $order->check_out_date)
                  ->where('check_out_date', '>', $order->check_in_date);
        })
        ->update([
            'status' => 'rejected'
        ]);

    return response()->json([
        'message' => 'Order approved and conflicting orders rejected'
    ]);
}
public function reject($id)
{
    $owner = Auth::guard('sanctum')->user();

    $order = Order::where('id', $id)
        ->whereHas('appartment', function ($query) use ($owner) {
            $query->where('owner_id', $owner->id);
        })
        ->where('status', 'pending')
        ->first();

    if (! $order) {
        return response()->json([
            'message' => 'Order not found or cannot be rejected'
        ], 404);
    }

    $order->update([
        'status' => 'rejected'
    ]);

    return response()->json([
        'message' => 'Order rejected successfully',
        'order'   => $order
    ], 200);
}

public function approveModification($orderId)
{
    $user = Auth::guard('sanctum')->user();
    $order = Order::findOrFail($orderId);

    $apartment = Appartment::findOrFail($order->appartment_id);
    if ($apartment->owner_id !== $user->id) {
        return response()->json([
            'message' => 'Forbidden'
        ], 403);
    }

    if ($order->change_status !== 'pending' || empty($order->pending_changes)) {
        return response()->json([
            'message' => 'No pending modifications'
        ], 409);
    }

    $allowedFields = [
        'guest_count',
        'check_in_date',
        'check_out_date',
    ];

    $approvedChanges = collect($order->pending_changes)
        ->only($allowedFields)
        ->toArray();

    $order->update(array_merge(
        $approvedChanges,
        [
            'pending_changes' => null,
            'change_status' => 'none',
        ]
    ));

    return response()->json([
        'message' => 'Modification approved',
        'order' => $order->fresh()
    ]);
}
public function rejectModification($orderId)
{
    $user = Auth::guard('sanctum')->user();
    $order = Order::findOrFail($orderId);

    $apartment = Appartment::findOrFail($order->appartment_id);
    if ($apartment->owner_id !== $user->id) {
        return response()->json([
            'message' => 'Forbidden'
        ], 403);
    }
    if ($order->change_status !== 'pending' || empty($order->pending_changes)) {
        return response()->json([
            'message' => 'No pending modifications'
        ], 409);
    }
    $order->update([
        'pending_changes' => null,
        'change_status' => 'none',
    ]);

    return response()->json([
        'message' => 'Modification rejected, order unchanged'
    ]);
}


}
