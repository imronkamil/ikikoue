<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAlamat extends Model
{
    use HasFactory;
    protected $table = 'm_customer_alamat';
    protected $primaryKey = 'customer_alamat_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
