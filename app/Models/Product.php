<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Category');
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Color');
    }

    public function guarantees(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Guarantee');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Property')->withPivot('value');
    }
}
