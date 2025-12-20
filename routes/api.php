<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Appartmentcontroller;
use App\Models\Appartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Termwind\Components\Raw;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/logout', [UserController::class, 'logout']);
Route::get('/appartment/index', [Appartmentcontroller::class, 'index']);

Route::group(
    [
        'prefix' => 'appartment',
        'middleware' => 'jwt.auth'
    ],
    function () {
        Route::post('/create', [Appartmentcontroller::class, 'store']);
        Route::post('/update/{id}', [Appartmentcontroller::class, 'update']);
        Route::delete('/destroy', [Appartmentcontroller::class, 'destroy']);
        Route::get('/show/{id}', [Appartmentcontroller::class, 'show']);



    }
);

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => 'jwt.auth'

    ],
    function () {
        Route::get('/index', [AdminController::class, 'index']);
        Route::get('/approveAppartment/{id}', [AdminController::class, 'approveAppartment']);
        Route::get('/rejectAppartment/{id}', [AdminController::class, 'rejectAppartment']);
    }
);


// Route::apiResource('tasks', TaskController::class);
