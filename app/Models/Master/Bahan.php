<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    use HasFactory;
    protected $table = 'm_bahan';
    protected $primaryKey = 'kd_bahan';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'konversi2'=>'float',
    ];
}
