<?php

namespace App\Models\Security;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;
    protected $table = 'user_activity';
    protected $primaryKey = 'sysid';
    public $timestamps = false;
    protected $casts = [
    ];
}
