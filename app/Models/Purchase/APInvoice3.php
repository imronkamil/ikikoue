<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APInvoice3 extends Model
{
    use HasFactory;
    protected $table = 't_ap_invoice3';
    protected $primaryKey = 'dtl3_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_bayar'=>'float',
        'qty_sisa'=>'float'
    ];
}
