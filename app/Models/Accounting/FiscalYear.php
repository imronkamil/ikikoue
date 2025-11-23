<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalYear extends Model
{
    use HasFactory;
    protected $table = 'm_fiscal_year';
    protected $primaryKey = 'fiscal_year_id';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
