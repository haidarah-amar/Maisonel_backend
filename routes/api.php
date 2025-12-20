<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Appartmentcontroller;
use App\Models\Appartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Termwind\Components\Raw;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::group(
    [
        'prefix' => 'appartment'
    ],
    function () {
        Route::get('/index', [Appartmentcontroller::class, 'index']);
        Route::post('/create', [Appartmentcontroller::class, 'store']);
        Route::post('/update/{id}', [Appartmentcontroller::class, 'update']);
        Route::delete('/destroy', [Appartmentcontroller::class, 'destroy']);
        Route::get('/show/{id}', [Appartmentcontroller::class, 'show']);



    }
);

// This is a test from fares <3

// this is also a test but from antigravity


// Route::apiResource('tasks', TaskController::class);
