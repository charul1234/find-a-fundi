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
use App\CategoryUser;
use App\Profile;
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
      
      $categories=array();      
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
    public function getSubCategories(Request $request){
     
      $categories=array();
     
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
        
        return response()->json($response);
    }
    /**
     * API to get get Packages by sub Category Id
     *
     * @return [string] message
     */
    public function getPackages(Request $request){
        
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
    public function bookingRequest(Request $request){

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
    public function getProviderDetail(Request $request){
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
     * API to save provider information
     *
     * @return [string] message
     */
    public function addProviderInfo(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $provider=array();
        if($user)
        {          
           $validator = Validator::make($request->all(), [             
            'location' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required',
            'category_id' => 'required',
            'subcategory_id' => 'required',
           ]);

           $data['work_address']=isset($data['location'])?$data['location']:'';
           $data['latitude']=isset($data['latitude'])?$data['latitude']:'';
           $data['longitude']=isset($data['longitude'])?$data['longitude']:'';
           $data['is_package']=isset($data['is_package'])?$data['is_package']:'';
           $data['is_hourly']=isset($data['is_hourly'])?$data['is_hourly']:'';

            $user_id=$user->id;
            $category_id=$request->category_id;
            
            if(intval($category_id) > 0)
            {
               CategoryUser::where('user_id',$user_id)->delete();
               $user->category_user()->create(['user_id'=>$user_id,'category_id'=>$category_id]);
            } 
            $subcategory_ids=$request->subcategory_id;   
            $subcategory_ids=explode(',',$subcategory_ids);
            if(count($subcategory_ids)>0)
            {
                  foreach ($subcategory_ids as $key => $subcategory_id) 
                  {  
                    if(intval($subcategory_id) > 0)
                     {          
                      $user->category_user()->create(['user_id'=>$user_id,'category_id'=>$subcategory_id]); 
                      }  
                  }                              
            }

            $profile = Profile::where(array('user_id'=>$user_id));
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$data['work_address'] ,'latitude'=>$data['latitude'],'longitude'=>$data['longitude'],'radius'=>$data['radius'],'is_hourly'=>$data['is_hourly'],'is_package'=>$data['is_package']);
                $profile->update($profile_data);
            }
            
           if($request->is_package==true)
           {
            $packagesdata=json_decode(stripslashes($request->packages));
            if(intval($packagesdata) > 0 && !empty($packagesdata))
            { 
              foreach ($packagesdata as $key => $data) {                
                $user->package_user()->create(['package_id'=>$data->package_id,'price'=>$data->price]);  
              }
            }
           } 
           if($request->is_hourly==true)
           {
            $hourlydata=json_decode(stripslashes($request->hourly));
            if(intval($hourlydata) > 0 && !empty($hourlydata))
            { 
              foreach ($hourlydata as $key => $data) {                
                $user->hourly_charge()->create(['user_id'=>$user_id,'hours'=>$data->hours,'price'=>$data->price,'type'=>$data->type]);  
              }
            }
           } 
            if ($request->hasFile('qualification')){
                $file = $request->file('qualification');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('qualification');
            }
            if ($request->hasFile('badge')){
                $file = $request->file('badge');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('badge');
            }
            if ($request->hasFile('certification')){
                $file = $request->file('certification');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('certification');
            }
           
          if ($validator->fails()) { 
              return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
          }  
          $response=array('status'=>true,'message'=>'Provider information successfully added.');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
            $is_type=false;
            $is_hourly=false;
            $is_rfq=false;
            if($type=='is_hourly')
            {
              $is_hourly=true;

            }
            if($type=='is_rfq')
            {
               $is_rfq=true;
            }
            $user_id = $request->input('user_id'); 
            $category_id = $request->input('category_id'); 
            $subcategory_id = $request->input('subcategory_id');  
            $subcategory_id=explode(',',$subcategory_id);
            $latitude = $request->input('latitude');  
            $longitude = $request->input('longitude');  

            $providers= CategoryUser::with(['category','user','user.profile'])
            ->whereHas('category', function($query) use ($subcategory_id) {             
              $query->whereIn('category_id', $subcategory_id);            
            })  
            
            ->whereHas('user.profile', function($query) use ($is_hourly,$is_rfq) {
              if($is_hourly==true)
              {
                $query->where('is_hourly',$is_hourly); 
              }
              if($is_rfq==true)
              {
                $query->where('is_rfq',$is_rfq); 
              }                       
            })          
            ->whereIn('category_id',$subcategory_id);
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $providers=$providers->offset($start_limit)->limit($end_limit)->get();
            $providersdata=[];
            foreach ($providers as $key => $provider) {
               //$provider['profile_picture']='';
              if(isset($provider->user) && $provider->user->getMedia('profile_picture')->count() > 0 && file_exists($provider->user->getFirstMedia('profile_picture')->getPath()))
              {
                $provider['profile_picture']=$provider->user->getFirstMedia('profile_picture')->getFullUrl();
              }               
               
               //print_r($provider->user->profile->latitude);
               /* echo $latitude; echo "<br/>";
               echo $longitude;echo "<br/>";
               echo $provider->user->profile->latitude;echo "<br/>";
               echo $provider->user->profile->longitude;echo "<br/>";     */       
              /* $Kilometer_distance=  $this->distance($latitude, $longitude, $provider->user->profile->latitude, $provider->user->profile->longitude, "K");
               if($provider->user->profile->latitude!='null' && $provider->user->profile->latitude!='')
               {
                echo "vvv". $Kilometer_distance."vvv".$provider->user->profile->radius;
                echo "<br/>";
                if($Kilometer_distance<=$provider->user->profile->radius)
                {
                  //echo "bb";  echo $provider->user->profile->user_id; echo "bb";
                  $providersdata[]=$provider;                  
                }
               }*/
            }

            if ($validator->fails()) {
                return response()->json(['status'=>false,'providers'=>'','message'=>$validator->errors()->first()]);
            }
             if(count($providers))
            { 

               $response=array('status'=>true,'providers'=>$providers,'message'=>'Record found');
            }else
            {
               $response=array('status'=>false,'providers'=>$providers,'message'=>'Record not found');
            }
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
