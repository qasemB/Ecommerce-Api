<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    public function cart(){
        return $this->belongsTo('App\Models\Cart');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function color(){
        return $this->belongsTo('App\Models\Color');
    }
    public function guarantee(){
        return $this->belongsTo('App\Models\Guarantee');
    }
}
