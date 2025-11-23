<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opname2 extends Model
{
    use HasFactory;
    protected $table = 't_opname2';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'kd_bahan'=>'string',
        'qty_kini'=>'float',
        'qty_stok'=>'float',
        'qty_selisih'=>'float',
        'qty_kurang'=>'float',
        'rp_harga'=>'float',
        'rp_harga_baru'=>'float',
        'rp_total'=>'float'
    ];
}
