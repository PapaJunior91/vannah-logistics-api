<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\ClientController;
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


Route::post('branches', [BranchController::class, 'createBranch']);
Route::get('branches', [BranchController::class, 'getBranches']);

Route::post('couriers', [CourierController::class, 'createCourier']);
Route::get('couriers', [CourierController::class, 'getCouriers']);

Route::post('clients', [ClientController::class, 'createClient']);
Route::get('clients', [ClientController::class, 'getClients']);

Route::post('deliveries', [DeliveryController::class, 'createDelivery']);
Route::get('deliveries', [DeliveryController::class, 'getDeliveries']);

Route::post('users', [UserController::class, 'createUser']);
Route::get('users', [UserController::class, 'getUsers']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
