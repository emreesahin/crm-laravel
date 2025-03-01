<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Validator;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    //Company
    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::put('/update-company/{id}', [CompanyController::class, 'updateCompany']);
    Route::get('/companies', [CompanyController::class, 'getAllCompanies']);
    Route::get('/company/{id}', [CompanyController::class, 'getCompany']);
    Route::delete('/delete-company/{id}', [CompanyController::class, 'deleteCompany']);

    //Customer
    Route::post('/create-customer', [CustomerController::class, 'createCustomer']);
    Route::put('/update-customer/{id}', [CustomerController::class, 'updateCustomer']);
    Route::get('/customers', [CustomerController::class, 'getAllCustomers']);
    Route::get('/customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::delete('/customer/{id}', [CustomerController::class, 'deleteCustomer']);

    //Order
    Route::post('/post-order', [OrderController::class, 'postOrder']);
    Route::get('/order/{id}', [OrderController::class, 'getOrder']);
    Route::get('/step-order/{id}', [OrderController::class, 'getStepNotes']);
    Route::put('/update-order/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/delete-order/{id}', [OrderController::class, 'deleteOrder']);
    Route::get('orders-count', [OrderController::class, 'getOrdersCount']);
    Route::post('/step-notes-order/{id}', [OrderController::class, 'addStepNote']);
    // Route::delete('/step-order/{id}', [OrderController::class, 'deleteStepNote']);
    // Route::put('/step-order/{id}', [OrderController::class, 'updateStepNote']);
});


Route::get('/test', function () {
    return response()->json(['message' => 'Api is working']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
