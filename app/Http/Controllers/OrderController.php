<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Appartment;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    

public function store(StoreOrderRequest $request)
{
    $user = Auth::guard('sanctum')->user();

    if (! $user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $validatedData = $request->validated();

    $appartment = Appartment::findOrFail($validatedData['appartment_id']);

    if ($appartment->owner_id === $user->id) {
        return response()->json([
            'message' => 'You cannot book your own appartment'
        ], 403);
    }

    $validatedData['user_id'] = $user->id;

    $order = Order::create($validatedData);

    return response()->json([
        'message' => 'Order created successfully and awaiting approval',
        'order'   => $order,
    ], 201);
}

public function update(UpdateOrderRequest $request, $id)
{
    $user = Auth::guard('sanctum')->user();

    $order = Order::where('id', $id)
        ->where('user_id', $user->id)
        ->first();

    if (! $order) {
        return response()->json([
            'message' => 'Order not found'
        ], 404);
    }
    
    $validatedData = $request->validated();

    if ($order->status === 'pending') {
        $order->update($validatedData);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order
        ]);
    }

    if ($order->status === 'confirmed') {
        $order->update([
            'pending_changes' => $validatedData,
            'change_status'   => 'pending',
        ]);

        return response()->json([
            'message' => 'Modification request sent to owner for approval',
        ]);
    }

    return response()->json([
        'message' => 'Order cannot be updated'
    ], 409);
}


public function index()
{
    $user = Auth::guard('sanctum')->user();

    $orders = Order::where('user_id', $user->id)->get();

    return response()->json([
        'orders' => $orders
    ]);
}

public function show($id)
{
    $user = Auth::guard('sanctum')->user();

    $order = Order::where('id', $id)
        ->where('user_id', $user->id)
        ->first();

    if (! $order) {
        return response()->json([
            'message' => 'Order not found'
        ], 404);
    }

    return response()->json([
        'order' => $order
    ]);
}

public function destroy($id)
{
    $user = Auth::guard('sanctum')->user();

    $order = Order::where('id', $id)
        ->where('user_id', $user->id)
        ->whereIn('status', ['pending', 'confirmed'])
        ->first();

    if (! $order) {
        return response()->json([
            'message' => 'Order not found or cannot be cancelled'
        ], 404);
    }

    $order->update([
        'status' => 'cancelled'
    ]);

    return response()->json([
        'message' => 'Order cancelled successfully'
    ]);
}


public function unavailableDates($appartmentId)
{
    $orders = Order::where('appartment_id', $appartmentId)
        ->where('status', 'confirmed')
        ->get(['check_in_date', 'check_out_date']);

    $dates = [];

    foreach ($orders as $order) {
        $current = \Carbon\Carbon::parse($order->check_in_date);
        $end = \Carbon\Carbon::parse($order->check_out_date)->subDay();

        while ($current->lte($end)) {
            $dates[] = $current->toDateString();
            $current->addDay();
        }
    }

    return response()->json([
        'unavailable_dates' => array_values(array_unique($dates))
    ]);
}

}
