<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket2 extends Model
{
    use HasFactory;
    protected $table = 'm_paket2';
    protected $primaryKey = 'paket2_id';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
