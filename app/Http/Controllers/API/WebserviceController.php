<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use Auth;
use File;
use App\User;
use App\Country;

class WebserviceController extends Controller
{

    /**
     * API to get Countries
     *
     * @return [string] message
     */
    public function getCountries(Request $request){
        $countries = Country::with(['cities:country_id,id,title'])->where('is_active',TRUE)->get();
        $response['status'] = true;  
        $response['countries'] = $countries;
        $response['message'] = "Success";
        return response()->json($response);
    }
}
