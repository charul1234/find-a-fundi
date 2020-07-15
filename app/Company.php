<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Company  extends Model implements HasMedia
{
	use HasMediaTrait;
    protected $fillable = [
        'user_id','name','remarks','document_number','is_payment_received','is_active'
    ];
    public function registerMediaCollections(){
        $this->addMediaCollection('batch')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile();     
    } 
}
