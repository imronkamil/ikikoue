<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SO6 extends Model
{
    use HasFactory;
    protected $table = 't_so6';
    protected $primaryKey = 'dtl6_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
