<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;
    protected $table = 'm_satuan';
    protected $primaryKey = 'satuan';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
