<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\MemberController;


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
    Route::get('/step-notes-order/{id}', [OrderController::class, 'getStepNotes']);
    Route::put('/update-order/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('/delete-order/{id}', [OrderController::class, 'deleteOrder']);
    Route::get('orders-count', [OrderController::class, 'getOrdersCount']);
    Route::post('/step-notes-order/{id}', [OrderController::class, 'addStepNotes']);
    Route::put('/step-notes-order/{id}', [OrderController::class, 'updateStepNotes']);

    //Member
    Route::get('members', [MemberController::class, 'getMembers']);
    Route::get('members/{id}', [MemberController::class, 'getMembers']);
    Route::get('current-member', [MemberController::class, 'getCurrentMember']);
    Route::post('members/admin', [MemberController::class, 'createAdmin']);
    Route::post('members', [MemberController::class, 'createMember']);
    Route::put('members/{id}', [MemberController::class, 'updateMember']);
    Route::delete('members/{id}', [MemberController::class, 'deleteMember']);
    Route::get('members/{id}/summary', [MemberController::class, 'getMemberSummary']);


});


Route::get('/test', function () {
    return response()->json(['message' => 'Api is working']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
