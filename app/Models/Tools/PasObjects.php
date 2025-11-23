<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasObjects extends Model
{
    use HasFactory;
    protected $table = 'pas_objects';
    protected $primaryKey = 'menu_id';
    protected $keyType = 'bigint';
    public $incrementing  = false;
    public $timestamps = false;
}
