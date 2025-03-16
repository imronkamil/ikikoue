<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokFifoDtl extends Model
{
    use HasFactory;
    protected $table = 'm_stok_fifo_dtl';
    protected $primaryKey = 'stok_fifo_dtl_key';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'qty'=>'float',
    ];
}
