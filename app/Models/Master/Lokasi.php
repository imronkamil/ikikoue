<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lokasi extends Model
{
    use HasFactory;
    protected $table = 'm_lokasi';
    protected $primaryKey = 'kd_lokasi';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'persen_pajak'=>'float',
    ];
}
