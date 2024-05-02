<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConceptController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\ItemSalesController;
use App\Http\Controllers\PaymentDetailsController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/conceptlist', [ConceptController::class, 'ConceptList']);

Route::get('/users', [UserController::class, 'list']);


Route::get('/averageSalesPerCustomer',[AuditController::class, 'averageSalesPerCustomer']);

//branch
Route::get('/branch', [BranchController::class, 'branchList']);

//item sales controller
Route::get('/itemSales', [ItemSalesController::class,'CalculateAverageSalesPerDay']);
Route::get('/totalSales', [ItemSalesController::class, 'calculateTotalSales']);
Route::get('/dailysalesreport', [ItemSalesController::class, 'DailySalesReport']);



//header Api
Route::get('/averageTx', [HeaderController::class, 'CalculateAverageTxPerDay']);
Route::get('/totalSalesPerDay', [HeaderController::class, 'TotalSalesPerDay']);

//payment details api

Route::get('/paymentDetails', [PaymentDetailsController::class,'PaymentDetails']);


//category report
Route::get('/productMix',[ItemSalesController::class,'ProductMix']);




//category list

Route::get('/categorylist', [CategoryController::class, 'categoryList']);