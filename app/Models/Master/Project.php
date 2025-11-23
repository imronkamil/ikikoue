<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $table = 'm_project';
    protected $primaryKey = 'project_id';
    protected $keyType = 'bigint';
    public $incrementing  = false;
    public $timestamps = false;
}
