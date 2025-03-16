<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Users extends Model
{
    use HasFactory;
    protected $table = 'pas_users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
    ];
}
