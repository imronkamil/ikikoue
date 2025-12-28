<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;
    protected $table = 'm_bank';
    protected $primaryKey = 'bank_id';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
