<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id','title','is_active'
    ];

    // For default settings
    protected static function boot(){
	    parent::boot();

	    // Order by name ASC
	    static::addGlobalScope('order', function ($query) {
	        $query->orderBy('title', 'ASC');
	    });
	}
	/**
     * Get the country that belong to this city.
    */
    public function country(){         
        return $this->belongsTo(Country::class);
    }
}
