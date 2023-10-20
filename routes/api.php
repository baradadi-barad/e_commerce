<?php

// use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\SellProductItemController;
use App\Http\Controllers\Api\ReturnProductItemController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WarehouseManagementController as Warehouse;
use App\Http\Controllers\Api\ManufacturerManagementController as Manufacturer;

use App\Http\Controllers\Api\PurchaseManagementController as Purchase;



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

// Authentication
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/recheck-user/{id}', [App\Http\Controllers\Api\AuthController::class, 'checkUpdatedUserValue']);



// Dashboard
Route::post('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);
Route::post('/dashboardajax', [App\Http\Controllers\Api\DashboardController::class, 'indexAjax']);

// My_Profile
Route::group(['prefix' => 'my-profile'], function () {
    Route::post('/{id}', [App\Http\Controllers\Api\UserController::class, 'userprofile']);
    Route::post('/update/{id}', [App\Http\Controllers\Api\UserController::class, 'userupdate']);
});

// Categories
Route::group(['prefix' => 'categories'], function () {
    Route::post('/', [App\Http\Controllers\Api\CategoriesController::class, 'index']);
    Route::post('/save', [App\Http\Controllers\Api\CategoriesController::class, 'store']);
    Route::post('/update/{id}', [App\Http\Controllers\Api\CategoriesController::class, 'update']);
    Route::post('/delete/{id}', [App\Http\Controllers\Api\CategoriesController::class, 'destroy']);
});

// Products
Route::group(['prefix' => 'products'], function () {

    Route::post('/', [App\Http\Controllers\Api\ProductsController::class, 'index']);
    Route::post('/save', [App\Http\Controllers\Api\ProductsController::class, 'store']);
    Route::post('/fetch-categories', [ProductsController::class, 'fetchCategory']);
    Route::post('/update/{id}', [App\Http\Controllers\Api\ProductsController::class, 'update']);
    Route::post('/delete/{id}', [App\Http\Controllers\Api\ProductsController::class, 'destroy']);
    Route::post('/update-price', [App\Http\Controllers\Api\ProductsController::class, 'updatePrice']);
});


// Stock Detail

Route::group(['prefix' => 'product/stock'], function () {
    Route::post('/store', [App\Http\Controllers\Api\ProductsController::class, 'createstock']);
    Route::post('/edit/{id}', [App\Http\Controllers\Api\ProductsController::class, 'editstock']);
    Route::post('/update/{id}', [App\Http\Controllers\Api\ProductsController::class, 'updatestock']);
    Route::post('/getproductwisestock/{id}', [App\Http\Controllers\Api\ProductsController::class, 'getProductStocks']);
    Route::post('/destroy/{id}', [App\Http\Controllers\Api\ProductsController::class, 'destroystock']);
    Route::post('/generateBarcode/{id}', [App\Http\Controllers\Api\ProductsController::class, 'generateBarcode']);
});

// Sell Product Item
Route::group(['prefix' => 'sellProductItem'], function () {
    Route::post('/sell-item', [App\Http\Controllers\Api\SellProductItemController::class, 'sellItem']);
    Route::post('/add-product-requried-data', [App\Http\Controllers\Api\SellProductItemController::class, 'requiredDataForAddingProduct']);
    Route::post('/sell-item-list', [App\Http\Controllers\Api\SellProductItemController::class, 'sellItemList']);


});

// Return Product Item
Route::group(['prefix' => 'returnProductItem'], function () {
    
    Route::post('/return-item', [App\Http\Controllers\Api\ReturnProductItemController::class, 'returnItem']);
    Route::post('/return-item-list', [App\Http\Controllers\Api\ReturnProductItemController::class, 'returnItemLists']);
    Route::post('/fetch-product-data/{id}', [App\Http\Controllers\Api\ReturnProductItemController::class, 'productData']);

});

// Warehouse
Route::group(['prefix'=>'warehouse'], function() {

    Route::post('/', [Warehouse::class, 'index']);
    Route::post('/store', [Warehouse::class, 'store']);
    Route::post('/update/{id}', [Warehouse::class, 'update']);
    Route::post('/delete/{id}', [Warehouse::class, 'destroy']);
    Route::post('/change-auto-status', [Warehouse::class, 'changeAutoStatus']);
});

// Manufacturer
Route::group(['prefix'=>'manufacturer'], function() {
    
    Route::post('/', [Manufacturer::class, 'index']);
    Route::post('/store', [Manufacturer::class, 'store']);
    // Route::post('/update/{id}', [Manufacturer::class, 'update']);
    // Route::post('/delete/{id}', [Manufacturer::class, 'destroy']);
    Route::post('/manufacture-data', [Manufacturer::class, 'manufactureData']);

});

// Purchase
Route::group(['prefix'=>'purchase'], function() {
    
    Route::post('/', [Purchase::class, 'index']);
    Route::post('/store', [Purchase::class, 'store']);
    Route::post('/update/{id}', [Purchase::class, 'update']);
    Route::post('/delete/{id}', [Purchase::class, 'destroy']);
    Route::post('/manufacture-data', [Purchase::class, 'manufactureData']);
    Route::post('/remaining-purchase-view', [Purchase::class, 'remainingView']);

});