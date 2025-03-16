<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoTran extends Model
{
    use HasFactory;
    protected $table = 't_notran';
    protected $primaryKey = 'doc_name';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
