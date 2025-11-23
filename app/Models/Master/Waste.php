<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;
    protected $table = 'm_waste';
    protected $primaryKey = 'waste_id';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
