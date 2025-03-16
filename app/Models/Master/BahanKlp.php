<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanKlp extends Model
{
    use HasFactory;
    protected $table = 'm_bahan_klp';
    protected $primaryKey = 'bahan_klp_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
