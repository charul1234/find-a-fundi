<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    //


    // For get sub cities
    public function cities(){
        return $this->hasMany(City::class, 'country_id');
    }
}
