<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    use HasFactory;
    protected $table = 'm_fiscal_period';
    protected $primaryKey = 'fiscal_period_id';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
