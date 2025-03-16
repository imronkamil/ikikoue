<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    use HasFactory;
    protected $table = 'm_pajak';
    protected $primaryKey = 'kd_pajak';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
        'persen_pajak'=>'float',
    ];
}
