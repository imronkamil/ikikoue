<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBahan extends Model
{
    use HasFactory;
    protected $table = 'm_supplier_bahan';
    protected $primaryKey = 'supplier_bahan_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
