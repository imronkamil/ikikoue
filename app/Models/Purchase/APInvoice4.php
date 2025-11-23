<?php

namespace App\Models\Purchase;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class APinvoice4 extends Model
{
    use HasFactory;
    protected $table = 't_ap_invoice4';
    protected $primaryKey = 'dtl4_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rp_jumlah'=>'float'
    ];
}
