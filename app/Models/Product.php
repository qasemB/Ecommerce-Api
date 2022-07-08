<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function categories(){
        return $this->belongsToMany('App\Models\Category');
    }
    public function colors(){
        return $this->belongsToMany('App\Models\Color');
    }
    public function guarantees(){
        return $this->belongsToMany('App\Models\Guarantee');
    }
}
