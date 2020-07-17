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
use App\Booking;
use App\BookingSubcategory;

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
      $user = Auth::user(); 
      $categories=array();
      if($user)
      {
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
      }else
      {
        $response=array('status'=>false,'categories'=>$categories,'message'=>'Oops! Invalid credential.');
      } 
        return response()->json($response);
    }

    /**
     * API to get Advertisements
     *
     * @return [string] message
     */
    public function getAdvertisements(Request $request){
      $user = Auth::user(); 
      $advertisements=array();
      if($user)
      {
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
      }else
      {
        $response=array('status'=>false,'advertisements'=>$advertisements,'message'=>'Oops! Invalid credential.');
      } 
        return response()->json($response);
    }
    /**
     * API to get get sub category by Category Id
     *
     * @return [string] message
     */
    public function getSubCategoriesByCategoryId(Request $request){
      $user = Auth::user(); 
      $categories=array();
      if($user)
      {
        $category_id = intval($request->input('category_id')); 
        $categories= Category::where(array('is_active'=>true,'parent_id'=>$category_id))->where('parent_id','!=',0)->get(['id','title']);
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
            $response=array('status'=>false,'subcategories'=>$categories,'message'=>'Record not found');
        }
      }else
      {
        $response=array('status'=>false,'subcategories'=>$categories,'message'=>'Oops! Invalid credential.');
      }
        
        return response()->json($response);
    }
    /**
     * API to get get Packages by sub Category Id
     *
     * @return [string] message
     */
    public function getPackagesBySubCategoryId(Request $request){
        
        $user = Auth::user(); 
        $packagesdata=array(); 
        $validator = Validator::make($request->all(), [
            'subcategory_id'=>'required',
        ]);
            
        if ($validator->fails()) {
            return response()->json(['status'=>false,'packages'=>$packagesdata,'message'=>$validator->errors()->first()]);
        }
        if($user)
        {   $end_limit = config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');        
            $category_id = $request->input('subcategory_id');  
            $category_id=explode(',',$category_id);   
                  
            $packages= PackageUser::with('package')
            ->whereHas('package', function($query) use ($category_id) {              
              $query->whereIn('category_id', $category_id);  
              $query->where('is_active',true);            
            });    
            //->where('user_id',$user->id);
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
                $response=array('status'=>false,'packages'=>$packagesdata,'message'=>'Record not found');
            }
        }else
        {
                $response=array('status'=>false,'packages'=>$packagesdata,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }    
     /**
     * API to add custom requirement
     *
     * @return [string] message
     */
    public function addCustomRequirement(Request $request){

        $user = Auth::user(); 
        $data = $request->all(); 
        if($user)
        {
            $validator = Validator::make($data, [
                'title'=>'required', 
                'description'=>'required',
                'date'=>'required',
                'time'=>'required',
                'location'=>'required',
                'latitude'=>'required',
                'longitude'=>'required',
                'category_id'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'booking'=>'','message'=>$validator->errors()->first()]);
            }
            $data['user_id']=$user->id;
            $data['datetime']=$data['date'].' '.$data['time']; 
            $booking = Booking::create($data);
            $subcategories=isset($data['subcategory_id'])?$data['subcategory_id']:'';
            if($subcategories)
            {
                $subcategories=explode(',',$subcategories);
                if(count($subcategories)>0)
                {
                    foreach ($subcategories as $key => $subcategory) 
                    {
                        BookingSubcategory::create(array('booking_id'=>$booking->id,
                                                         'category_id'=>$subcategory));
                    }
                }
            }                

            $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Custom requirement saved successfully.');
        }else
        {
                $response=array('status'=>false,'booking'=>'','message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }  
    /**
     * API to add Send Request
     *
     * @return [string] message
     */
    public function addSendRequest(Request $request){

        $user = Auth::user(); 
        $data = $request->all(); 
        if($user)
        {
            $validator = Validator::make($data, [
                'title'=>'required', 
                'description'=>'required',
                'date'=>'required',
                'time'=>'required',
                'location'=>'required',
                'latitude'=>'required',
                'longitude'=>'required',
                'category_id'=>'required',
                'user_id'=>'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'booking'=>'','message'=>$validator->errors()->first()]);
            }
            $data['datetime']=$data['date'].' '.$data['time']; 
            $booking = Booking::create($data);
            $subcategories=isset($data['subcategory_id'])?$data['subcategory_id']:'';
            if($subcategories!='')
            {
                $subcategories=explode(',',$subcategories);
                if(count($subcategories)>0)
                {
                    foreach ($subcategories as $key => $subcategory) 
                    {
                        BookingSubcategory::create(array('booking_id'=>$booking->id,
                                                         'category_id'=>$subcategory));
                    }
                }
            }                

            $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Send request saved successfully.');
        }else
        {
                $response=array('status'=>false,'booking'=>'','message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }  
    /**
     * API to get provider details according to Id 
     *
     * @return [string] message
     */
    public function getProviderById(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $provider=array();
        $user_id=isset($data['user_id'])?$data['user_id']:'';
        if($user)
        {
          $validator = Validator::make($data, [
                'user_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'data'=>$provider,'message'=>$validator->errors()->first()]);
            }
          $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })
            ->first(); 
          $provider['profile_picture']='';
          if(isset($provider) && $provider->getMedia('profile_picture')->count() > 0 && file_exists($provider->getFirstMedia('profile_picture')->getPath()))
          {
            $provider['profile_picture']=$provider->getFirstMedia('profile_picture')->getFullUrl();
          }  

          $response=array('status'=>true,'data'=>$provider,'message'=>'Record found');
        }else
        {
            $response=array('status'=>false,'data'=>$provider,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get all providers listing according lat, long, radius
     *
     * @return [string] message
     */
    public function getProvidersByLatLong(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $providers=array();
        if($user)
        {
            $end_limit = config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $validator = Validator::make($data, [
                'latitude'=>'required', 
                'longitude'=>'required',
                'category_id'=>'required',
                'subcategory_id'=>'required',
                'type'=>'required'
            ]);
            $type=$data['type'];
            /*if($type=='is_hourly')
            {
              $type=true;
            }*/
            
            $providers= User::with('profile')
            ->whereHas('profile', function($query) use ($type) {    
              $query->where('user_id',17);            
            })
            ->get(); 
            //echo $this->distance(32.9697, -96.80322, 33.46786, -97.53506, "K") . " Kilometers<br>";

            if ($validator->fails()) {
                return response()->json(['status'=>false,'providers'=>'','message'=>$validator->errors()->first()]);
            }

            $response=array('status'=>true,'providers'=>$providers,'message'=>'Record not found');
        }else
        {
            $response=array('status'=>false,'providers'=>$providers,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    
    /**
     * distance check
     *
     * @return [string] message
     */
    function distance($lat1, $lon1, $lat2, $lon2, $unit) {
      $theta = $lon1 - $lon2;
      $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
      $dist = acos($dist);
      $dist = rad2deg($dist);
      $miles = $dist * 60 * 1.1515;
      $unit = strtoupper($unit);

      if ($unit == "K") {
          return ($miles * 1.609344);
      } else if ($unit == "N") {
          return ($miles * 0.8684);
      } else {
          return $miles;
      }
}

}
