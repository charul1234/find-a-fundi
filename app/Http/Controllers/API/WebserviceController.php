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
        $categories = Category::with(['media','children' => function($q) {
                        $q->select('id','title','parent_id');
                        $q->where('is_active',TRUE);
                    }])->where(['is_active'=>TRUE, 'parent_id'=>0])->get(['id','title']);

        if (!empty($categories)) {
            foreach ($categories as $category) {
                $category->image = asset($category->getFirstMediaUrl('image'));
                if (!empty($category->children)) {
                    foreach ($category->children as $media) {
                        $media->image = asset($media->getFirstMediaUrl('image'));
                        unset($media->media);
                    }
                }
                unset($category->media);
            }
        }

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
        $advertisements = Advertisement::with('media')->where(['is_active'=>TRUE])->where('start_date', '<=', date("Y-m-d"))->where('end_date', '>=', date("Y-m-d"))->get(['id', 'title', 'discription' ,'start_date', 'end_date']);
        if (!empty($advertisements)) {
            foreach ($advertisements as $advertisement) {
                $advertisement->image = asset($advertisement->getFirstMediaUrl('image'));
                unset($advertisement->media);
            }
        }
        $response['status'] = true;  
        $response['advertisements'] = $advertisements;
        $response['message'] = "Success";
        return response()->json($response);
    }

    /**
     * API to get get Sub Categories by Category Id
     *
     * @return [string] message
     */
    public function getSubCategoriesByCategoryId(Request $request){
        $category_id = intval($request->input('category_id')); 
        $categories= Category::where(array('is_active'=>true,'parent_id'=>$category_id))->get(['id','title']);
        if(count($categories))
        {             
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $category->image = asset($category->getFirstMediaUrl('image'));
                    unset($category->media);
                }
            }
            $response=array('status'=>true,'subcategories'=>$categories,'message'=>'Record found!');
        }else
        {
            $response=array('status'=>false,'subcategories'=>'','message'=>'Record not found');
        }
        
        return response()->json($response);
    }
}
