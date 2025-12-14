<?php

use App\Http\Controllers\ProfileController;
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




// Route::apiResource('tasks', TaskController::class);
