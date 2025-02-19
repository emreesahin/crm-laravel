<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/create-company', [CompanyController::class, 'createCompany']);
    Route::post('/update-company/{id}', [CompanyController::class, 'updateCompany']);
    Route::get('/companies', [CompanyController::class, 'getAllCompanies']);
    Route::get('/company/{id}', [CompanyController::class, 'getCompany']);
    Route::delete('/company/{id}', [CompanyController::class, 'deleteCompany']);
});


Route::get('/test', function () {
    return response()->json(['message' => 'Hello World!']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
