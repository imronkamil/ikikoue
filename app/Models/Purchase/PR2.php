<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PR2 extends Model
{
    use HasFactory;
    protected $table = 't_pr2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty'=>'float',
        'rp_harga'=>'float',
        'persen_diskon'=>'float',
        'rp_diskon'=>'float',
        'persen_diskon2'=>'float',
        'rp_diskon2'=>'float',
        'persen_diskon3'=>'float',
        'rp_diskon3'=>'float',
        'persen_diskon4'=>'float',
        'rp_diskon4'=>'float',
        'persen_pajak'=>'float',
        'rp_pajak'=>'float',
        'rp_harga_akhir'=>'float',
        'qty_sisa'=>'float',
        'konversi'=>'float'
    ];
}
