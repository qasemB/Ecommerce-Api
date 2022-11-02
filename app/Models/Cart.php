<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    public function items(){
        return $this->hasMany('App\Models\Item');
    }
}
