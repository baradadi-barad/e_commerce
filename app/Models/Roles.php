<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Roles extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "roles";

    public function rolescont()
    {
        return $this->hasMany('App\Models\RoleRights', 'role_id')->with('contdata');
    }
}
