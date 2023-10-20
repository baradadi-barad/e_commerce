<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Categories extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "catagories";

    public function Category()
    {
        return $this->belongsTo(Categories::class , 'parent_category')->where('parent_category' ,'==' , '0')->withTrashed();
    }
}
