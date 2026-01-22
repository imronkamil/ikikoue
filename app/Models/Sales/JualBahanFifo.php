<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JualBahanFifo extends Model
{
    use HasFactory;
    protected $table = 't_jual_bahan_fifo';
    protected $primaryKey = 'dtl2_fifo_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
