<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocNo extends Model
{
    use HasFactory;
    protected $table = 'i_docno';
    protected $primaryKey = 'docno_id';
    protected $keyType = 'bigint';
    public $incrementing  = false;
    public $timestamps = false;
}
