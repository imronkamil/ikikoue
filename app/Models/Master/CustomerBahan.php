<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerBahan extends Model
{
    use HasFactory;
    protected $table = 'm_customer_bahan';
    protected $primaryKey = 'customer_bahan_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
