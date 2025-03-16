<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanSatuan extends Model
{
    use HasFactory;
    protected $table = 'm_bahan_satuan';
    protected $primaryKey = 'bahan_satuan_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'rasio'=>'float',
    ];
}
