<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::post('/update-company/{id}', [CompanyController::class, 'updateCompany']);
    Route::get('/companies', [CompanyController::class, 'getAllCompanies']);
    Route::get('/company/{id}', [CompanyController::class, 'getCompany']);
    Route::delete('/company/{id}', [CompanyController::class, 'deleteCompany']);
    Route::post('/create-customer', [CustomerController::class, 'createCustomer']);
    // Route::post('/update-customer/{id}', [CustomerController::class, 'updateCustomer']);
    // Route::get('/customers', [CustomerController::class, 'getAllCustomers']);
    // Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    // Route::delete('/customer/{id}', [CustomerController::class, 'deleteCustomer']);
    Route::post('/create-order', [OrderController::class, 'createOrder']);

});


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
