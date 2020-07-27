<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingUser extends Model
{
    //protected $table='package_user';
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'user_id','booking_id','status','reason'
    ];
}
