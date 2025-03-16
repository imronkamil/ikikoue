<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelBahan extends Model
{
    use HasFactory;
    protected $table = 'm_level_bahan';
    protected $primaryKey = 'kd_level';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
