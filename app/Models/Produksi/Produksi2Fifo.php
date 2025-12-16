<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produksi2Fifo extends Model
{
    use HasFactory;
    protected $table = 't_produksi2_fifo';
    protected $primaryKey = 'dtl2_fifo_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty'=>'float'
    ];
}
