<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PR3 extends Model
{
    use HasFactory;
    protected $table = 't_pr3';
    protected $primaryKey = 'dtl3_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_bayar'=>'float',
        'qty_sisa'=>'float'
    ];
}
