<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JualBahan extends Model
{
    use HasFactory;
    protected $table = 't_jual_bahan';
    protected $primaryKey = 'dtl2_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
