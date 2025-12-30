<?php

namespace App\Http\Controllers;

use App\Models\Appartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class availableApartmentsController extends Controller
{
    public function available()
    {
        $user = Auth::guard('sanctum')->user();
        $availableApartments = Appartment::where('is_approved', true)->where('is_active', true)->where('owner_id', '!=', $user->id)->get();
        return response()->json($availableApartments, 200);
    }

    public function owned()
    {
        $user = Auth::guard('sanctum')->user();
        $availableApartments = Appartment::where('owner_id', '=', $user->id)->get();
        return response()->json($availableApartments, 200);
    }

}
