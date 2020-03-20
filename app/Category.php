<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'parent_id', 'is_active'
    ];

    // For default settings
    protected static function boot(){
	    parent::boot();

	    // Order by name ASC
	    static::addGlobalScope('order', function ($query) {
	        $query->orderBy('title', 'ASC');
	    });
	}

    // For get sub categories of a category
    public function children(){
        return $this->hasMany(Category::class, 'parent_id');
    }

    // For get parent category of a category
    public function parent(){
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }
}
