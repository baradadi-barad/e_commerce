<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleRights extends Authenticatable
{
    use Notifiable;
    protected $table = 'role_rights';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public function rolesname()
    {
        return $this->belongsTo('App\Models\Roles', 'role_id')->withTrashed();
    }
    public function contdata()
    {
        return $this->belongsTo('App\Models\Contexts', 'context_id')->with('rolesnamedata14')->withTrashed();
    }
    public function contname()
    {
        return $this->belongsTo('App\Models\Contexts', 'context_id')->with('rolesnamedata14')->withTrashed();
    }
}
