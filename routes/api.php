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
        Route::get('bahan/is-bahan', 'isBahan');
        Route::get('bahan/get-allref', 'getAllRef');
        Route::get('bahan/get-kodebaru', 'getKodeBahanBaru');
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
        Route::get('supplier/is-supplier', 'isSupplier');
        Route::get('supplier/get-allref', 'getAllRef');
        Route::delete('supplier', 'destroy');
        Route::post('supplier', 'store');
    });
    Route::controller(\Master\LokasiController::class)->group(function () {
        Route::get('lokasi', 'show1');
        Route::get('lokasi/all', 'show2');
        Route::get('lokasi/get', 'get');
        Route::delete('lokasi', 'destroy');
        Route::post('lokasi', 'store');
    });
    Route::controller(\Master\PaketController::class)->group(function () {
        Route::get('paket', 'show1');
        Route::get('paket/all', 'show2');
        Route::get('paket/get', 'get');
        Route::delete('paket', 'destroy');
        Route::post('paket', 'store');
    });
    Route::controller(\Master\BayarController::class)->group(function () {
        Route::get('bayar', 'show1');
        Route::get('bayar/all', 'show2');
        Route::get('bayar/get', 'get');
        Route::delete('bayar', 'destroy');
        Route::post('bayar', 'store');
    });
    Route::controller(\Master\CustomerController::class)->group(function () {
        Route::get('customer', 'show1');
        Route::get('customer/all', 'show2');
        Route::get('customer/get', 'get');
        Route::get('customer/is-customer', 'isCustomer');
        Route::get('customer/get-customer', 'getCustomer');
        Route::get('customer/get-allref', 'getAllRef');
        Route::delete('customer', 'destroy');
        Route::post('customer', 'store');
    });
    Route::controller(\Master\ResepController::class)->group(function () {
        Route::get('resep', 'show1');
        Route::get('resep/all', 'show2');
        Route::get('resep/get', 'get');
        Route::get('resep/is-bahan', 'isBahan');
        Route::get('resep/get-allref', 'getAllRef');
        Route::get('resep/get-kodebaru', 'getKodeBahanBaru');
        Route::delete('resep', 'destroy');
        Route::post('resep', 'store');
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
    Route::controller(\Tools\UtilityController::class)->group(function () {
        Route::get('util', 'show1');
        Route::get('util/all', 'show2');
        Route::get('util/get', 'get');
        Route::get('util/get-status-bytglopname', 'getStatusByTglOpname');
        Route::get('util/get-fiscaldate-status', 'getPostingDateStatus');
        Route::get('util/get-kodeproject', 'getKodeProjectByLokasi');
        Route::get('util/get-hakmenu', 'getHakMenu');
        Route::get('util/get-notif', 'getNotifTrans');
        Route::get('util/get-notif-list', 'getNotifTransList');
        Route::get('util/get-newversion', 'getNewVersion');
        Route::get('util/get-login-access', 'getLoginAccess');
        Route::get('util/get-objitem', 'getObjectItem');
        Route::delete('util', 'destroy');
        Route::post('util', 'store');
    });
});

Route::group(['prefix' => 'purchase', 'as' => 'purchase', 'middleware' => 'appauth'], function () {
    Route::controller(\Purchase\PurchaseRequestController::class)->group(function () {
        Route::get('pr', 'show1');
        Route::get('pr/all', 'show2');
        Route::get('pr/get', 'get');
        Route::get('pr/get-batal', 'getBatal');
        Route::get('pr/get-linkdata', 'getLinkData');
        Route::get('pr/get-allref', 'getAllRef');
        Route::delete('pr', 'destroy');
        Route::post('pr/set-batal', 'setBatal');
        Route::post('pr', 'store');
    });
    Route::controller(\Purchase\PurchaseOrderController::class)->group(function () {
        Route::get('po', 'show1');
        Route::get('po/all', 'show2');
        Route::get('po/get', 'get');
        Route::get('po/get-batal', 'getBatal');
        Route::get('po/get-linkdata', 'getLinkData');
        Route::get('po/get-listpr', 'getListPR');
        Route::post('po/get-list-itempr', 'getListItemPR');
        Route::post('po/get-itempr', 'getItemPR');
        Route::get('po/get-allref', 'getAllRef');
        Route::delete('po', 'destroy');
        Route::post('po/set-batal', 'setBatal');
        Route::post('po', 'store');
    });
    Route::controller(\Purchase\GoodsReceiptController::class)->group(function () {
        Route::get('gr', 'show1');
        Route::get('gr/all', 'show2');
        Route::get('gr/get', 'get');
        Route::get('gr/get-batal', 'getBatal');
        Route::get('gr/get-linkdata', 'getLinkData');
        Route::get('gr/get-listpo', 'getListPO');
        Route::post('gr/get-itempo', 'getItemPO');
        Route::get('gr/get-allref', 'getAllRef');
        Route::delete('gr', 'destroy');
        Route::post('gr/set-batal', 'setBatal');
        Route::post('gr', 'store');
    });
    Route::controller(\Purchase\PurchaseInvoiceController::class)->group(function () {
        Route::get('pi', 'show1');
        Route::get('pi/all', 'show2');
        Route::get('pi/get', 'get');
        Route::get('pi/get-batal', 'getBatal');
        Route::get('pi/get-linkdata', 'getLinkData');
        Route::get('pi/get-listgr', 'getListGR');
        Route::post('pi/get-itemgr', 'getItemGR');
        Route::get('pi/get-invdp', 'getInvoiceDPRef');
        Route::get('pi/get-allref', 'getAllRef');
        Route::delete('pi', 'destroy');
        Route::post('pi/set-batal', 'setBatal');
        Route::post('pi', 'store');
    });
    Route::controller(\Purchase\PurchaseReturnController::class)->group(function () {
        Route::get('pretur', 'show1');
        Route::get('pretur/all', 'show2');
        Route::get('pretur/get', 'get');
        Route::get('pretur/get-batal', 'getBatal');
        Route::post('pretur/get-itempi', 'getItemPI');
        Route::get('pretur/get-allref', 'getAllRef');
        Route::delete('pretur', 'destroy');
        Route::post('pretur/set-batal', 'setBatal');
        Route::post('pretur', 'store');
    });
});

Route::group(['prefix' => 'inventory', 'as' => 'inventory', 'middleware' => 'appauth'], function () {
    Route::controller(\Inventory\StockTransferController::class)->group(function () {
        Route::get('st', 'show1');
        Route::get('st/all', 'show2');
        Route::get('st/get', 'get');
        Route::get('st/get-batal', 'getBatal');
        Route::get('st/get-linkdata', 'getLinkData');
        Route::get('st/get-allref', 'getAllRef');
        Route::delete('st', 'destroy');
        Route::post('st/set-batal', 'setBatal');
        Route::post('st', 'store');
    });
    Route::controller(\Inventory\StockTransferSendController::class)->group(function () {
        Route::get('tsend', 'show1');
        Route::get('tsend/all', 'show2');
        Route::get('tsend/get', 'get');
        Route::get('tsend/get-batal', 'getBatal');
        Route::get('tsend/get-linkdata', 'getLinkData');
        Route::get('tsend/get-list-request', 'getListRequest');
        Route::post('tsend/get-item-request', 'getItemRequest');
        Route::get('tsend/get-allref', 'getAllRef');
        Route::post('tsend/get-stok-status', 'getStockStatus');
        Route::delete('tsend', 'destroy');
        Route::post('tsend/set-batal', 'setBatal');
        Route::post('tsend', 'store');
    });
    Route::controller(\Inventory\StockTransferReceiveController::class)->group(function () {
        Route::get('treceive', 'show1');
        Route::get('treceive/all', 'show2');
        Route::get('treceive/get', 'get');
        Route::get('treceive/get-batal', 'getBatal');
        Route::get('treceive/get-list-transfer', 'getListTransfer');
        Route::post('treceive/get-item-transfer', 'getItemTransfer');
        Route::get('treceive/get-allref', 'getAllRef');
        Route::delete('treceive', 'destroy');
        Route::post('treceive/set-batal', 'setBatal');
        Route::post('treceive', 'store');
    });
    Route::controller(\Inventory\PembuanganController::class)->group(function () {
        Route::get('buang', 'show1');
        Route::get('buang/all', 'show2');
        Route::get('buang/get', 'get');
        Route::get('buang/get-batal', 'getBatal');
        Route::get('buang/get-approved', 'getApproved');
        Route::get('buang/get-allref', 'getAllRef');
        Route::delete('buang', 'destroy');
        Route::post('buang/set-batal', 'setBatal');
        Route::post('buang/set-approved', 'setApproved');
        Route::post('buang', 'store');
    });
    Route::controller(\Inventory\KoreksiStokController::class)->group(function () {
        Route::get('koreksi', 'show1');
        Route::get('koreksi/all', 'show2');
        Route::get('koreksi/get', 'get');
        Route::get('koreksi/get-batal', 'getBatal');
        Route::get('koreksi/get-approved', 'getApproved');
        Route::get('koreksi/get-listopname', 'getListOpname');
        Route::post('koreksi/get-list-itemopname', 'getListItemOpname');
        Route::post('koreksi/get-itemopname', 'getItemOpname');
        Route::get('koreksi/get-allref', 'getAllRef');
        Route::delete('koreksi', 'destroy');
        Route::post('koreksi/set-batal', 'setBatal');
        Route::post('koreksi/set-approved', 'setApproved');
        Route::post('koreksi', 'store');
    });
});

Route::group(['prefix' => 'produksi', 'as' => 'produksi', 'middleware' => 'appauth'], function () {
    Route::controller(\Produksi\PraProduksiController::class)->group(function () {
        Route::get('pra-produksi', 'show1');
        Route::get('pra-produksi/all', 'show2');
        Route::get('pra-produksi/get', 'get');
        Route::get('pra-produksi/get-batal', 'getBatal');
        Route::get('pra-produksi/get-linkdata', 'getLinkData');
        Route::get('pra-produksi/get-allref', 'getAllRef');
        Route::get('pra-produksi/get-detail-resep', 'getDetailResep');
        Route::delete('pra-produksi', 'destroy');
        Route::post('pra-produksi/set-batal', 'setBatal');
        Route::post('pra-produksi', 'store');
    });
    Route::controller(\Produksi\ProduksiController::class)->group(function () {
        Route::get('produksi', 'show1');
        Route::get('produksi/all', 'show2');
        Route::get('produksi/get', 'get');
        Route::get('produksi/get-batal', 'getBatal');
        Route::get('produksi/get-linkdata', 'getLinkData');
        Route::get('produksi/get-allref', 'getAllRef');
        Route::get('produksi/get-detail-resep', 'getDetailResep');
        Route::delete('produksi', 'destroy');
        Route::post('produksi/set-batal', 'setBatal');
        Route::post('produksi', 'store');
    });
});

Route::group(['prefix' => 'sales', 'as' => 'sales', 'middleware' => 'appauth'], function () {
    Route::controller(\Sales\SalesOrderController::class)->group(function () {
        Route::get('so', 'show1');
        Route::get('so/all', 'show2');
        Route::get('so/get', 'get');
        Route::get('so/get-harga', 'getHargaJual');
        Route::get('so/get-customer', 'getCustomer');
        Route::get('so/get-batal', 'getBatal');
        Route::get('so/get-linkdata', 'getLinkData');
        Route::get('so/get-allref', 'getAllRef');
        Route::delete('so', 'destroy');
        Route::post('so/set-batal', 'setBatal');
        Route::post('so', 'store');
    });
});

Route::group(['prefix' => 'finance', 'as' => 'finance', 'middleware' => 'appauth'], function () {
    Route::controller(\Finance\BankTerimaController::class)->group(function () {
        Route::get('bank-terima', 'show1');
        Route::get('bank-terima/all', 'show2');
        Route::get('bank-terima/get', 'get');
        Route::get('bank-terima/get-sobayar', 'getSOBayar');
        Route::get('bank-terima/get-posbayar', 'getPOSBayar');
        Route::get('bank-terima/get-batal', 'getBatal');
        Route::get('bank-terima/get-approved', 'getApproved');
        Route::get('bank-terima/get-allref', 'getAllRef');
        Route::delete('bank-terima', 'destroy');
        Route::post('bank-terima/set-batal', 'setBatal');
        Route::post('bank-terima/set-approved', 'setApproved');
        Route::post('bank-terima', 'store');
    });
});
