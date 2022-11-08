<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function user(){
        return $this->belongsTo('App\User');
    }
    public function cart(){
        return $this->belongsTo('App\Models\Cart');
    }
}
