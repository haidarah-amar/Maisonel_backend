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
}
