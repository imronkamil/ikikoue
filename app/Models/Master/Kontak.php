<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kontak extends Model
{
    use HasFactory;
    protected $table = 'm_kontak';
    protected $primaryKey = 'kd_kontak';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
