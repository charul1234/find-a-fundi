<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class HomeController extends Controller
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
        return view('home');
    }
    public function isEmailVerify(Request $request)
    {  
        $user_id=isset($request->id)?$request->id:'';   
        $user = User::findOrFail($user_id);
          if (isset($user->is_email_verify) && $user->is_email_verify==FALSE) {
              $user->update(['is_email_verify'=>TRUE]);
              session()->flash('success',__('global.messages.account_activated'));
          }
        return redirect()->route('login');
    }
}
