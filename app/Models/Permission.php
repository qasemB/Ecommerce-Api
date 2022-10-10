<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['title', 'descriptiosn', 'path', 'method', 'arrange', 'category'] ;
    protected $hidden = [
        'created_at', 'deleted_at', 'method', 'path', 'updated_at'
    ];

    public function roles() : BelongsToMany
    {
        return $this->belongsToMany('App\Models\Role');
    }
}
