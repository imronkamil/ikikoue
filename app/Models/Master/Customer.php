<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'm_customer';
    protected $primaryKey = 'kd_customer';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
