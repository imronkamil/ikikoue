<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rak extends Model
{
    use HasFactory;
    protected $table = 'm_rak';
    protected $primaryKey = 'kd_rak';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
