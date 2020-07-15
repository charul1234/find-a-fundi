<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingSubcategory extends Model
{
	public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'booking_id', 'category_id'
    ];
}
