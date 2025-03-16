<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;
    protected $table = 'o_session';
    protected $primaryKey = 'token';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    protected $casts = [
    ];
}
