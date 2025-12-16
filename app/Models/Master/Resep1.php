<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep1 extends Model
{
    use HasFactory;
    protected $table = 'm_resep1';
    protected $primaryKey = 'kd_bahan_resep';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
