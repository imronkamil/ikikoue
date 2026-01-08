<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ARDP1 extends Model
{
    use HasFactory;
    protected $table = 't_ardp1';
    protected $primaryKey = 'doc_key';
    protected $keyType = 'bigInteger';
    public $incrementing  = false;
    public $timestamps = false;
}
