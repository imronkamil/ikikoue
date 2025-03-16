<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupPerform extends Model
{
    use HasFactory;
    protected $table = 'm_grup_perform';
    protected $primaryKey = 'kd_grup_perform';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
