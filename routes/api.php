<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Operational;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderManagement;



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

//ORDER MANAGEMENT
//enquiries
Route::post('/order/send-enquiry',[OrderManagement::class, 'send_enquiry']);




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

    //operational Routes
    Route::post('/ops/add-image',[Operational::class, 'add_image']);
    Route::post('/ops/delete-image',[Operational::class, 'delete_image']);



    //products Category
    Route::post('/product/add-category',[ProductController::class, 'add_category']);
    Route::post('/product/edit-category',[ProductController::class, 'edit_category']);
    Route::post('/product/delete-category',[ProductController::class, 'delete_category']);

    //products Attribute
    Route::post('/product/add-attribute',[ProductController::class, 'add_attribute']);
    Route::post('/product/edit-attribute',[ProductController::class, 'edit_attribute']);
    Route::post('/product/delete-attribute',[ProductController::class, 'delete_attribute']);

    //add stocks
    Route::post('/product/add-stock',[ProductController::class, 'add_stock']);
    Route::post('/product/edit-stock',[ProductController::class, 'edit_stock']);
    Route::post('/product/delete-stock',[ProductController::class, 'delete_stock']);

    //Product add
    Route::post('/product/add-product',[ProductController::class, 'add_product']);
    Route::post('/product/add-product-variant',[ProductController::class, 'add_product_variant']);
    Route::post('/product/edit-product',[ProductController::class, 'edit_product']);
    Route::post('/product/delete-product',[ProductController::class, 'delete_product']);
    Route::post('/product/list-product-by-user',[ProductController::class, 'list_product_by_user']);


    Route::post('/product/add-product-image',[ProductController::class, 'add_product_image']);
    Route::post('/product/delete-product-image',[ProductController::class, 'delete_product_image']);

    Route::post('/product/add-product-attribute',[ProductController::class, 'add_product_attribute']);
    Route::post('/product/delete-product-attribute',[ProductController::class, 'delete_product_attribute']);

    Route::post('/product/add-product-category',[ProductController::class, 'add_product_category']);
    Route::post('/product/delete-product-category',[ProductController::class, 'delete_product_category']);


    //product supporting files
    //add supporting file
    Route::post('/product/add-product-supporting-file',[ProductController::class, 'add_product_supporting_file']);
    Route::post('/product/delete-product-supporting-file',[ProductController::class, 'delete_product_supporting_file']);

    //Wishlist
    //add to wishlist
    Route::post('/wishlist/add-to-wishlist',[WishlistController::class, 'add_to_wishlist']);
    Route::post('/wishlist/delete-from-wishlist',[WishlistController::class, 'remove_from_wishlisth']);
    Route::post('/wishlist/delete-all-from-wishlist',[WishlistController::class, 'remove_all_from_wishlisth']);
    Route::post('/wishlist/list-wishlist-items',[WishlistController::class, 'list_wishlist_items']);

    //Cart
    //add to cart
    Route::post('/cart/add-to-cart',[CartController::class, 'add_to_cart']);
    Route::post('/cart/delete-from-cart',[CartController::class, 'remove_from_cart']);
    Route::post('/cart/delete-all-from-cart',[CartController::class, 'remove_all_from_cart']);
    Route::post('/cart/list-cart-items',[CartController::class, 'list_cart_items']);

    //ORDER MANAGEMENT
    //enquiries
    Route::post('/order/update-enquiry',[OrderManagement::class, 'update_enquiry']);
    Route::post('/order/send-enquiry-mail',[OrderManagement::class, 'send_mail']);


});


