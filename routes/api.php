<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;


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
Route::post('/access/reset-password',[UserController::class, 'reset_password']);

//products category
Route::post('/product/list-category',[ProductController::class, 'list_category']);
Route::post('/product/list-category-by-id',[ProductController::class, 'list_category_by_id']);

//products attribute
Route::post('/product/list-attribute',[ProductController::class, 'list_attribute']);
Route::post('/product/list-attribute-by-id',[ProductController::class, 'list_attribute_by_id']);

//products
Route::post('/product/list-product',[ProductController::class, 'list_product']);
Route::post('/product/list-product-by-id',[ProductController::class, 'list_product_by_id']);



//Protected Routes
Route::group(["middleware" => ['auth:sanctum']], function() {
    //access control
    Route::post('/access/admin-user-register',[UserController::class, 'register']);
    Route::post('/access/logout',[UserController::class, 'logout']);
    Route::post('/access/edit-user',[UserController::class, 'edit_user']);
    Route::post('/access/update-password',[UserController::class, 'update_password']);
    Route::post('/access/add-address',[UserController::class, 'add_address']);
    Route::post('/access/edit-address',[UserController::class, 'edit_address']);
    Route::post('/access/list-address',[UserController::class, 'list_address']);
    Route::post('/access/list-users',[UserController::class, 'list_users']);
    Route::post('/access/delete-user',[UserController::class, 'delete_user']);
    Route::post('/ops/add-image',[UserController::class, 'add_image']);
    Route::post('/ops/delete-image',[UserController::class, 'delete_image']);



    //products Category
    Route::post('/product/add-category',[ProductController::class, 'add_category']);
    Route::post('/product/edit-category',[ProductController::class, 'edit_category']);
    Route::post('/product/delete-category',[ProductController::class, 'delete_category']);

    //products Attribute
    Route::post('/product/add-attribute',[ProductController::class, 'add_attribute']);
    Route::post('/product/edit-attribute',[ProductController::class, 'edit_attribute']);
    Route::post('/product/delete-attribute',[ProductController::class, 'delete_attribute']);

    //Product add
    Route::post('/product/add-product',[ProductController::class, 'add_product']);
    Route::post('/product/edit-product',[ProductController::class, 'edit_product']);

    Route::post('/product/add-product-image',[ProductController::class, 'add_product_image']);
    Route::post('/product/delete-product-image',[ProductController::class, 'delete_product_image']);

    Route::post('/product/add-product-attribute',[ProductController::class, 'add_product_attribute']);
    Route::post('/product/delete-product-attribute',[ProductController::class, 'delete_product_attribute']);




});


