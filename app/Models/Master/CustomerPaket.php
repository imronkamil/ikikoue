<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPaket extends Model
{
    use HasFactory;
    protected $table = 'm_customer_paket';
    protected $primaryKey = 'customer_paket_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
