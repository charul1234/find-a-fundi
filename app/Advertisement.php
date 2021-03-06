<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Carbon\Carbon;

class Advertisement extends Model implements HasMedia
{
    use HasMediaTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

         'page_name', 'section', 'title', 'discription', 'start_date','end_date','is_active','created_by','updated_by'
    ];   
    
    // For default settings
    protected static function boot(){
	    parent::boot();

	    // Order by name ASC
	    static::addGlobalScope('order', function ($query) {
	        $query->orderBy('title', 'ASC');
	    });
	}

    public function registerMediaCollections(){
        $this->addMediaCollection('image')
        ->useFallbackUrl(asset(config('constants.NO_IMAGE_URL')))
        ->useFallbackPath(public_path(config('constants.NO_IMAGE_URL')))
        ->singleFile();     
    } 
	/**
     * For get position of advertisement
     *
     * @return \Illuminate\Http\Response
    */   
    public static function getSections()
    { 
       $sections= array('top'=>'Top', 
                        'middle'=>'Middle', 
                        'bottom'=>'Bottom', 
                        'sidebar_panel'=>'Sidebar Panel');
        return $sections;        
    } 
}
