<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket1 extends Model
{
    use HasFactory;
    protected $table = 'm_paket1';
    protected $primaryKey = 'kd_paket';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
