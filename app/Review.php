<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
	public $timestamps = false;
     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
         'user_id', 'rating', 'text', 'added_by'
    ]; 
    // For get user
    public function user(){
        return $this->belongsTo(User::class,'added_by');
    }
}
