<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaJual extends Model
{
    use HasFactory;
    protected $table = 'm_harga_jual';
    protected $primaryKey = 'harga_jual_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'persen_harga'=>'float',
        'rp_harga'=>'float',
    ];
}
