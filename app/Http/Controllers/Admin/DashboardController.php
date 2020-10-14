<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $no_of_providers = User::whereHas('roles', function($query){
            $query->where('id', config('constants.ROLE_TYPE_PROVIDER_ID'));
        })->where(['is_active'=>true])->count();
        $no_of_seekers = User::whereHas('roles', function($query){
            $query->where('id', config('constants.ROLE_TYPE_SEEKER_ID'));
        })->where(['is_active'=>true])->count();
        return view('admin.dashboard',compact('no_of_providers','no_of_seekers'));
    }
}
