<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reason extends Model
{
    use HasFactory;
    protected $table = 'm_reason';
    protected $primaryKey = 'reason_id';
    protected $keyType = 'integer';
    public $incrementing  = false;
    public $timestamps = false;
}
