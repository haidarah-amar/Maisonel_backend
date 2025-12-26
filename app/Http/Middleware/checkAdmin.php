<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class checkAdmin
{
    
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('sanctum')->user()->role === 'admin') 
        return $next($request);
        return response()->json(['message' => 'Unauthorized'], 403);
    
    }
}
