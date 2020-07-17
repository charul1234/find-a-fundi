<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HourlyCharge extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'user_id','hours','price','type'
    ];
}
