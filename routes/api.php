<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Appartmentcontroller;
use App\Http\Controllers\availableApartmentsController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OwnerOrderController;
use App\Http\Controllers\RatingController;
use App\Models\Appartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Termwind\Components\Raw;

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::delete('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/availableApartments', [availableApartmentsController::class, 'index']);
Route::get('/filter', [availableApartmentsController::class, 'filter']);

Route::group(
    [
        'prefix' => 'admin',
        'middleware' => ['auth:sanctum', 'admin'],
    ],
    function () {
        Route::post('/approveUser/{id}', [AdminController::class, 'approveUser']);
        Route::post('/rejectUser/{id}', [AdminController::class, 'rejectUser']);
        Route::post('/approveAppartment/{id}', [AdminController::class, 'approveAppartment']);
        Route::post('/rejectAppartment/{id}', [AdminController::class, 'rejectAppartment']);

        Route::get('/allUsers', [AdminController::class, 'allUsers']);
        Route::get('/allApartments', [AdminController::class, 'allApartments']);
        Route::get('/allActiveUsers', [AdminController::class, 'allActiveUsers']);
        Route::get('/allRejectedUsers', [AdminController::class, 'allRejectedUsers']);
        Route::get('/allPendingUsers', [AdminController::class, 'allPendingUsers']);
        Route::get('/allApprovedApartments', [AdminController::class, 'allApprovedApartments']);
    }    
);

Route::group(
    [
        'prefix' => 'appartment',
        'middleware' => 'auth:sanctum',
    ],
    function () {
        Route::post('/create', [Appartmentcontroller::class, 'store']);
        Route::post('/update/{id}', [Appartmentcontroller::class, 'update']);
        Route::delete('/destroy/{id}', [Appartmentcontroller::class, 'destroy']);
        Route::get('/show/{id}', [Appartmentcontroller::class, 'show']);
        Route::delete('/images/{id}/index/{index}', [Appartmentcontroller::class, 'deleteImage']);   
    }    
);
// Orders for User's Booking
Route::group(['prefix' => 'order/user', 'middleware' => 'auth:sanctum'], function () {
    
    Route::post('/store', [App\Http\Controllers\OrderController::class, 'store']);
    Route::post('/update/{id}', [App\Http\Controllers\OrderController::class, 'update']);
    Route::get('/index', [OrderController::class, 'index']);
    Route::get('/show/{id}', [OrderController::class, 'show']);
    Route::delete('/cancle/{id}' , [OrderController::class, 'destroy']);
    Route::get('/unavailable_dates/{appartmentId}', [OrderController::class, 'unavailableDates']);
    Route::post('/rate/{id}', [OrderController::class, 'rating']);
    }
);

// Orders for Owner's Appartments
Route::group(['prefix' => 'order/owner', 'middleware' => 'auth:sanctum'], function () {
    
    Route::get('/orders', [OwnerOrderController::class, 'index']);
    Route::get('/show/{id}', [OwnerOrderController::class, 'show']);
    Route::post('/reject/{id}' , [OwnerOrderController::class, 'reject']);
    Route::post('/approve/{id}' , [OwnerOrderController::class, 'approve']);
    Route::post('/approve_update/{id}',[OwnerOrderController::class, 'approveModification']);
    Route::post('/reject_update/{id}',[OwnerOrderController::class, 'rejectModification']);

    }
);

// favorites routes
Route::group(['prefix' => '/favorites', 'middleware' => 'auth:sanctum'], function () {
 
    // toggle favorite (add/remove)
    Route::post('/toggle/{appartmentId}', [FavoriteController::class, 'toggle']);
    Route::get('/index', [FavoriteController::class, 'index']);
    }
); 

Route::group(['prefix' => '/rating', 'middleware' => 'auth:sanctum'], function () {
 
    // rating routes
    Route::post('/store/{orderId}', [RatingController::class, 'store']);
    Route::post('/update/{ratingId}', [RatingController::class, 'update']);
    Route::get('/index/{id}', [RatingController::class, 'index']);
    }
); 


