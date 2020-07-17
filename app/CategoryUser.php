<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryUser extends Model
{
	public $timestamps = false;
    protected $table='category_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'user_id', 'category_id'
    ];

    /**
     * Get the category information that belong to this category user.
    */
    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    /**
     * Get the user information that belong to this user_id.
    */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    
}
