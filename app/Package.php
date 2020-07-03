<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'title', 'duration', 'description', 'image', 'is_active'
    ];

    // For get category
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }

    // For get provider
    public function provider(){
        return $this->belongsTo(User::class,'user_id');
    }
}
