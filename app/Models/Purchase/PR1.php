<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PR1 extends Model
{
    use HasFactory;
    protected $table = 't_pr1';
    protected $primaryKey = 'doc_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_total_awal'=>'float',
        'persen_diskon'=>'float',
        'rp_diskon'=>'float',
        'persen_pajak'=>'float',
        'rp_pajak'=>'float',
        'persen_biaya'=>'float',
        'rp_biaya'=>'float',
        'rp_rounding'=>'float',
        'rp_total'=>'float',
        'rp_uangmuka'=>'float',
        'rp_bayar'=>'float',
        'rp_sisa'=>'float'
    ];
}
