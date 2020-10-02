<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Certification  extends Model implements HasMedia
{
	use HasMediaTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
        'user_id', 'title', 'type'
    ];
    public function registerMediaCollections(){
        $this->addMediaCollection('document')
        ->singleFile();  
        $this->addMediaCollection('degree')
        ->singleFile(); 
         $this->addMediaCollection('certification')
        ->singleFile(); 
         $this->addMediaCollection('diploma')
        ->singleFile(); 

    } 
}
