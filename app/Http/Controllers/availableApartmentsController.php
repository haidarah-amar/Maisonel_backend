<?php

namespace App\Http\Controllers;

use App\Models\Appartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class availableApartmentsController extends Controller
{
    public function index()
    {
        $user = Auth::guard('sanctum')->user();
        $availableApartments = Appartment::where('is_approved', true)->where('owner_id', '!=', $user->id)->get();
        return response()->json($availableApartments, 200);
    }
  public function filter(Request $request)
{
    $query = Appartment::where('is_approved', 1);

    if ($request->has('city')) {
        $query->where('city', $request->city);
    }

    if ($request->has('location')) {
        $query->where('location', 'LIKE', '%' . $request->location . '%');
    }

    if ($request->has('min_price')) {
        $query->where('price', '>=', $request->min_price);
    }

    if ($request->has('max_price')) {
        $query->where('price', '<=', $request->max_price);
    }

    if ($request->has('bedrooms')) {
        $query->where('bedrooms', $request->bedrooms);
    }

    return response()->json(
        $query->latest()->get()
    );
}
}
