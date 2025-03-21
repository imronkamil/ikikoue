<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $table = 'm_supplier';
    protected $primaryKey = 'kd_supplier';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
