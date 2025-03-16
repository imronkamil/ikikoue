<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $table = 'm_account';
    protected $primaryKey = 'no_account';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
}
