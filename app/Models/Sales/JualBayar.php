<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JualBayar extends Model
{
    use HasFactory;
    protected $table = 't_jual_bayar';
    protected $primaryKey = 'dtl3_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
