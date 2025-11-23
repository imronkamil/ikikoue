<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasAccess extends Model
{
    use HasFactory;
    protected $table = 'pas_access';
    protected $primaryKey = 'pass_access_id';
    protected $keyType = 'bigint';
    public $incrementing  = false;
    public $timestamps = false;
}
