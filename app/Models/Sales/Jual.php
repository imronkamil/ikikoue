<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jual extends Model
{
    use HasFactory;
    protected $table = 't_jual';
    protected $primaryKey = 'doc_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
