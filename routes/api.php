<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UsersController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('get/users',[UsersController::class,'index']);
Route::get('get/users/{id}',[UsersController::class,'getuser']);
