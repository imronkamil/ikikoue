<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokFifo extends Model
{
    use HasFactory;
    protected $table = 'm_stok_fifo';
    protected $primaryKey = 'stok_fifo_key';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'qty_on_hand'=>'float',
        'qty_on_order'=>'float',
        'qty_is_committed'=>'float',
        'qty_used'=>'float',
        'rp_harga'=>'float',
    ];
}
