<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferReceive2 extends Model
{
    use HasFactory;
    protected $table = 't_stock_transfer_receive2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty_req'=>'float',
        'qty'=>'float',
        'qty_send'=>'float',
        'rp_harga'=>'float',
        'rp_total'=>'float'
    ];
}
