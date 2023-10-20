<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contexts extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "contexts";



    public function rolesnamedata14()
    {
        return $this->belongsTo('App\Models\Contexts', 'parent_id')->withTrashed();
    }
    //    public function rolesname() {
    //        return $this->belongsTo('App\Roles', 'role_id');
    //    }
    public function rolerights()
    {
        return $this->hasMany('App\Models\RoleRights', 'context_id')->withTrashed();
    }
}
