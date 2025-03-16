<?php

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

Route::controller(\Security\UserController::class)->group(function () {
    Route::post('auth', 'store_auth');
});

Route::group(['prefix' => 'master', 'as' => 'master', 'middleware' => 'appauth'], function () {
    Route::controller(\Master\BahanController::class)->group(function () {
        Route::get('bahan', 'show1');
        Route::get('bahan/all', 'show2');
        Route::get('bahan/get', 'get');
        Route::get('bahan/get-allref', 'getAllRef');
        Route::delete('bahan', 'destroy');
        Route::post('bahan', 'store');
    });
    Route::controller(\Master\BahanSatuanController::class)->group(function () {
        Route::get('bahan-satuan', 'show1');
        Route::get('bahan-satuan/all', 'show2');
        Route::get('bahan-satuan/get', 'get');
        Route::delete('bahan-satuan', 'destroy');
        Route::post('bahan-satuan', 'store');
    });
    Route::controller(\Master\SatuanController::class)->group(function () {
        Route::get('satuan', 'show1');
        Route::get('satuan/all', 'show2');
        Route::get('satuan/get', 'get');
        Route::delete('satuan', 'destroy');
        Route::post('satuan', 'store');
    });
    Route::controller(\Master\PajakController::class)->group(function () {
        Route::get('pajak', 'show1');
        Route::get('pajak/all', 'show2');
        Route::get('pajak/get', 'get');
        Route::delete('pajak', 'destroy');
        Route::post('pajak', 'store');
    });
    Route::controller(\Master\RakController::class)->group(function () {
        Route::get('rak', 'show1');
        Route::get('rak/all', 'show2');
        Route::get('rak/get', 'get');
        Route::delete('rak', 'destroy');
        Route::post('rak', 'store');
    });
    Route::controller(\Master\LevelBahanController::class)->group(function () {
        Route::get('level-bahan', 'show1');
        Route::get('level-bahan/all', 'show2');
        Route::get('level-bahan/get', 'get');
        Route::delete('level-bahan', 'destroy');
        Route::post('level-bahan', 'store');
    });
    Route::controller(\Master\GrupBahanController::class)->group(function () {
        Route::get('grup-bahan', 'show1');
        Route::get('grup-bahan/all', 'show2');
        Route::get('grup-bahan/get', 'get');
        Route::delete('grup-bahan', 'destroy');
        Route::post('grup-bahan', 'store');
    });
    Route::controller(\Master\TipeBahanController::class)->group(function () {
        Route::get('tipe-bahan', 'show1');
        Route::get('tipe-bahan/all', 'show2');
        Route::get('tipe-bahan/get', 'get');
        Route::delete('tipe-bahan', 'destroy');
        Route::post('tipe-bahan', 'store');
    });
    Route::controller(\Master\GrupPerformController::class)->group(function () {
        Route::get('grup-perform', 'show1');
        Route::get('grup-perform/all', 'show2');
        Route::get('grup-perform/get', 'get');
        Route::delete('grup-perform', 'destroy');
        Route::post('grup-perform', 'store');
    });
    Route::controller(\Master\BahanKlpController::class)->group(function () {
        Route::get('bahan-klp', 'show1');
        Route::get('bahan-klp/all', 'show2');
        Route::get('bahan-klp/get', 'get');
        Route::delete('bahan-klp', 'destroy');
        Route::post('bahan-klp', 'store');
    });
    Route::controller(\Master\HargaJualController::class)->group(function () {
        Route::get('harga-jual', 'show1');
        Route::get('harga-jual/all', 'show2');
        Route::get('harga-jual/get', 'get');
        Route::delete('harga-jual', 'destroy');
        Route::post('harga-jual', 'store');
    });
    Route::controller(\Master\SupplierController::class)->group(function () {
        Route::get('supplier', 'show1');
        Route::get('supplier/all', 'show2');
        Route::get('supplier/get', 'get');
        Route::delete('supplier', 'destroy');
        Route::post('supplier', 'store');
    });
});

Route::group(['prefix' => 'tools', 'as' => 'tools', 'middleware' => 'appauth'], function () {
    Route::controller(\Tools\DocNoController::class)->group(function () {
        Route::get('docno', 'show1');
        Route::get('docno/all', 'show2');
        Route::get('docno/get', 'get');
        Route::get('docno/get-allref', 'getAllRef');
        Route::get('docno/get-docno', 'getDocNo');
        Route::get('docno/get-docnotest', 'getDocNoTest');
        Route::post('docno/set-docno', 'setDocNo');
        Route::delete('docno', 'destroy');
        Route::post('docno', 'store');
    });
});

Route::group(['prefix' => 'purchase', 'as' => 'purchase', 'middleware' => 'appauth'], function () {
    Route::controller(\Purchase\PurchaseRequestController::class)->group(function () {
        Route::get('pr', 'show1');
        Route::get('pr/all', 'show2');
        Route::get('pr/get', 'get');
        Route::get('pr/get-allref', 'getAllRef');
        Route::delete('pr', 'destroy');
        Route::post('pr', 'store');
    });
    Route::controller(\Purchase\PurchaseOrderController::class)->group(function () {
        Route::get('po', 'show1');
        Route::get('po/all', 'show2');
        Route::get('po/get', 'get');
        Route::get('po/get-listpr', 'getListPR');
        Route::post('po/get-itempr', 'getItemPR');
        Route::get('po/get-allref', 'getAllRef');
        Route::delete('po', 'destroy');
        Route::post('po', 'store');
    });
    Route::controller(\Purchase\GoodsReceiptController::class)->group(function () {
        Route::get('gr', 'show1');
        Route::get('gr/all', 'show2');
        Route::get('gr/get', 'get');
        Route::get('gr/get-listpo', 'getListPO');
        Route::post('gr/get-itempo', 'getItemPO');
        Route::get('gr/get-allref', 'getAllRef');
        Route::delete('gr', 'destroy');
        Route::post('gr', 'store');
    });
});
