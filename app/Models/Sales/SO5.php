<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SO5 extends Model
{
    use HasFactory;
    protected $table = 't_so5';
    protected $primaryKey = 'dtl5_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_tagihan'=>'float',
        'rp_diskon'=>'float',
        'rp_bayar'=>'float',
        'rp_sisa'=>'float',
        'rp_cair'=>'float',
        'persen_admin'=>'float',
        'rp_admin'=>'float'
    ];
}
