<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;

// Auth
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class,'show']);
Route::get('/categories', [ProductCategoryController::class,'index']);

// CUSTOMER
Route::middleware(['auth:api','role:CUSTOMER'])->group(function () {
     // Profile
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Addresses
    Route::get('/addresses', [App\Http\Controllers\AddressController::class, 'index']);
    Route::post('/addresses', [App\Http\Controllers\AddressController::class, 'add']);
    Route::put('/addresses/{id}', [App\Http\Controllers\AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [App\Http\Controllers\AddressController::class, 'delete']);
    Route::put('/addresses/default/{id}', [App\Http\Controllers\AddressController::class, 'setDefault']);
    Route::get('/addresses/default', [App\Http\Controllers\AddressController::class, 'getDefault']);

    // Carts
    Route::get('/cart', [App\Http\Controllers\CartController::class, 'index']);
    Route::post('/cart', [App\Http\Controllers\CartController::class, 'add']);
    Route::put('/cart/{id}', [App\Http\Controllers\CartController::class, 'update']);
    Route::delete('/cart/{id}', [App\Http\Controllers\CartController::class, 'delete']);

    // orders
    Route::post('/checkout/cart', [CheckOutController::class, 'checkoutCart']);
    Route::post('/checkout/buy-now', [CheckOutController::class, 'buyNow']);
    Route::get('/orders', [CheckOutController::class, 'history']);
    Route::get('/orders/{id}', [CheckOutController::class, 'detail']);
});

//WAREHOUSE
Route::middleware(['auth:api','role:WAREHOUSE'])->group(function () {
    //product
    Route::post('/products', [ProductController::class,'create']);
    Route::post('/products/{id}', [ProductController::class,'update']);
    Route::delete('/products/{id}', [ProductController::class,'delete']);

    //categoery
    Route::post('/categories', [ProductCategoryController::class, 'create']);
    Route::put('/categories/{id}', [ProductCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [ProductCategoryController::class, 'delete']);
});

//ADMIN
Route::middleware(['auth:api', 'role:ADMIN'])->prefix('/admin')->group(function () {
    Route::get('/overview', [AdminController::class, 'overview']);
    Route::get('/revenue/month', [AdminController::class, 'revenueByMonth']);
    Route::get('/revenue/day', [AdminController::class, 'revenueByDay']);
    Route::get('/revenue/week', [AdminController::class, 'revenueByWeek']);
    Route::get('/revenue/payment', [AdminController::class, 'revenueByPayment']);
    Route::get('/orders/status-rate', [AdminController::class, 'orderStatusRate']);
    Route::get('/customers/top', [AdminController::class, 'topCustomers']);
    Route::get('/products/best-selling', [AdminController::class, 'bestSellingProducts']);
    Route::get('/revenue/year/{year}', [AdminController::class, 'revenueByMonthOfYear']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::get('/users/role/{role}', [UserController::class, 'getByRole']);
});

Route::middleware('auth:api')->group(function () {
    Route::get('/chat/{userId}', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'send']);
});
