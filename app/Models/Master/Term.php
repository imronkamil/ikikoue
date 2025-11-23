<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;
    protected $table = 'm_term';
    protected $primaryKey = 'kd_term';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
