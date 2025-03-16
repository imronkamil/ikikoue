<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupBahan extends Model
{
    use HasFactory;
    protected $table = 'm_grup_bahan';
    protected $primaryKey = 'kd_grup_bahan';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
