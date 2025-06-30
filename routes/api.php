<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CuisineController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\FoodTruckController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/cuisines/grouped-by-category', [CuisineController::class, 'groupedByCategory']);

Route::get('/cuisines/filter', [CuisineController::class, 'filter']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/dishes', [DishController::class, 'index']);
Route::get('/cuisines/nearest', [CuisineController::class, 'getNearest']);

// New Food Truck Routes
Route::get('/food-trucks/filter', [FoodTruckController::class, 'filter']);
Route::get('/food-trucks/nearest', [FoodTruckController::class, 'getNearest']);