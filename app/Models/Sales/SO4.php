<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SO4 extends Model
{
    use HasFactory;
    protected $table = 't_so4';
    protected $primaryKey = 'dtl4_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'qty'=>'float',
        'kurang'=>'float',
        'pakai'=>'float',
        'rp_pakai'=>'float',
        'rp_hpp'=>'float'
    ];
}
