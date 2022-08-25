<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Discount extends Model
{
    public function products(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Product');
    }
}
