<?php

namespace App\Models\Tools;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasUsers extends Model
{
    use HasFactory;
    protected $table = 'pas_users';
    protected $primaryKey = 'user_id';
    protected $keyType = 'string';
    public $incrementing  = false;
    public $timestamps = false;
    /*protected $casts = [
        'foto'=>'string',
    ];*/

    public function getFotoAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // PostgreSQL returns BYTEA hex like: "\\x424d..."
        //$value = str_replace('\\x', '', $value);
        //$value = ltrim($value, "\\x");
        $value = preg_replace('/^\\\\x/', '', $value);

        // Convert hex → binary
        $binary = hex2bin($value);

        // Encode to base64
        return base64_encode($binary);
    }
}

