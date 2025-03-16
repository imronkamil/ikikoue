<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeBahan extends Model
{
    use HasFactory;
    protected $table = 'm_tipe_bahan';
    protected $primaryKey = 'kd_tipe_bahan';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
