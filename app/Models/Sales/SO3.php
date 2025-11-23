<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SO3 extends Model
{
    use HasFactory;
    protected $table = 't_so3';
    protected $primaryKey = 'dtl3_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_bayar'=>'float',
        'rp_sisa'=>'float'
    ];
}
