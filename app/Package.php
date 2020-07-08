<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Package extends Model implements HasMedia
{
    use HasMediaTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

         'category_id', 'title', 'duration', 'description', 'is_active'
    ];   

    public function registerMediaCollections(){
        $this->addMediaCollection('image');
             //->singleFile();     
    } 

    // For get category
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }

    // For get provider
    public function provider(){
        return $this->belongsTo(User::class,'user_id');
    }
}
