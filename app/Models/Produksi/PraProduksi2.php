<?php

namespace App\Models\Produksi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PraProduksi2 extends Model
{
    use HasFactory;
    protected $table = 't_pra_produksi2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty'=>'float',
        'rp_harga'=>'float',
        'rp_total'=>'float'
    ];
}
