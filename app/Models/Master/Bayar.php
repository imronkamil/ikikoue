<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bayar extends Model
{
    use HasFactory;
    protected $table = 'm_bayar';
    protected $primaryKey = 'kd_bayar';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
