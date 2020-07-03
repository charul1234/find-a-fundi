<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','is_active','is_default'
    ];

    // For default settings
    protected static function boot(){
	    parent::boot();

	    // Order by name ASC
	    static::addGlobalScope('order', function ($query) {
	        $query->orderBy('title', 'ASC');
	    });
	}
}
