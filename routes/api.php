<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Access Control
Route::post('/access/user-register',[UserController::class, 'register']);
Route::post('/access/login',[UserController::class, 'login']);
Route::post('/access/forgot-password',[UserController::class, 'forgot_password']);


//Protected Routes
Route::group(["middleware" => ['auth:sanctum']], function() {
    //access control
    Route::post('/access/admin-user-register',[UserController::class, 'register']);
    Route::post('/access/logout',[UserController::class, 'logout']);
    Route::post('/access/edit-user',[UserController::class, 'edit_user']);
    Route::post('/access/update-password',[UserController::class, 'update_password']);
});


