<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer2 extends Model
{
    use HasFactory;
    protected $table = 't_stock_transfer2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty'=>'float',
        'rp_harga'=>'float',
        'rp_total'=>'float',
        'qty_sisa'=>'float'
    ];
}
