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
    /**
     * Get the category information that belong to this booking.
    */
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
}
