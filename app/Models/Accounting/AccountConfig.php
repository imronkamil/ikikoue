<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountConfig extends Model
{
    use HasFactory;
    protected $table = 'm_account_config';
    protected $primaryKey = 'account_config_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
