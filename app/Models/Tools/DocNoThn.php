<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocNoThn extends Model
{
    use HasFactory;
    protected $table = 'i_docno_thn';
    protected $primaryKey = 'docno_thn_id';
    protected $keyType = 'bigint';
    public $incrementing  = false;
    public $timestamps = false;
    /*protected $casts = [
        'doc_key'=>'bigint',
    ];*/
}
