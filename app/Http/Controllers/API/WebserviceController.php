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
use App\Package;
use App\PackageUser;

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
     * API to get get sub category by Category Id
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
    /**
     * API to get get Packages by Category Id
     *
     * @return [string] message
     */
    public function getPackagesByCategoryId(Request $request){
        
        $user = Auth::user(); 
        $validator = Validator::make($request->all(), [
            'category_id'=>'required',
        ]);
            
        if ($validator->fails()) {
            return response()->json(['status'=>false,'packages'=>'','message'=>$validator->errors()->first()]);
        }
        if($user)
        {   $end_limit = config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');        
            $category_id = intval($request->input('category_id'));             
            $packages= PackageUser::with('package')
            ->whereHas('package', function($query) use ($category_id) {
              $query->where('category_id', $category_id);
            })
            ->where('user_id',$user->id);
            $keywords = $request->input('keywords');
            $keywords=isset($keywords)?$keywords:'';
            if($keywords!= ''){
                $packages->whereHas('package', function($query) use ($keywords) {
                $query->where('title', 'LIKE', '%' . $keywords . '%');
            });
            }
            $sortby = $request->input('sortby');
            $sortby=isset($sortby)?$sortby:'';
            if($sortby!= ''){
              $packages->orderBy('price', $sortby);
            }   
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $packages=$packages->offset($start_limit)->limit($end_limit)->get();        
                  
            if(count($packages))
            {   
                $packagesdata=array();                   
                if (!empty($packages)) {
                    foreach ($packages as $package) { 
                        $package->package->image = $package->package->getMedia('image');
                        unset($package->package->media);
                        $packageImages=array();                                  
                        if (count($package->package->image) > 0) 
                        {
                            foreach ($package->package->image as $media)
                            {                        
                               $packageImages[]=array('id'=>$media->id,
                                                      'name'=>$media->name,
                                                      'file_name'=>$media->file_name,
                                                      'image_path'=>$media->getFullUrl());
                             }
                        }
                        $packagesdata[]=array('id'=>$package->package->id,
                                              'title'=>$package->package->title,
                                              'category_id'=>$package->package->category_id,
                                              'duration'=>$package->package->duration,
                                              'description'=>$package->package->description,
                                              'images'=>$packageImages,
                                              'user_id'=>$package->user_id,
                                              'price'=>$package->price,
                                              );
                    }    
                } 

                $response=array('status'=>true,'packages'=>$packagesdata,'message'=>'Record found!');
            }else
            {
                $response=array('status'=>false,'packages'=>'','message'=>'Record not found');
            }
        }else
        {
                $response=array('status'=>false,'packages'=>'','message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }     
}
