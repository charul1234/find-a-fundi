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
use App\Category;
use App\Advertisement;

class WebserviceController extends Controller
{

    /**
     * API to get Countries
     *
     * @return [string] message
     */
    public function getCountries(Request $request){
        $countries = Country::with(['cities' => function($q) {
                        $q->select('id','title','country_id');
                        $q->where('is_active',TRUE);
                    }])->where('is_active',TRUE)->get(['id','title']);
        $response['status'] = true;  
        $response['countries'] = $countries;
        $response['message'] = "Success";
        return response()->json($response);
    }

    /**
     * API to get get Categories
     *
     * @return [string] message
     */
    public function getCategories(Request $request){
        $categories = Category::with(['children' => function($q) {
                        $q->select('id','title','parent_id');
                        $q->where('is_active',TRUE);
                    }])->where(['is_active'=>TRUE, 'parent_id'=>0])->get(['id','title']);
        $response['status'] = true;  
        $response['categories'] = $categories;
        $response['message'] = "Success";
        return response()->json($response);
    }

    /**
     * API to get Advertisements
     *
     * @return [string] message
     */
    public function getAdvertisements(Request $request){
        $advertisements = Advertisement::where(['is_active'=>TRUE])->get(['id', 'title', 'description' ,'start_date', 'end_date']);
        $response['status'] = true;  
        $response['advertisements'] = $advertisements;
        $response['message'] = "Success";
        return response()->json($response);
    }
}
