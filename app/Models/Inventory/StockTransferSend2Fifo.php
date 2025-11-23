<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransferSend2Fifo extends Model
{
    use HasFactory;
    protected $table = 't_stock_transfer_send2_fifo';
    protected $primaryKey = 'dtl2_fifo_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty'=>'float'
    ];
}
