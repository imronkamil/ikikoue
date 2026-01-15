<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'i_profile';
    protected $primaryKey = 'nm_company';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    /*protected $casts = [
        'foto'=>'string',
    ];*/

}

