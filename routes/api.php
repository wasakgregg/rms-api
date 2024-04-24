<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\ItemSalesController;
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

Route::get('/users', [UserController::class, 'list']);


Route::get('/averageSalesPerCustomer',[AuditController::class, 'averageSalesPerCustomer']);av


Route::get('/branch', [BranchController::class, 'branchList']);


Route::get('/itemSales', [ItemSalesController::class,'CalculateAverageSalesPerDay']);
Route::get('/totalSales', [ItemSalesController::class, 'calculateTotalSales']);


Route::get('/averageTx', [HeaderController::class, 'CalculateAverageTxPerDay']);
