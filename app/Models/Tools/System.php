<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    use HasFactory;
    protected $table = 'i_system';
    protected $primaryKey = 'system_id';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
