<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\DeliveryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::post('login', [AuthController::class, 'login']);

Route::post('companies', [CompanyController::class, 'createCompany']);
Route::get('companies/{id}', [CompanyController::class, 'getCompany']);

Route::post('branches', [BranchController::class, 'createBranch']);
Route::get('branches', [BranchController::class, 'getBranches']);

Route::get('regions', [BranchController::class, 'getRegions']);

Route::post('couriers', [CourierController::class, 'createCourier']);
Route::get('couriers', [CourierController::class, 'getCouriers']);

Route::post('clients', [ClientController::class, 'createClient']);
Route::get('clients', [ClientController::class, 'getClients']);
Route::put('clients/{id}', [ClientController::class, 'updateClient']);
Route::put('archive-clients/{id}', [ClientController::class, 'archiveClient']);

Route::post('deliveries', [DeliveryController::class, 'createDelivery']);
Route::get('deliveries', [DeliveryController::class, 'getDeliveries']);
Route::get('filter-deliveries', [DeliveryController::class, 'filterDeliveries']);
Route::put('deliveries/{id}', [DeliveryController::class, 'updateDelivery']);
Route::put('archive-deliveries/{id}', [DeliveryController::class, 'archiveDelivery']);

Route::post('users', [UserController::class, 'createUser']);
Route::get('users', [UserController::class, 'getUsers']);

Route::get('total-income', [DeliveryController::class, 'getTotalIncome']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
