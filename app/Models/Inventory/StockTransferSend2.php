<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferSend2 extends Model
{
    use HasFactory;
    protected $table = 't_stock_transfer_send2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty_req'=>'float',
        'qty'=>'float',
        'qty_sisa'=>'float',
        'rp_harga'=>'float',
        'rp_total'=>'float',
        'qty_sisa'=>'float'
    ];
}
