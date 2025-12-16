<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resep2 extends Model
{
    use HasFactory;
    protected $table = 'm_resep2';
    protected $primaryKey = 'resep2_id';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
