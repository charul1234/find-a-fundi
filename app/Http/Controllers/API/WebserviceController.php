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
use App\Certification;
use App\BookingUser;
use App\Transaction;
use App\Faq;
use App\Review;
use App\Schedule;
use App\ExperienceLevel;
use App\HourlyCharge;
use App\Notifications\SendOTP;

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
       if(count($countries))
       {
          $response['status'] = true;  
          $response['countries'] = $countries;
          $response['message'] = "Record found"; 
        }else
        {
          $response['status'] = false;  
          $response['message'] = "Record not found"; 
        }             
      
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
        if(count($categories)>0)
        { 
          $response['status'] = true;  
          $response['categories'] = $categories;
          $response['message'] = "Success";
        }else
        {
          $response['status'] = false;  
          $response['message'] = "Record not found";
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
        if(count($advertisements)>0)
        {
           $response['status'] = true;  
           $response['advertisements'] = $advertisements;
           $response['message'] = "Success";
        }else
        {
           $response['status'] = false;  
           $response['message'] = "Record not found";
        }
        
      }else
      {
        $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
            $response=array('status'=>false,'message'=>'Record not found');
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
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
        }
        if($user)
        {   $end_limit = config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');        
            $category_id = $request->input('subcategory_id');  
            $category_id=explode(',',$category_id);   
                  
            /*$packages= PackageUser::with('package')
            ->whereHas('package', function($query) use ($category_id) {              
              $query->whereIn('category_id', $category_id);  
              $query->where('is_active',true);            
            });  */  
            $packages= Package::with(['category','media'])->whereIn('category_id', $category_id)->where('is_active',true);            
          
            $keywords = $request->input('keywords');
            $keywords=isset($keywords)?$keywords:'';
            if($keywords!= ''){
                $packages->where('title', 'LIKE', '%' . $keywords . '%'); 
                //$packages->orWhere('description', 'LIKE', '%' . $keywords . '%');            
            }
            /*$sortby = $request->input('sortby');
            $sortby=isset($sortby)?$sortby:'';
            if($sortby!= ''){
              $packages->orderBy('price', $sortby);
            } */ 
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $packages=$packages->offset($start_limit)->limit($end_limit)->get();        
               
            if(count($packages))
            {  
                 
                if (!empty($packages)) {
                    foreach ($packages as $package) { 
                       $packageImages=array();              
                        $package->image = $package->getMedia('image');
                        unset($package->media);
                        $packageImages=array();                                  
                        if (count($package->image) > 0) 
                        {
                            foreach ($package->image as $media)
                            {                        
                               $packageImages[]=array('id'=>$media->id,
                                                      'name'=>$media->name,
                                                      'file_name'=>$media->file_name,
                                                      'image_path'=>$media->getFullUrl());
                             }
                        }
                        $packagesdata[]=array('id'=>$package->id,
                                              'title'=>$package->title,
                                              'category_id'=>$package->category_id,
                                              'duration'=>$package->duration,
                                              'description'=>$package->description,
                                              'images'=>$packageImages
                                              );
                    }   
                } 

                $response=array('status'=>true,'packages'=>$packagesdata,'message'=>'Record found!');
            }else
            {
                $response=array('status'=>false,'message'=>'Record not found');
            }
        }else
        {
                $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
        $latitude=isset($data['latitude'])?$data['latitude']:'';
        $longitude=isset($data['longitude'])?$data['longitude']:'';
        $device_token=array();
        
        if($user)
        {

          $rules = [   
                'title'=>'required', 
                'description'=>'required',
                'date'=>'required',
                'time'=>'required',
                'location'=>'required',
                'latitude'=>'required',
                'longitude'=>'required',
                'category_id'=>'required',           
          ]; 
            if(isset($data['is_hourly'])==1 && intval($data['is_hourly'])>0) {          
               $rules['user_id'] =  'required';   
               $rules['min_budget'] =  'required'; 
               $rules['max_budget'] =  'required'; 
               $rules['hourly_charge_id'] =  'required';   
            }
           $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $userprofile=User::with('profile')->where('id',$user->id)->first();
            $provider_id=isset($data['user_id'])?$data['user_id']:0;
            $hourly_charge_id=isset($data['hourly_charge_id'])?$data['hourly_charge_id']:0;
            $data['user_id']=$provider_id;
            $data['hourly_charge_id']=$hourly_charge_id;
            $bookingdatetime=$data['date'].' '.$data['time'];
            $data['datetime']=$bookingdatetime;
            $data['requested_id']=isset($user->id)?$user->id:0;
            $data['status']='requested';
            $todays_datetime=date('Y-m-d H:i:s');
            $is_hourly=isset($data['is_hourly'])?$data['is_hourly']:0;
            $is_rfq=isset($data['is_rfq'])?$data['is_rfq']:0;

            if($is_hourly==1)
            {
              $update_tentative_starttime=''; 
              $update_tentative_endtime='';    
              $define_hour_charge=$define_hours=$define_hour_type=$booking_enddatetime='';    
              if($hourly_charge_id)
              {
                $define_hourly_charge=HourlyCharge::where('id',$hourly_charge_id)->first();
                $define_hours=isset($define_hourly_charge->hours)?$define_hourly_charge->hours:'';
                $define_hour_type=isset($define_hourly_charge->type)?$define_hourly_charge->type:'';
                if($define_hour_type=='hours')
                {
                  $define_hour_charge='+'.$define_hours." hour";
                }else if($define_hour_type=='days')
                {
                  $define_hour_charge='+'.$define_hours." day";
                }else if($define_hour_type=='weeks')
                {
                  $define_hour_charge='+'.$define_hours." week";
                }else if($define_hour_type=='months')
                {
                  $define_hour_charge='+'.$define_hours." month";
                }else if($define_hour_type=='years')
                {
                  $define_hour_charge='+'.$define_hours." year";
                }
                
                $booking_enddatetime=date('Y-m-d H:i:s',strtotime($define_hour_charge,strtotime($bookingdatetime)));                  
              }

            
              $tentative_hour=isset($userprofile->profile->tentative_hour)?$userprofile->profile->tentative_hour:'';
              if($tentative_hour=='')
              {
                $tentative_hour=getSetting('tentative_hour');
              }             
              if($tentative_hour!='')
              {
                   $tentative_addhour='+'.$tentative_hour." hour";
                   $tentative_minushour='-'.$tentative_hour." hour";
                   $update_tentative_endtime=$booking_enddatetime;
                   $update_tentative_endtime= date('Y-m-d H:i:s',strtotime($tentative_addhour,strtotime($update_tentative_endtime)));
                   $update_tentative_starttime= date('Y-m-d H:i:s',strtotime($tentative_minushour,strtotime($bookingdatetime)));
              }else
              {
                   $update_tentative_starttime=$bookingdatetime;
                   $update_tentative_endtime=$booking_enddatetime;
              }                  
              
              //$checkbooking= Booking::where(array('is_hourly'=>true,'user_id'=>$provider_id))->whereBetween('datetime', [$update_tentative_starttime, $update_tentative_endtime])->get();

              $checkbooking= Booking::where(array('is_hourly'=>true,'user_id'=>$provider_id))->where('datetime','<=', $bookingdatetime)->where('end_datetime','>=',$bookingdatetime)->get();
                
              //$checkbooking= Booking::where(array('is_hourly'=>true,'user_id'=>$provider_id))->whereBetween('datetime', [$update_tentative_starttime, $update_tentative_endtime])->get();
            
              if(($todays_datetime<$bookingdatetime) && ($checkbooking->count()==0))
              {
                $data['end_datetime']=$update_tentative_endtime;
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
                           
                //need to send request or notification to all providers user that are belong to Lat long address.
                $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Request Sent successfully');
                //start notification code                
                      $notification_providerdata=User::where('id',$provider_id)->first();
                      if($notification_providerdata)
                      {
                        $notification_title=config('constants.NOTIFICATION_NEW_BOOKING_HOURLY_SUBJECT');
                        $notification_message=config('constants.NOTIFICATION_NEW_BOOKING_HOURLY_MESSAGE');
                        if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                        {
                          if(!empty($notification_providerdata->device_token))
                          {
                            $device_token[]=$notification_providerdata->device_token;
                          }      
                                      
                        }else
                        {
                          if(!empty($notification_providerdata->device_token))
                          {
                            $device_token[]=$notification_providerdata->device_token;
                          }                           
                        } 
                        if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                        {                     
                           sendIphoneNotifications($notification_title,$notification_message,$device_token);
                        }
                      }
                      //end notification code                 
                }else
                {
                  $response=array('status'=>false,'message'=>'Job date not available!');
                }
            }else {
              //rfq start

              if(($todays_datetime<$bookingdatetime))
              {       $booking = Booking::create($data);
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
                      $subcategory_id = $subcategories;  
                      //$subcategory_id=explode(',',$subcategory_id);
                      $role_id=config('constants.ROLE_TYPE_PROVIDER_ID');
                      $providers= User::with(['category_user','profile','roles']);
                       /* $providers->whereHas('profile', function($query) use ($is_rfq) {   
                        $query->where('is_rfq',$is_rfq); 
                      }) ;*/
                      $providers->whereHas('category_user', function($query) use ($subcategory_id) {
                         $query->whereIn('category_id',$subcategory_id);
                      });   
                       $providers->whereHas('roles', function($query) use ($role_id) {
                          $query->where('id', $role_id);
                      });   
                      $providers=$providers->get();
                      $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Request Sent successfully');
                      if(!empty($providers))
                      {
                        foreach ($providers as $key => $provider) 
                        {
                            $Kilometer_distance=  $this->distance($provider->profile->latitude,$provider->profile->longitude , $latitude,$longitude , "K");
                            $radius=floatval($provider->profile->radius);
                            $Kilometer_distance=round($Kilometer_distance, 2);  
                            if($provider->profile->radius!='null' && $provider->profile->radius!='')
                            {
                               if($radius>=$Kilometer_distance)
                                {
                                  // start notification code                
                                  $notification_providerdata=User::where('id',$provider->id)->first();
                                  if($notification_providerdata)
                                  {
                                    $notification_title=config('constants.NOTIFICATION_NEW_BOOKING_REQUEST_SUBJECT');
                                    $notification_message=config('constants.NOTIFICATION_NEW_BOOKING_REQUEST_MESSAGE');
                                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                                    {
                                      if(!empty($notification_providerdata->device_token))
                                      {
                                        $device_token[]=$notification_providerdata->device_token;
                                      }                        
                                    }else
                                    {
                                      if(!empty($notification_providerdata->device_token))
                                      {
                                        $device_token[]=$notification_providerdata->device_token;
                                      }
                                    } 
                                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                                    {                     
                                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                                    }/*else
                                    {
                                       //sendIphoneNotification($title,$message,$token);
                                    } */
                                  }
                                  //end notification code 
                                }
                            }                  
                        }
                      }
                      
              }else
              {
                $response=array('status'=>false,'message'=>'Job date not available!');
              }
              //rfq end
            }  
            
        }else
        {
                $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
        $provider=$certification_data=array();
        $user_id=isset($data['user_id'])?$data['user_id']:'';
        if($user)
        {
          $validator = Validator::make($data, [
                'user_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
           $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city','category_user.category','certification','package_user','hourly_charge'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first();  
          if(isset($provider))
          {



          $subcategories=$packages=$hourly=[];
          if(!empty($provider->category_user)){
            if(count($provider->category_user)>0)
          {
            foreach ($provider->category_user as $key => $providerdata) 
            {
              if($providerdata->category->parent_id!=0){
                $subcategories[]=array('id'=>$providerdata->category->id,
                                     'title'=>$providerdata->category->title,
                                     'parent_id'=>$providerdata->category->parent_id,
                                     'is_active'=>$providerdata->category->is_active);
            }
           }  
          }
          }
          
          $provider_works_photo = $provider->getMedia('works_photo');  
          $works_photo_Images=array(); 
          if (count($provider_works_photo) > 0) 
          {
            foreach ($provider_works_photo as $key => $works_photo) {
               $works_photo_Images[]=array('id'=>$works_photo->id,
                                      'name'=>$works_photo->name,
                                      'file_name'=>$works_photo->file_name,
                                      'image_path'=>$works_photo->getFullUrl());
            }
          }
          
          $certification_img='';
          if(count($provider->certification))
          {
            foreach ($provider->certification as $key => $certification) {
               if(isset($certification) && $certification->getMedia('certification')->count() > 0 && file_exists($certification->getFirstMedia('certification')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('certification')->getFullUrl();
              }
               if(isset($certification) && $certification->getMedia('diploma')->count() > 0 && file_exists($certification->getFirstMedia('diploma')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('diploma')->getFullUrl();
              }
               if(isset($certification) && $certification->getMedia('degree')->count() > 0 && file_exists($certification->getFirstMedia('degree')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('degree')->getFullUrl();
              }
              $certification_data[]=array('id'=>$certification->id,
                                        'title'=>$certification->title,
                                        'type'=>$certification->type,
                                        'img'=>$certification_img);
            }
          }
          $certificate_conduct='';
          $nca='';
          if(isset($provider) && $provider->getMedia('certificate_conduct')->count() > 0 && file_exists($provider->getFirstMedia('certificate_conduct')->getPath()))
          {           
            $certificate_conduct=$provider->getFirstMedia('certificate_conduct')->getFullUrl();
          } 
          if(isset($provider) && $provider->getMedia('nca')->count() > 0 && file_exists($provider->getFirstMedia('nca')->getPath()))
          {           
            $nca=$provider->getFirstMedia('nca')->getFullUrl();
          } 
          $rating=0.0;
          $provider['certification_data']=$certification_data;
          $provider['nca']=$nca;
          $provider['certificate_conduct']=$certificate_conduct;
          $provider['works_photo']=$works_photo_Images;
          $provider['subcategories']=$subcategories;
                  
             
          $provider['profile_picture']='';
          $age="";                
          if(isset($provider) && $provider->getMedia('profile_picture')->count() > 0 && file_exists($provider->getFirstMedia('profile_picture')->getPath()))
          {
            $provider['profile_picture']=$provider->getFirstMedia('profile_picture')->getFullUrl();
          }  
          if(isset($provider->profile->dob) && $provider->profile->dob!='')
          {

            $age = (date('Y') - date('Y',strtotime($provider->profile->dob)));          
          }
          /*rating*/
          if($provider->profile->display_seeker_reviews==true)
          {
            $provider_review=Review::where(array('user_id'=>$user_id))->get();
            if(count($provider_review)>0)
            {
              $no_of_count=count($provider_review); 
              $provider_rating=$provider_review->sum('rating');
              $rating = $provider_rating / $no_of_count;
              $rating=(round($rating,2));
            }
          }          
          /*rating*/
          $provider['age']=(string)$age;
          $provider['rating']=$rating;
          unset($provider['media']);
          unset($provider['certification']);

          $response=array('status'=>true,'data'=>$provider,'message'=>'Record found');
          
          } else{
             $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
          } 
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
            'category_id' => 'required',
            'subcategory_id' => 'required',
           ]);
           if ($validator->fails()) { 
              return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);           
           } 
           
           $is_package=isset($data['is_package'])?$data['is_package']:0;
           $is_hourly=isset($data['is_hourly'])?$data['is_hourly']:0;
           $is_rfq=isset($data['is_rfq'])?$data['is_rfq']:0;
           $screen_name=isset($data['screen_name'])?$data['screen_name']:'';

            $user_id=$user->id;
            if($user_id){ 
                $userdata=array('screen_name'=>$screen_name);                
                $user->update($userdata);
            }
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
                $profile_data=array('is_hourly'=>$data['is_hourly'],'is_package'=>$data['is_package'],'is_rfq'=>$is_rfq);
                $profile->update($profile_data);
            }
            
           if($is_package==true)
           {
            $packagesdata=json_decode(stripslashes($request->packages));
            if(intval($packagesdata) > 0 && !empty($packagesdata))
            { 
              foreach ($packagesdata as $key => $data) {                
                $user->package_user()->create(['package_id'=>$data->package_id,'price'=>$data->price]);  
              }
            }
           } 
           if($is_hourly==true)
           {
            $hourlydata=json_decode(stripslashes($request->hourly));
            if(!empty($hourlydata))
            {               
              foreach ($hourlydata as $key => $data) {             
                $user->hourly_charge()->create(['user_id'=>$user_id,'hours'=>$data->duration,'price'=>$data->price,'type'=>$data->type]);  
              }
            }
           }         
          
          
          $response=array('status'=>true,'message'=>'Provider information successfully added.');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to save provider more information
     *
     * @return [string] message
     */
    public function addProviderMoreInfo(Request $request){
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
            'passport_number' => 'required',
            'residential_address'=>'required'
           ]);
           if ($validator->fails()) { 
              return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);           
           } 

           $location=isset($data['location'])?$data['location']:'';
           $latitude=isset($data['latitude'])?$data['latitude']:'';
           $longitude=isset($data['longitude'])?$data['longitude']:'';
           $radius=isset($data['radius'])?$data['radius']:'';
           $passport_number=isset($data['passport_number'])?$data['passport_number']:'';
           $residential_address=isset($data['residential_address'])?$data['residential_address']:'';
           $experience_level_id=isset($data['experience_level_id'])?$data['experience_level_id']:'';
           $reference=isset($data['reference'])?$data['reference']:'';
           $facebook_url=isset($data['facebook_url'])?$data['facebook_url']:'';
           $instagram_url=isset($data['instagram_url'])?$data['instagram_url']:'';
           $twitter_url=isset($data['twitter_url'])?$data['twitter_url']:'';
           $fundi_is_middlemen=isset($data['fundi_is_middlemen'])?$data['fundi_is_middlemen']:0;
           $fundi_have_tools=isset($data['fundi_have_tools'])?$data['fundi_have_tools']:0;
           $fundi_have_smartphone=isset($data['fundi_have_smartphone'])?$data['fundi_have_smartphone']:0;
           $screen_name=isset($data['screen_name'])?$data['screen_name']:'';
           $certification_text=isset($data['certification_text'])?$data['certification_text']:'';
           $diploma_text=isset($data['diploma_text'])?$data['diploma_text']:'';
           $degree_text=isset($data['degree_text'])?$data['degree_text']:'';
           $is_academy_trained=isset($data['is_academy_trained'])?$data['is_academy_trained']:0;
           $year_experience=isset($data['passout_year'])?$data['passout_year']:'';
           $reference_name1=isset($data['reference_name1'])?$data['reference_name1']:'';
           $reference_mobile_number1=isset($data['reference_mobile_number1'])?$data['reference_mobile_number1']:'';
           $reference_name2=isset($data['reference_name2'])?$data['reference_name2']:'';
           $reference_mobile_number2=isset($data['reference_mobile_number2'])?$data['reference_mobile_number2']:'';

           $user_id=$user->id;
           if($user_id){ 
                $userdata=array('screen_name'=>$screen_name);                
                $user->update($userdata);
           }
            
            $profile = Profile::where(array('user_id'=>$user_id));
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'radius'=>$radius,'passport_number'=>$passport_number,'residential_address'=>$residential_address,'year_experience'=>$year_experience,'reference'=>$reference,'facebook_url'=>$facebook_url,'instagram_url'=>$instagram_url,'twitter_url'=>$twitter_url,'fundi_is_middlemen'=>$fundi_is_middlemen,'fundi_have_tools'=>$fundi_have_tools,'fundi_have_smartphone'=>$fundi_have_smartphone,'experience_level_id'=>$experience_level_id,'is_academy_trained'=>$is_academy_trained,'reference_name1'=>$reference_name1,'reference_mobile_number1'=>$reference_mobile_number1,'reference_name2'=>$reference_name2,'reference_mobile_number2'=>$reference_mobile_number2);
                $profile->update($profile_data);
            }            
            if ($request->hasFile('certificate_conduct')){
                $file = $request->file('certificate_conduct');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('certificate_conduct');
            }
            if ($request->hasFile('nca')){
                $file = $request->file('nca');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('nca');
            }

            if ($request->hasFile('passport_image')){
                $file = $request->file('passport_image');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('passport_image');
            }           
           
            if ($request->hasFile('works_photo')){
                 $files = $request->file('works_photo');
                  $i=0;
                  foreach ($files as $file) {
                     $customname = time().$i. '.' . $file->getClientOriginalExtension();
                     $user->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('works_photo');
                       $i++;
               }
            }
            
            if(isset($certification_text) && $certification_text!='')
            {
             $certification= $user->Certification()->create(['user_id'=>$user_id,'title'=>$certification_text,'type'=>'certification']); 
              if ($request->hasFile('certification')){
                $file = $request->file('certification');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $certification->addMedia($file)->toMediaCollection('certification');
              }
            }
            if(isset($diploma_text) && $diploma_text!='')
            {
              $diploma=$user->Certification()->create(['user_id'=>$user_id,'title'=>$diploma_text,'type'=>'diploma']);
              if ($request->hasFile('diploma')){
                $file = $request->file('diploma');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $diploma->addMedia($file)->toMediaCollection('diploma');
              } 
            }
            if(isset($degree_text) && $degree_text!='')
            {
              $degree= $user->Certification()->create(['user_id'=>$user_id,'title'=>$degree_text,'type'=>'degree']); 
               if ($request->hasFile('degree')){
                $file = $request->file('degree');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $degree->addMedia($file)->toMediaCollection('degree');
              }
            }      
           
          $response=array('status'=>true,'message'=>'Provider information successfully added.');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get user provider details according to Id 
     *
     * @return [string] message
     */
    public function getUserProfile(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $provider=$subcategories=$categories=array();
        
        if($user)
        {  $user_id=$user->id;        
           $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first();            
        
          if(count($provider->category_user)>0)
          {
            foreach ($provider->category_user as $key => $providerdata) 
            {
              if($providerdata->category->parent_id!=0){
                $subcategories[]=array('id'=>$providerdata->category->id,
                                     'title'=>$providerdata->category->title,
                                     'parent_id'=>$providerdata->category->parent_id,
                                     'is_active'=>$providerdata->category->is_active);
            }
             if($providerdata->category->parent_id==0){
                $categories[]=array('id'=>$providerdata->category->id,
                                     'title'=>$providerdata->category->title,
                                     'parent_id'=>$providerdata->category->parent_id,
                                     'is_active'=>$providerdata->category->is_active);
            }
           }  
          }
          
          
          $provider['categories']=$categories;
          $provider['subcategories']=$subcategories;
                  
             
          $provider['profile_picture']='';
          if(isset($provider) && $provider->getMedia('profile_picture')->count() > 0 && file_exists($provider->getFirstMedia('profile_picture')->getPath()))
          {
            $provider['profile_picture']=$provider->getFirstMedia('profile_picture')->getFullUrl();
          }  
          unset($provider['category_user']);
          unset($provider['media']);
          $response=array('status'=>true,'data'=>$provider,'message'=>'Record found');
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
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $type=$data['type'];
            $is_type=false;
            $is_hourly=false;
            $is_rfq=false;
            $is_package=false;
            if($type=='is_hourly')
            {
              $is_hourly=true;

            }
            if($type=='is_rfq')
            {
               $is_rfq=true;
            }
            if($type=='is_package')
            {
               $is_package=true;
            }
            $user_id = $request->input('user_id'); 
            $category_id = $request->input('category_id'); 
            $subcategory_id = $request->input('subcategory_id');  
            $subcategory_id=explode(',',$subcategory_id);
            $latitude = $request->input('latitude');  
            $longitude = $request->input('longitude');  
            $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
            $experience_level_id=isset($request->experience_level_id)?$request->experience_level_id:'';
            $min_price=isset($request->min_price)?$request->min_price:'';
            $max_price=isset($request->max_price)?$request->max_price:'';
            $security_check=isset($request->security_check)?$request->security_check:'';
            
            $providers= User::with(['category_user','profile','hourly_charge','roles','profile.experience_level','package_user','review'])          
            
            ->whereHas('profile', function($query) use ($is_hourly,$is_rfq,$is_package) {
              if($is_hourly==true)
              {
                $query->where('is_hourly',$is_hourly); 

              }
              if($is_rfq==true)
              {
                $query->where('is_rfq',$is_rfq); 
              }    
              if($is_package==true)
              {
                $query->where('is_package',$is_package); 
              }                    
            }) ;
             if($is_hourly==true)
             { 
               if($min_price)
               { 
                $providers->whereHas('hourly_charge', function($query) use ($min_price) { 
                        $query->where('price', '>=', $min_price);     
                    });
               }
               if($max_price)
               { 
                $providers->whereHas('hourly_charge', function($query) use ($max_price) { 
                        $query->where('price', '<=', $max_price);     
                    });
               }
             }
              if($is_package==true)
             { 
                if($min_price)
               { 
                $providers->whereHas('package_user', function($query) use ($min_price) { 
                        $query->where('price', '>=', $min_price);     
                    });
               }
               if($max_price)
               { 
                $providers->whereHas('package_user', function($query) use ($max_price) { 
                        $query->where('price', '<=', $max_price);     
                    });
               }
             }
            
            $providers->whereHas('category_user', function($query) use ($subcategory_id) {
               $query->whereIn('category_id',$subcategory_id);
            });   
             $providers->whereHas('roles', function($query) use ($role_id) {
                $query->where('id', config('constants.ROLE_TYPE_PROVIDER_ID'));
            });      
            if($experience_level_id)
            {
              $providers->whereHas('profile', function($query) use ($experience_level_id) {  
              $query->where('experience_level_id',$experience_level_id);          
              });
            }
            if($experience_level_id)
            {
              $providers->whereHas('profile', function($query) use ($experience_level_id) { 
              $query->where('experience_level_id',$experience_level_id);          
              });
            }
            

            if(intval($security_check)>0)
            {  
               $providers->whereHas('profile', function($query) use ($security_check) {   
                  $query->where('security_check',$security_check);     
                });
            }
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $providers=$providers->offset($start_limit)->limit($end_limit)->get();                     
            $providersdata=[];
            //print_r($providers->toArray());
            
            foreach ($providers as $key => $provider) {  
                /* $providerdata=array();
                if($is_hourly==true){ 
                  $providerdata=$provider->hourly_charge()->where('price','>',$min_price)->get();
                }else{
                  $providerdata=$provider->hourly_charge();
                }*/  
                  
              $rating=0.0;      
              if(isset($provider) && $provider->getMedia('profile_picture')->count() > 0 && file_exists($provider->getFirstMedia('profile_picture')->getPath()))
              {
                 $provider['profile_picture']=$provider->getFirstMedia('profile_picture')->getFullUrl();
              }else
              {
                 $provider['profile_picture']= asset(config('constants.NO_IMAGE_URL'));
              }              
                  
               $Kilometer_distance=  $this->distance($provider->profile->latitude,$provider->profile->longitude , $latitude,$longitude , "K");
               $radius=floatval($provider->profile->radius);
               $Kilometer_distance=round($Kilometer_distance, 2);
              if($provider->profile->display_seeker_reviews==true)
              {
                $provider_review=Review::where(array('user_id'=>$provider->id))->get();
                if(count($provider_review)>0)
                {
                  $no_of_count=count($provider_review); 
                  $provider_rating=$provider_review->sum('rating');
                  $rating = $provider_rating / $no_of_count;
                  $rating=(round($rating,2));
                }
              }               
               
              if($provider->profile->radius!='null' && $provider->profile->radius!='')
               {
               if($radius>=$Kilometer_distance)
                {
                   //$providersdata[]=$provider;     
                   $providersdata[]=array('user_id'=>$provider->id,
                                          'name'=>$provider->name,
                                          'email'=>$provider->email,
                                          'mobile_number'=>$provider->mobile_number,
                                          'is_hourly'=>$provider->profile->is_hourly,
                                          'is_rfq'=>$provider->profile->is_rfq,
                                          'profile_picture'=>$provider['profile_picture'],
                                          'rating'=>$rating
                                          );              
                }
              }           
            }    
            
             if(count($providersdata))
            { 
                $response=array('status'=>true,'providers'=>$providersdata,'message'=>'Record found');
            }else
            {
                $response=array('status'=>false,'message'=>'Record not found');
            }
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get all providers jobs listing seeker end 
     *
     * @return [string] message
    */
    public function getJob(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $booking_data=$bookings=$bookingtype=array();
        $type=isset($request->type)?$request->type:'';
        if($user)
        {        
            $validator = Validator::make($data, [
                'type'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }

            $bookings= Booking::with(['category','user','user.profile','subcategory','booking_user'])->where(['requested_id'=>$user->id]);
           
            $bookings=$bookings->orderBy('datetime','desc');
            
           
            $bookings=$bookings->get();
            $booking_list[$type]=array();
            $works_photo_Images=array(); 
            $age=""; 
            $rating=0.0;
            $package_id=$package_title=$package_duration=$package_description=$quantity=$total_package_amount="";

           if(count($bookings)>0)
           {
            $bookingrecords='';   
            $job_status='';         
            foreach ($bookings as $key => $booking) 
             {
              $booking_rfq=$booking_package_data=array();   
              $subcategories=$categories=array();   
              $job_status=isset($booking->job_status)?$booking->job_status:'';
              if($booking->category!='')
              {
                 $categories[]=array('id'=>$booking->category->id,
                                     'title'=>$booking->category->title,
                                     'parent_id'=>$booking->category->parent_id,
                                     'is_active'=>$booking->category->is_active);
              }
              if(count($booking->subcategory)>0)
              {
                foreach ($booking->subcategory as $key => $subcategory) 
                {
                  $subcategories[]=array('id'=>$subcategory->category->id,
                                     'title'=>$subcategory->category->title,
                                     'parent_id'=>$subcategory->category->parent_id,
                                     'is_active'=>$subcategory->category->is_active);
                 
                }
              }   
              $profile_picture='';
              if(isset($booking->user) && $booking->user->getMedia('profile_picture')->count() > 0 && file_exists($booking->user->getFirstMedia('profile_picture')->getPath()))
              {
                $profile_picture=$booking->user->getFirstMedia('profile_picture')->getFullUrl();
              }else
              {
                  $profile_picture = asset(config('constants.NO_IMAGE_URL'));
              } 
              $booking_type='';

                if($booking->is_hourly)
                {
                  $booking_type='hourly';
                }elseif ($booking->is_rfq) {
                  $booking_type='rfq';
                }elseif ($booking->is_package) {
                  $booking_type='package';
                }
                if($type==config('constants.PAYMENT_STATUS_REQUESTED'))
                {
                  if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_hourly==1)
                     {  
                      $booking_rfq=array();
                      //condition for hourly type job  
                      $providerdata=User::with(['profile','media'])->where('id',$booking->user_id)->first();

                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      } 
                      if (isset($providerdata) && ($providerdata->getMedia('works_photo')->count() > 0)) 
                       {
                        foreach ($providerdata->getMedia('works_photo') as $key => $works_photo) {
                           $works_photo_Images[]=array('id'=>$works_photo->id,
                                                  'name'=>$works_photo->name,
                                                  'file_name'=>$works_photo->file_name,
                                                  'image_path'=>$works_photo->getFullUrl());
                        }
                       } 
                       if(isset($providerdata->profile->dob) && $providerdata->profile->dob!='')
                      {

                        $age = (date('Y') - date('Y',strtotime($providerdata->profile->dob)));          
                      } 
                      $display_seeker_reviews=isset($providerdata->profile->display_seeker_reviews)?$providerdata->profile->display_seeker_reviews:0;
                      if($display_seeker_reviews==true)
                      {
                        $provider_review=Review::where(array('user_id'=>$providerdata->profile->user_id))->get();
                        if(count($provider_review)>0)
                        {
                          $no_of_count=count($provider_review); 
                          $provider_rating=$provider_review->sum('rating');
                          $rating = $provider_rating / $no_of_count;
                          $rating=(round($rating,2));
                        }
                      }
                      $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                          
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                       $booking_list[$type][]=$bookingrecords;
                       
                     }else if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_package==1)
                     {
                        $booking_rfq=array();
                        //Package information
                        $booking_package=Package::where('id',$booking->package_id)->first();
                        //end Package information
                        //condition for package type job  
                        $providerdata=User::with(['profile','media'])->where('id',$booking->user_id)->first();

                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        } 
                        if (isset($providerdata) && ($providerdata->getMedia('works_photo')->count() > 0)) 
                        {
                        foreach ($providerdata->getMedia('works_photo') as $key => $works_photo) {
                           $works_photo_Images[]=array('id'=>$works_photo->id,
                                                  'name'=>$works_photo->name,
                                                  'file_name'=>$works_photo->file_name,
                                                  'image_path'=>$works_photo->getFullUrl());
                        }
                        } 
                        if(isset($providerdata->profile->dob) && $providerdata->profile->dob!='')
                        {

                        $age = (date('Y') - date('Y',strtotime($providerdata->profile->dob)));          
                        } 
                        $display_seeker_reviews=isset($providerdata->profile->display_seeker_reviews)?$providerdata->profile->display_seeker_reviews:0;
                      if($display_seeker_reviews==true)
                      {
                        $provider_review=Review::where(array('user_id'=>$providerdata->profile->user_id))->get();
                        if(count($provider_review)>0)
                        {
                          $no_of_count=count($provider_review); 
                          $provider_rating=$provider_review->sum('rating');
                          $rating = $provider_rating / $no_of_count;
                          $rating=(round($rating,2));
                        }
                        }
                        $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                          
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>isset($booking_package->id)?(string)$booking_package->id:'',
                                          'package_title'=>isset($booking_package->title)?$booking_package->title:'',
                                          'package_duration'=>isset($booking_package->duration)?(string)$booking_package->duration:'',
                                          'package_description'=>isset($booking_package->description)?$booking_package->description:'',
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                        $booking_list[$type][]=$bookingrecords;
                     }else if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_rfq==1)
                     { 
                       $booking_rfq=array();     
                       $providerdata=User::with(['profile'])->where('id',$booking->user_id)->first();
                       $provider_name=isset($providerdata->name)?$providerdata->name:'';
                       $provider_email=isset($providerdata->email)?$providerdata->email:'';
                       $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';      
                       if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }  
                       //condition for rfq type job
                       $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'status'=>config('constants.PAYMENT_STATUS_ACCEPTED')))->first();

                       if(isset($booking_users))
                       {
                        $booking_providerdata=User::with(['profile','media'])->where('id',$booking_users->user_id)->first();
                        $booking_provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                        $booking_provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                        $booking_provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                        if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        } 
                        if (isset($booking_providerdata) && ($booking_providerdata->getMedia('works_photo')->count() > 0)) 
                         {
                          foreach ($booking_providerdata->getMedia('works_photo') as $key => $works_photo) {
                             $works_photo_Images[]=array('id'=>$works_photo->id,
                                                    'name'=>$works_photo->name,
                                                    'file_name'=>$works_photo->file_name,
                                                    'image_path'=>$works_photo->getFullUrl());
                          }
                         } 
                         if(isset($booking_providerdata->profile->dob) && $booking_providerdata->profile->dob!='')
                        {

                          $age = (date('Y') - date('Y',strtotime($booking_providerdata->profile->dob)));          
                        } 
                        $display_seeker_reviews=isset($providerdata->profile->display_seeker_reviews)?$providerdata->profile->display_seeker_reviews:0;
                      if($display_seeker_reviews==true)
                        {
                          $provider_review=Review::where(array('user_id'=>$booking_providerdata->profile->user_id))->get();
                          if(count($provider_review)>0)
                          {
                            $no_of_count=count($provider_review); 
                            $provider_rating=$provider_review->sum('rating');
                            $rating = $provider_rating / $no_of_count;
                            $rating=(round($rating,2));
                          }
                        }
                         $booking_rfq[]=array('booking_id'=>$booking_users->booking_id,
                                             'user_id'=>$booking_users->user_id,
                                             'is_rfq'=>$booking_users->is_rfq,
                                             'budget'=>isset($booking_users->budget)?(string)$booking_users->budget:'',
                                             'service_datetime'=>$booking_users->service_datetime,
                                             'requirement'=>isset($booking_users->requirement)?$booking_users->requirement:'',
                                             'comment'=>isset($booking_users->comment)?$booking_users->comment:'',
                                             'is_quoted'=>$booking_users->is_quoted,
                                             'reason'=>isset($booking_users->reason)?$booking_users->reason:'',
                                             'status'=>$booking_users->status,
                                             'name'=>$booking_provider_name,
                                             'email'=>$booking_provider_email,
                                            'mobile_number'=>$booking_provider_mobile_number,
                                            'profile_picture'=>$booking_provider_profile_picture
                                             );
                         $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                          
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                       $booking_list[$type][]=$bookingrecords;   
                       }
                     }
                }else if($type==config('constants.PAYMENT_STATUS_PENDING')) 
                {
                     $booking_rfq=array();     
                     if(($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') || $booking->status==config('constants.PAYMENT_STATUS_QUOTED') || $booking->status==config('constants.PAYMENT_STATUS_DECLINED')) && $booking->is_hourly==1)
                     {  
                        //pending jobs means jobs that have hourly with requested, quoted, declined     
                        $providerdata=User::with(['profile','media'])->where('id',$booking->user_id)->first();                         
                         if(isset($providerdata->profile->dob) && $providerdata->profile->dob!='')
                        {

                          $age = (date('Y') - date('Y',strtotime($providerdata->profile->dob)));          
                        } 
                        $display_seeker_reviews=isset($providerdata->profile->display_seeker_reviews)?$providerdata->profile->display_seeker_reviews:0;
                        if($display_seeker_reviews==true)
                        {
                          $provider_review=Review::where(array('user_id'=>$providerdata->profile->user_id))->get();
                          if(count($provider_review)>0)
                          {
                            $no_of_count=count($provider_review); 
                            $provider_rating=$provider_review->sum('rating');
                            $rating = $provider_rating / $no_of_count;
                            $rating=(round($rating,2));
                          }
                        }
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }    
                        if (isset($providerdata) && ($providerdata->getMedia('works_photo')->count() > 0)) 
                        {
                          foreach ($providerdata->getMedia('works_photo') as $key => $works_photo) {
                             $works_photo_Images[]=array('id'=>$works_photo->id,
                                                    'name'=>$works_photo->name,
                                                    'file_name'=>$works_photo->file_name,
                                                    'image_path'=>$works_photo->getFullUrl());
                          }
                        }             
                         $bookingrecords=array('booking_id'=>$booking->id,
                                            'type'=>$booking_type,
                                            'category_id'=>$booking->category_id,
                                            'user_id'=>$booking->user_id,
                                            'title'=>$booking->title,
                                            'description'=>$booking->description,
                                            'location'=>$booking->location,
                                            'latitude'=>$booking->latitude,
                                            'longitude'=>$booking->longitude,
                                            'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                            'is_rfq'=>$booking->is_rfq,
                                            'booking_rfq'=>$booking_rfq,
                                            'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                            'is_hourly'=>$booking->is_hourly,
                                            'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                            'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                            'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                            'datetime'=>$booking->datetime,
                                            'requested_id'=>$booking->requested_id,
                                            'categories'=>$categories,
                                            'subcategories'=>$subcategories,
                                            'status'=>isset($booking->status)?$booking->status:'',
                                            'name'=>$provider_name,
                                            'email'=>$provider_email,
                                            'mobile_number'=>$provider_mobile_number,
                                            'profile_picture'=>$provider_profile_picture,
                                            'works_photo'=>$works_photo_Images,
                                            'age'=>(string)$age,
                                            'rating'=>$rating,
                                            'is_package'=>$booking->is_package,
                                            'package_id'=>$package_id,
                                            'package_title'=>$package_title,
                                            'package_duration'=>$package_duration,
                                            'package_description'=>$package_description,
                                            'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                            'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                            'is_quoted'=>$booking->is_quoted,
                                            'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                            'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                            'comment'=>isset($booking->comment)?$booking->comment:'',
                                            'job_status'=>$job_status
                                            );                     
                         $booking_list[$type][]=$bookingrecords;  
                     }else if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') && $booking->is_rfq==1)
                     {
                       $booking_rfq=array();
                       //condition for rfq type job                       
                       $booking_declined=$booking->booking_user()->where(array('booking_id'=>$booking->id,'status'=>config('constants.PAYMENT_STATUS_DECLINED')))->first();
                       if(isset($booking_declined))
                       {
                        $booking_providerdata=User::with(['profile'])->where('id',$booking_declined->user_id)->first();
                        $booking_provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                        $booking_provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                        $booking_provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                        if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        
                         
                         $booking_rfq[]=array('booking_id'=>$booking_declined->booking_id,
                                             'user_id'=>$booking_declined->user_id,
                                             'is_rfq'=>$booking_declined->is_rfq,
                                             'budget'=>isset($booking_declined->budget)?(string)$booking_declined->budget:'',
                                             'service_datetime'=>$booking_declined->service_datetime,
                                             'requirement'=>isset($booking_declined->requirement)?$booking_declined->requirement:'',
                                             'comment'=>isset($booking_declined->comment)?$booking_declined->comment:'',
                                             'is_quoted'=>$booking_declined->is_quoted,
                                             'reason'=>isset($booking_declined->reason)?$booking_declined->reason:'',
                                             'status'=>$booking_declined->status,
                                             'name'=>$booking_provider_name,
                                             'email'=>$booking_provider_email,
                                             'mobile_number'=>$booking_provider_mobile_number,
                                             'profile_picture'=>$booking_provider_profile_picture);
                            
                       }
                       $booking_quote=$booking->booking_user()->where(array('booking_id'=>$booking->id,'status'=>config('constants.PAYMENT_STATUS_QUOTED')))->first();
                       if(isset($booking_quote))
                       { 
                         $booking_providerdata=User::with(['profile'])->where('id',$booking_quote->user_id)->first();

                          $booking_provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                          $booking_provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                          $booking_provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                          if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                          {
                                $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                          }else
                          {
                                $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                          } 
                         if (isset($booking_providerdata) && ($booking_providerdata->getMedia('works_photo')->count() > 0)) 
                         {
                          foreach ($booking_providerdata->getMedia('works_photo') as $key => $works_photo) {
                             $works_photo_Images[]=array('id'=>$works_photo->id,
                                                    'name'=>$works_photo->name,
                                                    'file_name'=>$works_photo->file_name,
                                                    'image_path'=>$works_photo->getFullUrl());
                          }
                         }    
                         if(isset($booking_providerdata->profile->dob) && $booking_providerdata->profile->dob!='')
                          {

                            $age = (date('Y') - date('Y',strtotime($booking_providerdata->profile->dob)));          
                          } 
                          $display_seeker_reviews=isset($booking_providerdata->profile->display_seeker_reviews)?$booking_providerdata->profile->display_seeker_reviews:0;
                         if($display_seeker_reviews==true)
                          {
                            $provider_review=Review::where(array('user_id'=>$booking_providerdata->profile->user_id))->get();
                            if(count($provider_review)>0)
                            {
                              $no_of_count=count($provider_review); 
                              $provider_rating=$provider_review->sum('rating');
                              $rating = $provider_rating / $no_of_count;
                              $rating=(round($rating,2));
                            }
                          }                   
                         $booking_rfq[]=array('booking_id'=>$booking_quote->booking_id,
                                             'user_id'=>$booking_quote->user_id,
                                             'is_rfq'=>$booking_quote->is_rfq,
                                             'budget'=>isset($booking_quote->budget)?(string)$booking_quote->budget:'',
                                             'service_datetime'=>$booking_quote->service_datetime,
                                             'requirement'=>isset($booking_quote->requirement)?$booking_quote->requirement:'',
                                             'comment'=>isset($booking_quote->comment)?$booking_quote->comment:'',
                                             'is_quoted'=>$booking_quote->is_quoted,
                                             'reason'=>isset($booking_quote->reason)?$booking_quote->reason:'',
                                             'status'=>$booking_quote->status,
                                             'name'=>$booking_provider_name,
                                             'email'=>$booking_provider_email,
                                             'mobile_number'=>$booking_provider_mobile_number,
                                             'profile_picture'=>$booking_provider_profile_picture);         
                       }

                      
                       $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                         
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>isset($booking_provider_name)?$booking_provider_name:'',
                                          'email'=>isset($booking_provider_email)?$booking_provider_email:'',
                                          'mobile_number'=>isset($booking_provider_mobile_number)?$booking_provider_mobile_number:'',
                                          'profile_picture'=>isset($booking_provider_profile_picture)?$booking_provider_profile_picture:'',
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                       $booking_list[$type][]=$bookingrecords;                       
                       //is_rfq type jobs  
                     }else if(($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') || $booking->status==config('constants.PAYMENT_STATUS_QUOTED') || $booking->status==config('constants.PAYMENT_STATUS_DECLINED')) && $booking->is_package==1)
                     {   
                      $booking_package=Package::where('id',$booking->package_id)->first();
                      //end Package information
                      $providerdata=User::with(['profile','media'])->where('id',$booking->user_id)->first();                         
                       if(isset($providerdata->profile->dob) && $providerdata->profile->dob!='')
                      {

                        $age = (date('Y') - date('Y',strtotime($providerdata->profile->dob)));          
                      } 
                      $display_seeker_reviews=isset($providerdata->profile->display_seeker_reviews)?$providerdata->profile->display_seeker_reviews:0;
                      if($display_seeker_reviews==true)
                      {
                        $provider_review=Review::where(array('user_id'=>$providerdata->profile->user_id))->get();
                        if(count($provider_review)>0)
                        {
                          $no_of_count=count($provider_review); 
                          $provider_rating=$provider_review->sum('rating');
                          $rating = $provider_rating / $no_of_count;
                          $rating=(round($rating,2));
                        }
                      }
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }    
                      if (isset($providerdata) && ($providerdata->getMedia('works_photo')->count() > 0)) 
                      {
                        foreach ($providerdata->getMedia('works_photo') as $key => $works_photo) {
                           $works_photo_Images[]=array('id'=>$works_photo->id,
                                                  'name'=>$works_photo->name,
                                                  'file_name'=>$works_photo->file_name,
                                                  'image_path'=>$works_photo->getFullUrl());
                        }
                      }  

                       $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                         
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>isset($booking_package->id)?(string)$booking_package->id:'',
                                          'package_title'=>isset($booking_package->title)?$booking_package->title:'',
                                          'package_duration'=>isset($booking_package->duration)?(string)$booking_package->duration:'',
                                          'package_description'=>isset($booking_package->description)?$booking_package->description:'',
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                       $booking_list[$type][]=$bookingrecords;            

                     }
                }else if($type==config('constants.PAYMENT_STATUS_COMPLETED'))
                {
                  $booking_rfq=array();
                  if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_hourly==1)
                  {
                    $booking_providerdata=User::with(['profile'])->where('id',$booking->user_id)->first();
                        $provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                        $provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                        $provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                         if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                          {
                                $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                          }else
                          {
                                $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                          }
                          if(isset($booking_providerdata->profile->dob) && $booking_providerdata->profile->dob!='')
                          {

                            $age = (date('Y') - date('Y',strtotime($booking_providerdata->profile->dob)));          
                          } 
                          $display_seeker_reviews=isset($booking_providerdata->profile->display_seeker_reviews)?$booking_providerdata->profile->display_seeker_reviews:0;
                        if($display_seeker_reviews==true)
                          {
                            $provider_review=Review::where(array('user_id'=>$booking_providerdata->profile->user_id))->get();
                            if(count($provider_review)>0)
                            {
                              $no_of_count=count($provider_review); 
                              $provider_rating=$provider_review->sum('rating');
                              $rating = $provider_rating / $no_of_count;
                              $rating=(round($rating,2));
                            }
                          }  
                      $bookingrecords=array('booking_id'=>$booking->id,
                                            'type'=>$booking_type,
                                            'category_id'=>$booking->category_id,
                                            'user_id'=>$booking->user_id,
                                            'title'=>$booking->title,
                                            'description'=>$booking->description,
                                            'location'=>$booking->location,
                                            'latitude'=>$booking->latitude,
                                            'longitude'=>$booking->longitude,
                                            'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                           
                                            'is_rfq'=>$booking->is_rfq,
                                            'booking_rfq'=>$booking_rfq,
                                            'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                            'is_hourly'=>$booking->is_hourly,
                                            'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                            'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                            'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                            'datetime'=>$booking->datetime,
                                            'requested_id'=>$booking->requested_id,
                                            'categories'=>$categories,
                                            'subcategories'=>$subcategories,
                                            'status'=>isset($booking->status)?$booking->status:'',
                                            'name'=>$provider_name,
                                            'email'=>$provider_email,
                                            'mobile_number'=>$provider_mobile_number,
                                            'profile_picture'=>$booking_provider_profile_picture,
                                            'age'=>(string)$age,
                                            'rating'=>$rating,
                                            'is_package'=>$booking->is_package,
                                            'package_id'=>$package_id,
                                            'package_title'=>$package_title,
                                            'package_duration'=>$package_duration,
                                            'package_description'=>$package_description,
                                            'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                            'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                            'is_quoted'=>$booking->is_quoted,
                                            'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                            'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                            'comment'=>isset($booking->comment)?$booking->comment:'',
                                            'job_status'=>$job_status
                                            );  
                      $booking_list[$type][]=$bookingrecords;
                  }else if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_package==1)
                    {
                      $booking_package=Package::where('id',$booking->package_id)->first();
                      //end Package information
                      $booking_providerdata=User::with(['profile'])->where('id',$booking->user_id)->first();
                      $provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                      $provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                      $provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                       if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        if(isset($booking_providerdata->profile->dob) && $booking_providerdata->profile->dob!='')
                        {

                          $age = (date('Y') - date('Y',strtotime($booking_providerdata->profile->dob)));          
                        } 
                        $display_seeker_reviews=isset($booking_providerdata->profile->display_seeker_reviews)?$booking_providerdata->profile->display_seeker_reviews:0;
                        if($display_seeker_reviews==true)
                        {
                          $provider_review=Review::where(array('user_id'=>$booking_providerdata->profile->user_id))->get();
                          if(count($provider_review)>0)
                          {
                            $no_of_count=count($provider_review); 
                            $provider_rating=$provider_review->sum('rating');
                            $rating = $provider_rating / $no_of_count;
                            $rating=(round($rating,2));
                          }
                        }  
                      $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                         
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$booking_provider_profile_picture,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>isset($booking_package->id)?(string)$booking_package->id:'',
                                          'package_title'=>isset($booking_package->title)?$booking_package->title:'',
                                          'package_duration'=>isset($booking_package->duration)?(string)$booking_package->duration:'',
                                          'package_description'=>isset($booking_package->description)?$booking_package->description:'',
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );  
                      $booking_list[$type][]=$bookingrecords;
                    } else if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_rfq==1)
                  {
                    $booking_user=$booking->booking_user()->where(array('booking_id'=>$booking->id,'status'=>config('constants.PAYMENT_STATUS_COMPLETED')))->first();

                    if($booking_user)
                    {
                     $booking_providerdata=User::with(['profile'])->where('id',$booking_user->user_id)->first();
                        $booking_provider_name=isset($booking_providerdata->name)?$booking_providerdata->name:'';
                        $booking_provider_email=isset($booking_providerdata->email)?$booking_providerdata->email:'';
                        $booking_provider_mobile_number=isset($booking_providerdata->mobile_number)?$booking_providerdata->mobile_number:'';
                        if(isset($booking_providerdata) && $booking_providerdata->getMedia('profile_picture')->count() > 0 && file_exists($booking_providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $booking_provider_profile_picture=$booking_providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $booking_provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                         if(isset($booking_providerdata->profile->dob) && $booking_providerdata->profile->dob!='')
                          {

                            $age = (date('Y') - date('Y',strtotime($booking_providerdata->profile->dob)));          
                          } 
                        $display_seeker_reviews=isset($booking_providerdata->profile->display_seeker_reviews)?$booking_providerdata->profile->display_seeker_reviews:0;
                        if($display_seeker_reviews==true)
                          {
                            $provider_review=Review::where(array('user_id'=>$booking_providerdata->profile->user_id))->get();
                            if(count($provider_review)>0)
                            {
                              $no_of_count=count($provider_review); 
                              $provider_rating=$provider_review->sum('rating');
                              $rating = $provider_rating / $no_of_count;
                              $rating=(round($rating,2));
                            }
                          } 
                        
                         
                         $booking_rfq[]=array('booking_id'=>$booking_user->booking_id,
                                             'user_id'=>$booking_user->user_id,
                                             'is_rfq'=>$booking_user->is_rfq,
                                             'budget'=>isset($booking_user->budget)?(string)$booking_user->budget:'',
                                             'service_datetime'=>$booking_user->service_datetime,
                                             'requirement'=>isset($booking_user->requirement)?$booking_user->requirement:'',
                                             'comment'=>isset($booking_user->comment)?$booking_user->comment:'',
                                             'is_quoted'=>$booking_user->is_quoted,
                                             'reason'=>isset($booking_user->reason)?$booking_user->reason:'',
                                             'status'=>$booking_user->status,
                                             'name'=>$booking_provider_name,
                                             'email'=>$booking_provider_email,
                                             'mobile_number'=>$booking_provider_mobile_number,
                                             'profile_picture'=>$booking_provider_profile_picture);
                             $bookingrecords=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',                                          
                                          'is_rfq'=>$booking->is_rfq,
                                          'booking_rfq'=>$booking_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>isset($booking->status)?$booking->status:'',
                                          'name'=>$booking_provider_name,
                                          'email'=>$booking_provider_email,
                                          'mobile_number'=>$booking_provider_mobile_number,
                                          'profile_picture'=>$booking_provider_profile_picture,
                                          'works_photo'=>$works_photo_Images,
                                          'age'=>(string)$age,
                                          'rating'=>$rating,
                                          'is_package'=>$booking->is_package,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>isset($booking->quantity)?(string)$booking->quantity:'',
                                          'total_package_amount'=>isset($booking->total_package_amount)?(string)$booking->total_package_amount:'',
                                          'is_quoted'=>$booking->is_quoted,
                                          'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                          'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                          'comment'=>isset($booking->comment)?$booking->comment:'',
                                          'job_status'=>$job_status
                                          );                     
                       $booking_list[$type][]=$bookingrecords;  
                      }
                  }                    
                }                 
              }            
            }
            if(count($booking_list[$type])>0)
            {
               $booking_data=$booking_list;
               $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'record found');
            }else
            {
               $booking_data=$booking_list;
               $response=array('status'=>false,'bookingdata'=>$booking_data,'message'=>'no record found');
            }
            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get all my provider jobs listing 
     *
     * @return [string] message
    */
    public function getMyJobs(Request $request){
        $userdata = Auth::user(); 
        $data = $request->all(); 
        $bookingdata=$booking_data=$bookings=$bookingtype=$booking_package_data=$bookingrfq_data=array();
        $type=isset($request->type)?$request->type:'';
        $package_id=$package_title=$package_duration=$package_description=$quantity=$total_package_amount="";

        if($userdata)
        {          
            $validator = Validator::make($data, [
                'type'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            //$end_limit =config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $bookings= Booking::with(['category','user','user.profile','subcategory','booking_user']);               
            //$start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            //$bookings=$bookings->offset($start_limit)->limit($end_limit)->get();   
            $bookings=$bookings->get(); 
            $job_status='';  
            
            $booking_array[$type]=$device_token=array();
            if(count($bookings)>0)
            {
             
             foreach ($bookings as $key => $booking) 
             {

                $job_status=isset($booking->job_status)?$booking->job_status:'';
                 $subcategories=$categories=array();   
                if($booking->category!='')
                {
                   $categories[]=array('id'=>$booking->category->id,
                                       'title'=>$booking->category->title,
                                       'parent_id'=>$booking->category->parent_id,
                                       'is_active'=>$booking->category->is_active);
                }
                if(count($booking->subcategory)>0)
                {
                  foreach ($booking->subcategory as $key => $subcategory) 
                  {
                    $subcategories[]=array('id'=>$subcategory->category->id,
                                       'title'=>$subcategory->category->title,
                                       'parent_id'=>$subcategory->category->parent_id,
                                       'is_active'=>$subcategory->category->is_active);                 
                  }
                }   
                $booking_type='';
                if($booking->is_hourly)
                {
                  $booking_type='hourly';
                }elseif ($booking->is_rfq) {
                  $booking_type='rfq';
                }elseif ($booking->is_package) {
                  $booking_type='package';
                }
                if(isset($booking->user) && $booking->user->getMedia('profile_picture')->count() > 0 && file_exists($booking->user->getFirstMedia('profile_picture')->getPath()))
                  {
                      $provider_profile_picture=$booking->user->getFirstMedia('profile_picture')->getFullUrl();
                  }else
                  {
                      $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                  }
              //job condition  declined
                 //&& (strtotime($booking->datetime) > strtotime(date("Y-m-d H:i:s")))
              if($type==config('constants.PAYMENT_STATUS_DECLINED'))
              {
                if($booking->status=='declined' && $booking->is_hourly==1 && ($booking->user_id==$userdata->id)){
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                    //$booking_array[$type][]=$booking;
                     $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status=='declined' && $booking->is_package==1 && ($booking->user_id==$userdata->id)){
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                      if($booking->is_package==1)
                      {
                        $booking_package=Package::where('id',$booking->package_id)->first();
                        $package_id=isset($booking_package->id)?$booking_package->id:'';
                        $package_title=isset($booking_package->title)?$booking_package->title:'';
                        $package_duration=isset($booking_package->duration)?$booking_package->duration:'';
                        $package_description=isset($booking_package->description)?$booking_package->description:'';
                        $quantity=isset($booking->quantity)?$booking->quantity:'';
                        $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                      }                    
                     //$booking_array[$type][]=$booking;
                     $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED')  && $booking->is_rfq==1){ 
                      $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$userdata->id,'status'=>config('constants.PAYMENT_STATUS_DECLINED')))->first();
                      if(isset($booking_users)){
                        $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                      }
                }
              }else if($type==config('constants.PAYMENT_STATUS_PENDING')) {
                //job condition  pending
                if($booking->status==config('constants.PAYMENT_STATUS_QUOTED') && $booking->is_hourly==1  && ($booking->user_id==$userdata->id)) {
                    //$booking_array[$type][]=$booking;
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                  $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_QUOTED') && $booking->is_package==1  && ($booking->user_id==$userdata->id)) {
                    //$booking_array[$type][]=$booking;
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                      if($booking->is_package==1)
                      {
                        $booking_package=Package::where('id',$booking->package_id)->first();
                        $package_id=isset($booking_package->id)?$booking_package->id:'';
                        $package_title=isset($booking_package->title)?$booking_package->title:'';
                        $package_duration=isset($booking_package->duration)?$booking_package->duration:'';
                        $package_description=isset($booking_package->description)?$booking_package->description:'';
                        $quantity=isset($booking->quantity)?$booking->quantity:'';
                        $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                      }
                  $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') && $booking->is_rfq==1){ 
                      $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$userdata->id,'status'=>config('constants.PAYMENT_STATUS_QUOTED')))->first();
                      if(isset($booking_users)){
                        $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                      }
                }
              }
              else if($type==config('constants.PAYMENT_STATUS_REQUESTED'))
              {  
                 if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') && $booking->is_hourly==1 && ($booking->user_id==$userdata->id)){
                        $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }

                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;

                   
                 }else if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') && $booking->is_package==1 && ($booking->user_id==$userdata->id)){

                        $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        if($booking->is_package==1)
                        {
                          $booking_package=Package::where('id',$booking->package_id)->first();
                          $package_id=isset($booking_package->id)?$booking_package->id:'';
                          $package_title=isset($booking_package->title)?$booking_package->title:'';
                          $package_duration=isset($booking_package->duration)?$booking_package->duration:'';
                          $package_description=isset($booking_package->description)?$booking_package->description:'';
                          $quantity=isset($booking->quantity)?$booking->quantity:'';
                          $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                        }
                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;   
                     
                 }else if($booking->status==config('constants.PAYMENT_STATUS_REQUESTED') && $booking->is_rfq==1 && ($booking->user_id==0)){
                  $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$userdata->id))->get();
                  /*if($booking_users->count()<0)
                  {
                    echo $booking_users->count();
                  }*/
                 /* print_r($booking_users->count()>0);*/


                  if(!$booking_users->count()>0)
                  {
                    
                    //$booking_array[$type][]=$booking;                
                    $booking_latitude=$booking->latitude;
                    $booking_longitude=$booking->longitude;
                    $provider_user_id=isset($booking->user->profile->user_id)?$booking->user->profile->user_id:'';
                    $provider_name=$provider_email=$provider_mobile_number=$provider_profile_picture=$provider_latitude=$provider_longitude=$provider_radius=$provider_location='';
                    if($provider_user_id)
                    {

                      $provider_latitude=isset($booking->user->profile->latitude)?$booking->user->profile->latitude:'';
                      $provider_longitude=isset($booking->user->profile->longitude)?$booking->user->profile->longitude:''; 
                      $provider_radius=isset($booking->user->profile->radius)?$booking->user->profile->radius:'';   
                      $provider_location=isset($booking->user->profile->work_address)?$booking->user->profile->work_address:'';           
                     
                      
                     }

                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=$providerdata->name;
                      $provider_email=$providerdata->email;
                      $provider_mobile_number=$providerdata->mobile_number;
                      if($provider_latitude=='' || $provider_longitude=='' || $provider_radius=='')
                      {
                        $provider_latitude=$providerdata->profile->latitude;
                        $provider_longitude=$providerdata->profile->longitude;
                        $provider_radius=$providerdata->profile->radius;
                      }  
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                      $Kilometer_distance=  $this->distance($provider_latitude,$provider_longitude, $booking_latitude,$booking_longitude , "K");
                      $provider_radius=floatval($provider_radius);
                      $Kilometer_distance=round($Kilometer_distance, 2);  

                   if($provider_radius!='null' && $provider_radius!='')
                      {
                       if($provider_radius>=$Kilometer_distance)
                        { 
                           $bookingtype=array(
                                            'booking_id'=>$booking->id,
                                            'type'=>$booking_type,
                                            'category_id'=>$booking->category_id,
                                            'user_id'=>$booking->user_id,
                                            'title'=>$booking->title,
                                            'description'=>$booking->description,
                                            'location'=>$booking->location,
                                            'latitude'=>$booking->latitude,
                                            'longitude'=>$booking->longitude,
                                            'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                            'is_rfq'=>$booking->is_rfq,
                                            'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                            'is_hourly'=>$booking->is_hourly,
                                            'is_package'=>$booking->is_package,
                                            'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                            'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                            'datetime'=>$booking->datetime,
                                            'requested_id'=>$booking->requested_id,
                                            'categories'=>$categories,
                                            'subcategories'=>$subcategories,
                                            'status'=>$booking->status,
                                            'name'=>$provider_name,
                                            'email'=>$provider_email,
                                            'mobile_number'=>$provider_mobile_number,
                                            'profile_picture'=>$provider_profile_picture,
                                            'package_id'=>$package_id,
                                            'package_title'=>$package_title,
                                            'package_duration'=>$package_duration,
                                            'package_description'=>$package_description,
                                            'quantity'=>$quantity,
                                            'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                            'job_status'=>$job_status,
                                            'booking_rfq'=>$bookingrfq_data
                                            );  
                           $booking_array[$type][]=$bookingtype; 
                         
                        }                  
                    }
                 }else
                 {                  
                 }
               }                 
              }else if($type==config('constants.PAYMENT_STATUS_COMPLETED')) {
                 //job condition  completed
                if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_hourly==1 && ($booking->user_id==$userdata->id)) {
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                    //$booking_array[$type][]=$booking;
                    $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_package==1 && ($booking->user_id==$userdata->id)) {
                      $providerdata=User::with(['profile'])->where('id',$userdata->id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                      if($booking->is_package==1)
                      {
                        $booking_package=Package::where('id',$booking->package_id)->first();
                        $package_id=isset($booking_package->id)?$booking_package->id:'';
                        $package_title=isset($booking_package->title)?$booking_package->title:'';
                        $package_duration=isset($booking_package->duration)?$booking_package->duration:'';
                        $package_description=isset($booking_package->description)?$booking_package->description:'';
                        $quantity=isset($booking->quantity)?$booking->quantity:'';
                        $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                      }
                    //$booking_array[$type][]=$booking;
                    $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_COMPLETED') && $booking->is_rfq==1){ 
                      $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$userdata->id,'status'=>config('constants.PAYMENT_STATUS_COMPLETED')))->first();
                      if(isset($booking_users)){
                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$booking->user->name,
                                          'email'=>$booking->user->email,
                                          'mobile_number'=>$booking->user->mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                         $booking_array[$type][]=$bookingdata;
                      }
                }
              }else if($type==config('constants.PAYMENT_STATUS_ACCEPTED')) {
                  //job condition  accepted
                  if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_hourly==1  && ($booking->user_id==$userdata->id)){
                      $providerdata=User::with(['profile'])->where('id',$booking->user_id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                    //$booking_array[$type][]=$booking;
                    $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_package==1  && ($booking->user_id==$userdata->id)){
                      $providerdata=User::with(['profile'])->where('id',$booking->user_id)->first();
                      $provider_name=isset($providerdata->name)?$providerdata->name:'';
                      $provider_email=isset($providerdata->email)?$providerdata->email:'';
                      $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                      if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                      {
                            $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                      }else
                      {
                            $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                      }
                      if($booking->is_package==1)
                      {
                        $booking_package=Package::where('id',$booking->package_id)->first();
                        $package_id=isset($booking_package->id)?$booking_package->id:'';
                        $package_title=isset($booking_package->title)?$booking_package->title:'';
                        $package_duration=isset($booking_package->duration)?$booking_package->duration:'';
                        $package_description=isset($booking_package->description)?$booking_package->description:'';
                        $quantity=isset($booking->quantity)?$booking->quantity:'';
                        $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                      }
                    //$booking_array[$type][]=$booking;
                    $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                      $booking_array[$type][]=$bookingdata;
                }else if($booking->status==config('constants.PAYMENT_STATUS_ACCEPTED') && $booking->is_rfq==1){ 
                 $booking_users=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$userdata->id,'status'=>config('constants.PAYMENT_STATUS_ACCEPTED')))->first();
                      if(isset($booking_users)){

                        $providerdata=User::with(['profile'])->where('id',$booking_users->user_id)->first();
                        $provider_name=isset($providerdata->name)?$providerdata->name:'';
                        $provider_email=isset($providerdata->email)?$providerdata->email:'';
                        $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                        if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                        {
                              $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                        }else
                        {
                              $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                        }
                        //booking rfq data

                         $bookingrfq_data[]=array('booking_id'=>$booking_users->booking_id,
                                             'user_id'=>$booking_users->user_id,
                                             'is_rfq'=>$booking_users->is_rfq,
                                             'budget'=>isset($booking_users->budget)?(string)$booking_users->budget:'',
                                             'service_datetime'=>$booking_users->service_datetime,
                                             'requirement'=>isset($booking_users->requirement)?$booking_users->requirement:'',
                                             'comment'=>isset($booking_users->comment)?$booking_users->comment:'',
                                             'is_quoted'=>$booking_users->is_quoted,
                                             'reason'=>isset($booking_users->reason)?$booking_users->reason:'',
                                             'status'=>$booking_users->status,
                                             'name'=>$provider_name,
                                             'email'=>$provider_email,
                                            'mobile_number'=>$provider_mobile_number,
                                            'profile_picture'=>$provider_profile_picture
                                             );

                        //end booking rfq data


                        //$booking_array[$type][]=$booking;
                        $bookingdata=array('booking_id'=>$booking->id,
                                          'type'=>$booking_type,
                                          'category_id'=>$booking->category_id,
                                          'user_id'=>$booking->user_id,
                                          'title'=>$booking->title,
                                          'description'=>$booking->description,
                                          'location'=>$booking->location,
                                          'latitude'=>$booking->latitude,
                                          'longitude'=>$booking->longitude,
                                          'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                          'is_rfq'=>$booking->is_rfq,
                                          'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                          'is_hourly'=>$booking->is_hourly,
                                          'is_package'=>$booking->is_package,
                                          'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                          'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                          'datetime'=>$booking->datetime,
                                          'requested_id'=>$booking->requested_id,
                                          'categories'=>$categories,
                                          'subcategories'=>$subcategories,
                                          'status'=>$booking->status,
                                          'name'=>$provider_name,
                                          'email'=>$provider_email,
                                          'mobile_number'=>$provider_mobile_number,
                                          'profile_picture'=>$provider_profile_picture,
                                          'package_id'=>$package_id,
                                          'package_title'=>$package_title,
                                          'package_duration'=>$package_duration,
                                          'package_description'=>$package_description,
                                          'quantity'=>$quantity,
                                          'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:'',
                                          'job_status'=>$job_status,
                                          'booking_rfq'=>$bookingrfq_data
                                          ); 
                         $booking_array[$type][]=$bookingdata;
                      }
                }
              }              
             }
             if(count($booking_array[$type])>0)
             {
               $booking_data=$booking_array;
               $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'record found');
             }else
             {
               $booking_data=$booking_array;
               $response=array('status'=>false,'bookingdata'=>$booking_data,'message'=>'record not found');
             }             
           }else{            
            $response=array('status'=>false,'message'=>'record not found');
        } 
      }else{            
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
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
    /**
     * API to get user seeker details according to Id 
     *
     * @return [string] message
     */
    public function getSeekerProfile(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $seeker=array();
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles','media','profile','profile.city'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        if($seeker)
        { 
          $seeker['profile_picture']='';
          $age="";                
          if(isset($seeker) && $seeker->getMedia('profile_picture')->count() > 0 && file_exists($seeker->getFirstMedia('profile_picture')->getPath()))
          {
            $seeker['profile_picture']=$seeker->getFirstMedia('profile_picture')->getFullUrl();
          }  
          if(isset($seeker->profile->dob) && $seeker->profile->dob!='')
          {

            $age = (date('Y') - date('Y',strtotime($seeker->profile->dob)));          
          }      
          $country='';
          $city='';
          $country_id='';
          $city_id='';
          
          if(!empty($seeker->profile->city))
          {
             $city=isset($seeker->profile->city->title)?$seeker->profile->city->title:'';
             $country=isset($seeker->profile->city->country->title)?$seeker->profile->city->country->title:'';
             $country_id=isset($seeker->profile->city->country->id)?$seeker->profile->city->country->id:'';
             $city_id=isset($seeker->profile->city->id)?$seeker->profile->city->id:'';
          }
          $seeker=array('id'=>$seeker->id,
                        'name'=>$seeker->name,
                        'email'=>$seeker->email,
                        'mobile_number'=>$seeker->mobile_number,
                        'facebook_id'=>$seeker->facebook_id,
                        'facebook_data'=>$seeker->facebook_data,
                        'google_plus_id'=>$seeker->google_plus_id,
                        'google_plus_data'=>$seeker->google_plus_data,
                        'device_type'=>$seeker->device_type,
                        'device_token'=>$seeker->device_token,
                        'age'=>(string)$age,
                        'facebook_url'=>$seeker->profile->facebook_url,
                        'twitter_url'=>$seeker->profile->twitter_url,
                        'linkedin_url'=>$seeker->profile->linkedin_url,
                        'googleplus_url'=>$seeker->profile->googleplus_url,
                        'instagram_url'=>$seeker->profile->instagram_url,
                        'residential_address'=>$seeker->profile->residential_address,
                        'work_address'=>$seeker->profile->work_address,
                        'radius'=>$seeker->profile->radius,
                        'latitude'=>$seeker->profile->latitude,
                        'longitude'=>$seeker->profile->longitude,
                        'longitude'=>$seeker->profile->longitude,
                        'country'=>$country,
                        'city'=>$city,
                        'country_id'=>$country_id,
                        'city_id'=>$city_id);
          
          unset($seeker['media']);
          $response=array('status'=>true,'data'=>$seeker,'message'=>'Record found');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get provider bookings according to Id 
     *
     * @return [string] message
     */
    public function getJobDetail(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all(); 
        $userData=$rfq_bookinguserData=array();
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
             
        $userdata=User::with(['profile','media'])->where('id',$user->id)->first();
        if($userdata)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $booking= Booking::with(['hourly_charge'])->where('id',$request->booking_id)->first(); 

            $is_rfq=isset($booking->is_rfq)?$booking->is_rfq:0; 
            if(($user->roles->first()->id==config('constants.ROLE_TYPE_PROVIDER_ID')) && ($is_rfq==0))
              { 
                $booking=$booking->where('user_id',$userdata->id);
              }else if(($user->roles->first()->id==config('constants.ROLE_TYPE_SEEKER_ID')) && ($is_rfq==0))
              {
                $booking=$booking->where('requested_id',$userdata->id);
              }
            
            $boking_id=isset($request->booking_id)?$request->booking_id:'';
            $booking=$booking->where('id',$boking_id)->first();
            
           
           if($booking)
            {
              $package_id="";
              $package_title="";
              $package_duration="";
              $package_description="";
              $quantity="";
              $total_package_amount="";
              $hourly_charge=null;
              if(isset($booking->hourly_charge))
              {
                 $hourly_charge=$booking->hourly_charge;
                
              }
              if($is_rfq==0)
              {
                
                if($booking->is_package==1)
                {
                  $booking_package=Package::where('id',$booking->package_id)->first();
                  $package_id=isset($booking_package->id)?$booking_package->id:'';
                  $package_title=isset($booking_package->title)?$booking_package->title:'';
                  $package_duration=isset($booking_package->duration)?(string)$booking_package->duration:'';
                  $package_description=isset($booking_package->description)?$booking_package->description:'';
                  $quantity=isset($booking->quantity)?(string)$booking->quantity:'';
                  $total_package_amount=isset($booking->total_package_amount)?(string)$booking->total_package_amount:'';
                }

                $user_data=User::with('profile','media');
                if($user->roles->first()->id==config('constants.ROLE_TYPE_PROVIDER_ID'))
                {
                  $user_requested_id=$booking->requested_id;
                }else if($user->roles->first()->id==config('constants.ROLE_TYPE_SEEKER_ID'))
                {
                  $user_requested_id=$booking->user_id;
                }
                $user_data=$user_data->where('id',$user_requested_id);
                $user_data=$user_data->first();
                $user_data['profile_picture']='';
                $age="";                
                if(isset($user_data) && $user_data->getMedia('profile_picture')->count() > 0 && file_exists($user_data->getFirstMedia('profile_picture')->getPath()))
                {
                   $user_data['profile_picture']=$user_data->getFirstMedia('profile_picture')->getFullUrl();
                }else
                {

                   $user_data['profile_picture']= asset(config('constants.NO_IMAGE_URL'));
              
                }  
                if(isset($user_data->profile->dob) && $user_data->profile->dob!='')
                {

                  $age = (date('Y') - date('Y',strtotime($user_data->profile->dob)));          
                }

                $age=(string)$age;
                unset($user_data['media']);   
                $userData=array('user_id'=>$user_data->id,
                                     'name'=>$user_data->name,
                                     'email'=>$user_data->email,
                                     'age'=>$age,
                                     'profile_picture'=>$user_data['profile_picture'],
                                     'residential_address'=>$user_data->profile->residential_address,
                                     'work_address'=>$user_data->profile->work_address,
                                     'radius'=>$user_data->profile->radius,
                                     'latitude'=>$user_data->profile->latitude,
                                     'longitude'=>$user_data->profile->longitude,
                                     'longitude'=>$user_data->profile->longitude,
                                     'booking'=>array('id'=>$booking->id,
                                                      'title'=>$booking->title,
                                                      'description'=>$booking->description,
                                                      'location'=>$booking->location,
                                                      'latitude'=>$booking->latitude,
                                                      'longitude'=>$booking->longitude,
                                                      'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                                      'is_rfq'=>$booking->is_rfq,
                                                      'is_quoted'=>$booking->is_quoted,
                                                      'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                                      'is_hourly'=>$booking->is_hourly,
                                                      'is_package'=>$booking->is_package,
                                                      'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                                      'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                                      'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                                      'datetime'=>$booking->datetime,
                                                      'created_at'=>$booking->created_at,     
                                                      'package_id'=>$package_id,
                                                      'package_title'=>$package_title,
                                                      'package_duration'=>$package_duration,
                                                      'package_description'=>$package_description,
                                                      'quantity'=>$quantity,
                                                      'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:''),
                                      'rfq_data'=>$rfq_bookinguserData,
                                      'hourly_charge'=>$hourly_charge);
            }else
            {  
               $booking_id=$request->booking_id;
               $bookings=$booking->with('booking_user')->whereHas('booking_user', function($query) use ($booking_id){
                $query->where('booking_id',$booking_id)->groupBy('user_id');
               })->first(); 
               if($bookings)
               {

                $user_data=User::with('profile')->where('id',$bookings->requested_id)->first();
                
                if(isset($bookings->booking_user) && !empty($bookings->booking_user))
                 {

                  $profile_picture='';
                  $age=""; 
                  foreach ($bookings->booking_user as $key => $bookinguser) {
                    if(isset($bookinguser->user) && $bookinguser->user->getMedia('profile_picture')->count() > 0 && file_exists($bookinguser->user->getFirstMedia('profile_picture')->getPath()))
                    {
                       $profile_picture=$bookinguser->user->getFirstMedia('profile_picture')->getFullUrl();
                    }else
                    {

                       $profile_picture= asset(config('constants.NO_IMAGE_URL'));                  
                    }  
                    if(isset($bookinguser->user->profile->dob) && $bookinguser->user->profile->dob!='')
                    {

                      $age = (date('Y') - date('Y',strtotime($bookinguser->user->profile->dob)));          
                    }
                    $age=(string)$age;


                    $rfq_bookinguserData[]= array('user_id'=>$bookinguser->user->id,
                                                   'name'=>$bookinguser->user->name,
                                                   'email'=>$bookinguser->user->email,
                                                   'age'=>$age,
                                                   'profile_picture'=>$profile_picture);
                   }
                 }
                 $booking=$bookings;
               }else
               {                
                $booking=$booking->where('id',$booking_id)->first();
                $user_data=User::with('profile')->where('id',$booking->requested_id)->first();
               }               
               
                $userData=array('user_id'=>isset($user_data->id)?$user_data->id:'',
                                     'name'=>isset($user_data->name)?$user_data->name:'',
                                     'email'=>isset($user_data->email)?$user_data->email:'',
                                     'age'=>isset($age)?$age:'',
                                     'profile_picture'=>isset($user_data['profile_picture'])?$user_data['profile_picture']:'',
                                     'residential_address'=>isset($user_data->profile->residential_address)?$user_data->profile->residential_address:'',
                                     'work_address'=>isset($user_data->profile->work_address)?$user_data->profile->work_address:'',
                                     'radius'=>isset($user_data->profile->radius)?$user_data->profile->radius:'',
                                     'latitude'=>isset($user_data->profile->latitude)?$user_data->profile->latitude:'',
                                     'longitude'=>isset($user_data->profile->longitude)?$user_data->profile->longitude:'',
                                     'longitude'=>isset($user_data->profile->longitude)?$user_data->profile->longitude:'',
                                     'booking'=>array('id'=>$booking->id,
                                                      'title'=>$booking->title,
                                                      'description'=>$booking->description,
                                                      'location'=>$booking->location,
                                                      'latitude'=>$booking->latitude,
                                                      'longitude'=>$booking->longitude,
                                                      'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                                      'is_rfq'=>$booking->is_rfq,
                                                      'is_quoted'=>$booking->is_quoted,
                                                      'request_for_quote_budget'=>isset($booking->request_for_quote_budget)?(string)$booking->request_for_quote_budget:'',
                                                      'is_hourly'=>$booking->is_hourly,
                                                      'is_package'=>$booking->is_package,
                                                      'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                                      'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                                      'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                                      'datetime'=>$booking->datetime,
                                                      'created_at'=>$booking->created_at,
                                                      'package_id'=>$package_id,
                                                      'package_title'=>$package_title,
                                                      'package_duration'=>$package_duration,
                                                      'package_description'=>$package_description,
                                                      'quantity'=>$quantity,
                                                      'total_package_amount'=>isset($total_package_amount)?(string)$total_package_amount:''),
                                      'rfq_data'=>$rfq_bookinguserData,
                                      'hourly_charge'=>$hourly_charge);
              }
            
                 
                 $response=array('status'=>true,'data'=>$userData,'message'=>'Record found');
            }else
            {
                 $response=array('status'=>false,'message'=>'No record found');
            }
         
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to make declined jobs for provider Id 
     *
     * @return [string] message
     */
    public function jobDeclined(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
        $userdata=User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
            })->where('id',$user->id)->first();        
        if($userdata)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required', 
                'reason'=>'required', 
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $booking= Booking::where('id',$request->booking_id)->first();
            if($booking)
            {
              if($booking->is_hourly==1 || $booking->is_package==1)
              {
                    $booking_data=array('user_id'=>$userdata->id,
                                        'booking_id'=>$booking->id,
                                        'status'=>config('constants.PAYMENT_STATUS_DECLINED'),
                                        'reason'=>$request->reason);
                    $booking->update($booking_data);
                    $response=array('status'=>true,'data'=>$booking->id,'message'=>'Job declined done');
              }else if($booking->is_rfq==1)
              {
                    $booking_user= BookingUser::where(array('user_id'=>$userdata->id,'booking_id'=>$request->booking_id))->first();
                if($booking_user)
                {
                    $booking_data=array('user_id'=>$userdata->id,
                                     'booking_id'=>$booking->id,
                                     'is_rfq'=>1,
                                     'status'=>config('constants.PAYMENT_STATUS_DECLINED'),
                                     'reason'=>$request->reason);
                    $booking->booking_user()->update($booking_data);
                }else
                {
                    $booking_data=array('user_id'=>$userdata->id,
                                     'booking_id'=>$booking->id,
                                     'is_rfq'=>1,
                                     'status'=>config('constants.PAYMENT_STATUS_DECLINED'),
                                     'reason'=>$request->reason);
                    $booking->booking_user()->create($booking_data);
                }                
                $response=array('status'=>true,'data'=>$booking->id,'message'=>'Job declined done');
              }              
            }else
            {
              $response=array('status'=>false,'message'=>'no record found');
            }            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
      /**
     * API to make jobs Quote for provider Id 
     *
     * @return [string] message
     */
    public function jobQuote(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
        $userdata=User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
            })->where('id',$user->id)->first();   
        $device_token=array();     
        if($userdata)
        {          
            $rules = [   
                  'booking_id'=>'required', 
                  'type'=>'required', 
                  'requirement'=>'required', 
                  'price'=>'required',
                  'service_datetime'=>'required',  
                  'comment'=>'nullable'      
            ]; 
            
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $booking= Booking::where('id',$request->booking_id);
            if($request->type=='is_hourly')
            {
              $booking=$booking->where('is_hourly',1);
              $booking=$booking->first();
              if($booking)
              {                 
                 $booking_data=array('requirement'=>$request->requirement,
                                     'budget'=>$request->price,
                                     'service_datetime'=>$request->service_datetime,
                                     'status'=>config('constants.PAYMENT_STATUS_QUOTED'),
                                     'is_quoted'=>1,
                                     'user_id'=>$user->id,
                                     'comment'=>$request->comment);
                 $booking->update($booking_data);  
                               
                 if ($request->hasFile('works_photo'))
                 {
                   $files = $request->file('works_photo');
                   $i=0; 
                    foreach ($files as $file) 
                    {
                       $customname = time() .$i.'.' . $file->getClientOriginalExtension();
                       $userdata->addMedia($file)
                         ->usingFileName($customname)
                         ->toMediaCollection('works_photo');
                       $i++;
                    }
                 }                 
                      
                  //send notification to seeker job accepted by provider
                  // start notification code
                  $seekerdata=User::where('id',$booking->requested_id)->first();
                  if($seekerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_JOB_QUOTED_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_JOB_QUOTED_MESSAGE');
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                        $device_token[]=$seekerdata->device_token;
                    }else
                    {
                        $device_token[]=$seekerdata->device_token;
                    } 
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                      
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code
                 $response=array('status'=>true,'message'=>'Job Quoted done');
              }else
              {
                 $response=array('status'=>false,'message'=>'no record found');
              } 

            }else if($request->type=='is_package')
            {
              $booking=$booking->where('is_package',1);
              $booking=$booking->first();
              if($booking)
              {                 
                 $booking_data=array('requirement'=>$request->requirement,
                                     'budget'=>$request->price,
                                     'service_datetime'=>$request->service_datetime,
                                     'status'=>config('constants.PAYMENT_STATUS_QUOTED'),
                                     'is_quoted'=>1,
                                     'user_id'=>$user->id,
                                     'comment'=>$request->comment);
                 $booking->update($booking_data);                 
                 if ($request->hasFile('works_photo'))
                 {
                   $files = $request->file('works_photo');
                    $i=0;
                    foreach ($files as $file) 
                    {
                       $customname = time() .$i. '.' . $file->getClientOriginalExtension();
                       $userdata->addMedia($file)
                         ->usingFileName($customname)
                         ->toMediaCollection('works_photo');
                        $i++;
                    }
                 }
                 //send notification to seeker job accepted by provider
                 // start notification code
                  $seekerdata=User::where('id',$booking->requested_id)->first();
                  if($seekerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_JOB_QUOTED_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_JOB_QUOTED_MESSAGE');
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                        $device_token[]=$seekerdata->device_token;
                    }else
                    {
                        $device_token[]=$seekerdata->device_token;
                    } 
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                      
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code
                 $response=array('status'=>true,'message'=>'Job Quoted done');
              }else
              {
                 $response=array('status'=>false,'message'=>'no record found');
              } 

            }else if($request->type=='is_rfq') 
            {  
              $booking=$booking->where('is_rfq',1);
              $booking=$booking->first();           
              //$booking=$booking->where('is_rfq',1)->first();              
              /*if($booking)
              {*/
                 //$bookingUser= BookingUser::where(['booking_id'=>$request->booking_id,'user_id'=>$user->id])->first();
                 /*if($bookingUser)
                 {*/
                  $bookingUserData= BookingUser::where(['booking_id'=>$request->booking_id,'user_id'=>$user->id])->first();
                  $booking_user=array('booking_id'=>$request->booking_id,
                                       'user_id'=>$user->id,
                                       'is_rfq'=>1,
                                       'budget'=>$request->price,
                                       'service_datetime'=>$request->service_datetime,
                                       'requirement'=>$request->requirement,  
                                       'is_quoted'=>1,
                                       'status'=>config('constants.PAYMENT_STATUS_QUOTED'),
                                       'comment'=>$request->comment
                                       );
                  if($bookingUserData)
                  {   
                      $bookingUserData->update($booking_user);                 
                      
                  }else
                  {
                      $bookingUserData=BookingUser::create($booking_user);
                  }
                   
                  if ($request->hasFile('works_photo'))
                   {
                     $files = $request->file('works_photo');
                     $i=0;
                     foreach ($files as $file) 
                      {
                         $customname = time() .$i.'.' . $file->getClientOriginalExtension();
                         /*$bookingUserData->addMedia($file)
                           ->usingFileName($customname)
                           ->toMediaCollection('booking_works_photo');*/
                           $userdata->addMedia($file)
                           ->usingFileName($customname)
                           ->toMediaCollection('works_photo');
                         $i++;
                      }
                   } 
                  // start notification code
                  $seekerdata=User::where('id',$booking->requested_id)->first();
                  if($seekerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_JOB_QUOTED_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_JOB_QUOTED_MESSAGE');
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                        $device_token[]=$seekerdata->device_token;
                    }else
                    {
                        $device_token[]=$seekerdata->device_token;
                    } 
                    if($seekerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                      
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code
                   $response=array('status'=>true,'message'=>'Job Quoted done');
                // }      
                 
              /*}else
              {
                   $response=array('status'=>false,'message'=>'no record found');
              } */
            }
            //notification code

            //end notification code
            
                       
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to get all providers listing that have requested for booking quote 
     *
     * @return [string] message
    */
    public function getRFQProviders(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $booking_data=$booking_rfq_users=array();
        $type=isset($request->type)?$request->type:'';
        if($user)
        {          
            $validator = Validator::make($data, [
                'booking_id'=>'required',
                'type'=>'required', 
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $is_rfq=false;
            if($type=='is_rfq')
            {
              $is_rfq=true;
            }
            $bookings= Booking::with(['user','user.profile','booking_user'])->where(['id'=>$request->booking_id,'requested_id'=>$user->id,'is_rfq'=>$is_rfq])->first();
            if($bookings)
            {              
              $booking_users=BookingUser::where(array('booking_id'=>$bookings->id,'status'=>config('constants.PAYMENT_STATUS_QUOTED')))->get();
              if($booking_users)
              {
                //$rating='';
                foreach($booking_users as $booking_user)
                {
                    $rating=0.0;
                    $providerdata=User::with(['profile'])->where('id',$booking_user->user_id)->first();
                    $provider_name=isset($providerdata->name)?$providerdata->name:'';
                    $provider_email=isset($providerdata->email)?$providerdata->email:'';
                    $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                    if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                    {
                          $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                    }else
                    {
                          $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                    }
                    if($providerdata->profile->display_seeker_reviews==true)
                    {
                      $provider_review=Review::where(array('user_id'=>$providerdata->profile->user_id))->get();
                      if(count($provider_review)>0)
                      {
                        $no_of_count=count($provider_review); 
                        $provider_rating=$provider_review->sum('rating');
                        $rating = $provider_rating / $no_of_count;
                        $rating=(round($rating,2));
                      }
                    }
                    $booking_rfq_users[]=array('booking_id'=>$booking_user->booking_id,
                                           'user_id'=>$booking_user->user_id,
                                           'is_rfq'=>$booking_user->is_rfq,
                                           'budget'=>isset($booking_user->budget)?(string)$booking_user->budget:'',
                                           'service_datetime'=>$booking_user->service_datetime,
                                           'requirement'=>isset($booking_user->requirement)?$booking_user->requirement:'',
                                           'comment'=>isset($booking_user->comment)?$booking_user->comment:'',
                                           'is_quoted'=>$booking_user->is_quoted,
                                           'reason'=>isset($booking_user->reason)?$booking_user->reason:'',
                                           'status'=>$booking_user->status,
                                           'name'=>$provider_name,
                                           'email'=>$provider_email,
                                           'mobile_number'=>$provider_mobile_number,
                                           'profile_picture'=>$provider_profile_picture,
                                           'rating'=>$rating
                                           );
                }
              }
              $booking_data=array('id'=>$bookings->id,
                                  'title'=>$bookings->title,
                                  'description'=>$bookings->description,
                                  'location'=>$bookings->location,
                                  'latitude'=>$bookings->latitude,
                                  'longitude'=>$bookings->longitude,
                                  'is_rfq'=>$bookings->is_rfq,
                                  'booking_rfq'=>$booking_rfq_users);
              
              $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'Record found.');
            }else
            {
              $response=array('status'=>false,'message'=>'Record not found.');
            }
            
            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
     /**
     * API to get booking details
     *
     * @return [string] message
    */
    public function getBookingDetail(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $booking_data=$userdata=array();
        $type=isset($request->type)?$request->type:'';
        if($user)
        {          
            $validator = Validator::make($data, [
                'booking_id'=>'required',
                'type'=>'required', 
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
             
             if($type=='is_hourly')
             {
                $booking= Booking::where(['id'=>$request->booking_id,'requested_id'=>$user->id,'is_hourly'=>true])->first();
                if($booking)
                {
                    $rating=0.0;
                    $provider_name=isset($booking->user->name)?$booking->user->name:'';
                    $provider_email=isset($booking->user->email)?$booking->user->email:'';
                    $provider_mobile_number=isset($booking->user->mobile_number)?$booking->user->mobile_number:'';
                    if(isset($booking->user) && $booking->user->getMedia('profile_picture')->count() > 0 && file_exists($booking->user->getFirstMedia('profile_picture')->getPath()))
                    {
                          $provider_profile_picture=$booking->user->getFirstMedia('profile_picture')->getFullUrl();
                    }else
                    {
                          $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                    }

                    $provider_review=Review::where(array('user_id'=>$booking->user->id))->get();
                    if(count($provider_review)>0)
                    {
                      $no_of_count=count($provider_review); 
                      $provider_rating=$provider_review->sum('rating');
                      $rating = $provider_rating / $no_of_count;
                      $rating=(round($rating,2));
                    }
                        $userdata=array('name'=>$provider_name,
                                        'email'=>$provider_email,
                                        'mobile_number'=>$provider_mobile_number,
                                        'profile_picture'=>$provider_profile_picture,
                                        'rating'=>$rating);
                    $booking_data=array('id'=>$booking->id,
                                        'title'=>$booking->title,
                                        'description'=>$booking->description,
                                        'location'=>$booking->location,
                                        'latitude'=>$booking->latitude,
                                        'longitude'=>$booking->longitude,
                                        'is_hourly'=>isset($booking->is_hourly)?$booking->is_hourly:'',
                                        'is_rfq'=>isset($booking->is_rfq)?$booking->is_rfq:'',
                                        'budget'=>isset($booking->budget)?(string)$booking->budget:'',
                                        'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                        'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                        'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                        'datetime'=>isset($booking->datetime)?$booking->datetime:'',
                                        'service_datetime'=>isset($booking->service_datetime)?$booking->service_datetime:'',
                                        'requirement'=>isset($booking->requirement)?$booking->requirement:'',
                                        'status'=>$booking->status,
                                        'userdata'=>$userdata);
                    $response=array('status'=>true,'booking'=>$booking_data,'message'=>'Record found.');
                }else
                {
                    $response=array('status'=>false,'message'=>'Record not found.');
                }  
             }else if($type=='is_rfq')
             {
              $booking= Booking::with(['user','user.profile','booking_user'])->where(['id'=>$request->booking_id,'requested_id'=>$user->id,'is_rfq'=>true])->first();
              if($booking)
                {                   
                   $booking_user=BookingUser::where(array('booking_id'=>$booking->id,'status'=>config('constants.PAYMENT_STATUS_QUOTED')));
                   if($request->user_id)
                   {
                    $booking_user=$booking_user->where('user_id',$request->user_id);
                   }
                    $booking_user=$booking_user->first();

                    $rating=0.0;
                    $providerdata=User::with(['profile'])->where('id',$request->user_id)->first();
                    $provider_name=isset($providerdata->name)?$providerdata->name:'';
                    $provider_email=isset($providerdata->email)?$providerdata->email:'';
                    $provider_mobile_number=isset($providerdata->mobile_number)?$providerdata->mobile_number:'';
                    $provider_id=isset($providerdata->id)?$providerdata->id:'';
                    if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
                    {
                          $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
                    }else
                    {
                          $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                    }                    
                    $provider_review=Review::where(array('user_id'=>$provider_id))->get();
                    if(count($provider_review)>0)
                    {
                      $no_of_count=count($provider_review); 
                      $provider_rating=$provider_review->sum('rating');
                      $rating = $provider_rating / $no_of_count;
                      $rating=(round($rating,2));
                    }
                       $userdata=array( 'name'=>$provider_name,
                                        'email'=>$provider_email,
                                        'mobile_number'=>$provider_mobile_number,
                                        'profile_picture'=>$provider_profile_picture,
                                        'rating'=>$rating);
                   $booking_data=array('id'=>$booking->id,
                                        'title'=>$booking->title,
                                        'description'=>$booking->description,
                                        'location'=>$booking->location,
                                        'latitude'=>$booking->latitude,
                                        'longitude'=>$booking->longitude,
                                        'is_hourly'=>isset($booking->is_hourly)?$booking->is_hourly:'',
                                        'is_rfq'=>isset($booking->is_rfq)?$booking->is_rfq:'',
                                        'budget'=>isset($booking_user->budget)?(string)$booking_user->budget:'',
                                        'estimated_hours'=>isset($booking->estimated_hours)?(string)$booking->estimated_hours:'',
                                        'min_budget'=>isset($booking->min_budget)?(string)$booking->min_budget:'',
                                        'max_budget'=>isset($booking->max_budget)?(string)$booking->max_budget:'',
                                        'datetime'=>isset($booking->datetime)?$booking->datetime:'',
                                        'service_datetime'=>isset($booking_user->service_datetime)?$booking_user->service_datetime:'',
                                        'requirement'=>isset($booking_user->requirement)?$booking_user->requirement:'',
                                        'status'=>isset($booking->status)?$booking->status:'',
                                        'userdata'=>$userdata
                                        );
                   $response=array('status'=>true,'booking'=>$booking_data,'message'=>'Record found.');
                }else
                {
                   $response=array('status'=>false,'message'=>'Record not found.');
                }
             }
            
                      
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
      /**
     * API to make payment
     *
     * @return [string] message
    */
    public function makePayment(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $payment_data=$device_token=array();

        if($user)
        {          
            $validator = Validator::make($data, [
                'booking_id'=>'required',
                'user_id'=>'required', 
                'transaction_type'=>'required',
                'trans_id'=>'required',
                'trans_time'=>'nullable',
                'trans_amount'=>'required',
                'business_shortcode'=>'nullable',
                'bill_ref_number'=>'required',
                'invoice_number'=>'nullable',
                'third_party_trans_id'=>'nullable',
                'msisdn'=>'required',
                'first_name'=>'required',
                'middle_name'=>'nullable',
                'last_name'=>'nullable',
                'org_account_balance'=>'nullable',
                'status'=>'required',
                'payment_mode'=>'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $booking=Booking::where(array('id'=>$request->booking_id,'requested_id'=>$user->id))->first(); 
            if($booking)
            {
              $data['payment_by']=$user->id;
              if($booking->is_hourly==1)
              {
                $transaction = Transaction::create($data);              
                $payment_data=array('user_id'=>$request->user_id,
                                  'booking_id'=>$request->booking_id,
                                  'booking_name'=>$booking->title,
                                  'transaction_type'=>isset($request->transaction_type)?$request->transaction_type:'',
                                  'trans_id'=>isset($request->trans_id)?$request->trans_id:'',
                                  'trans_time'=>isset($request->trans_time)?$request->trans_time:'',
                                  'trans_amount'=>isset($request->trans_amount)?$request->trans_amount:'',
                                  'invoice_number'=>isset($request->invoice_number)?$request->invoice_number:'',
                                  'first_name'=>isset($request->first_name)?$request->first_name:'',
                                  'status'=>isset($request->status)?$request->status:'',
                                  'payment_mode'=>isset($request->payment_mode)?$request->payment_mode:'');
                 $booking->update(array('status'=>config('constants.PAYMENT_STATUS_ACCEPTED')));
                 $response=array('status'=>true,'payment'=>$payment_data,'message'=>'Payment successfully done.');
                 // start notification code                
                  $notification_providerdata=User::where('id',$request->user_id)->first();
                  if($notification_providerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_AFTER_PAYMENT_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_AFTER_PAYMENT_MESSAGE');
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }                        
                    }else
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }
                    } 
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                     
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code 

              }else if($booking->is_rfq==1)
              {
                $booking_user=BookingUser::where(array('booking_id'=>$booking->id,'user_id'=>$request->user_id))->first();
                if($booking_user)
                {
                   $booking->update(array('status'=>config('constants.PAYMENT_STATUS_ACCEPTED')));
                   //$booking->update(array('status'=>config('constants.PAYMENT_STATUS_ACCEPTED')));
                   $transaction = Transaction::create($data);
                   $booking_user->update(array('status'=>config('constants.PAYMENT_STATUS_ACCEPTED')));
                   $payment_data=array('user_id'=>$request->user_id,
                                    'booking_id'=>$request->booking_id,
                                    'booking_name'=>$booking->title,
                                    'transaction_type'=>isset($request->transaction_type)?$request->transaction_type:'',
                                    'trans_id'=>isset($request->trans_id)?$request->trans_id:'',
                                    'trans_time'=>isset($request->trans_time)?$request->trans_time:'',
                                    'trans_amount'=>isset($request->trans_amount)?$request->trans_amount:'',
                                    'invoice_number'=>isset($request->invoice_number)?$request->invoice_number:'',
                                    'first_name'=>isset($request->first_name)?$request->first_name:'',
                                    'status'=>isset($request->status)?$request->status:'',
                                    'payment_mode'=>isset($request->payment_mode)?$request->payment_mode:'');
                   $response=array('status'=>true,'payment'=>$payment_data,'message'=>'Payment successfully done.');
                   // start notification code                
                  $notification_providerdata=User::where('id',$booking_user->user_id)->first();
                  if($notification_providerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_AFTER_PAYMENT_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_AFTER_PAYMENT_MESSAGE');
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }                        
                    }else
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }
                    } 
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                     
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code 
                }else
                { 
                  $response=array('status'=>false,'message'=>'Record not found.');
                }  
              }else if($booking->is_package==1)
              {                
                $transaction = Transaction::create($data);       
                $payment_data=array('user_id'=>$request->user_id,
                                  'booking_id'=>$request->booking_id,
                                  'booking_name'=>$booking->title,
                                  'transaction_type'=>isset($request->transaction_type)?$request->transaction_type:'',
                                  'trans_id'=>isset($request->trans_id)?$request->trans_id:'',
                                  'trans_time'=>isset($request->trans_time)?$request->trans_time:'',
                                  'trans_amount'=>isset($request->trans_amount)?$request->trans_amount:'',
                                  'invoice_number'=>isset($request->invoice_number)?$request->invoice_number:'',
                                  'first_name'=>isset($request->first_name)?$request->first_name:'',
                                  'status'=>isset($request->status)?$request->status:'',
                                  'payment_mode'=>isset($request->payment_mode)?$request->payment_mode:'');
                 $booking->update(array('status'=>config('constants.PAYMENT_STATUS_ACCEPTED')));
                 $response=array('status'=>true,'payment'=>$payment_data,'message'=>'Payment successfully done.');
                 // start notification code                
                  $notification_providerdata=User::where('id',$request->user_id)->first();
                  if($notification_providerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_AFTER_PAYMENT_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_AFTER_PAYMENT_MESSAGE');
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }                        
                    }else
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }
                    } 
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                     
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code 
              } 
            }else
            {
              $response=array('status'=>false,'message'=>'Record not found.');
            }
        }else
        { 
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to get user seeker FAQ
     *
     * @return [string] message
     */
    public function getFAQ(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $faqlist=array();
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        if($seeker)
        { 
          $lists=Faq::where('is_active',true)->get();
          if(count($lists)>0)
          {
            foreach ($lists as $key => $list) {
              $faqlist[]=array('title'=>$list->title,
                               'description'=>$list->description,
                               'is_active'=>$list->is_active);
            }
            $response=array('status'=>true,'faqlist'=>$faqlist,'message'=>'Record found.');
          }else
          {
            $response=array('status'=>false,'message'=>'Record not found.');
          }          
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
   }
   /**
     * API to save seeker rating for provider
     *
     * @return [string] message
     */
    public function addRating(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $review_data=array();
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        if($seeker)
        { 
             $validator = Validator::make($data, [
                'user_id'=>'required', 
                'rating'=>'required',
                'text'=>'nullable'
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $review=Review::where(array('user_id'=>$request->user_id,'added_by'=>$user->id))->first();
            $data['added_by']=$user->id;
            $review_show_on_profile=0;
            if($request->review_show_on_profile)
            {
              $review_show_on_profile=true;
            }
            if($review)
            {
              Review::where(['user_id'=>$request->user_id,'added_by'=>$user->id])->delete();
              $review = Review::create($data);
              $review_data=array('user_id'=>$request->user_id,
                                  'rating'=>$request->rating,
                                  'text'=>isset($request->text)?$request->text:'');
              $response=array('status'=>true,'review'=>$review_data,'message'=>'you have successfully given review, Thank you.');
            }else
            {
               $review = Review::create($data);
               
               if($review_show_on_profile==true)
               {
                  $profile = Profile::where(array('user_id'=>$request->user_id))->first();
                  $profile_data=array('display_seeker_reviews'=>$review_show_on_profile);
                  $profile->update($profile_data);                
               }
               $providerdata=User::with(['profile','media'])->where('id',$request->user_id)->first();
               if ($request->hasFile('works_photo')){
                 $files = $request->file('works_photo');
                  $i=0;
                  foreach ($files as $file) 
                  {
                     $customname = time().$i. '.' . $file->getClientOriginalExtension();
                     $providerdata->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('works_photo');
                       $i++;
                   }
               }
               $response=array('status'=>true,'review'=>$review_data,'message'=>'you have successfully given review, Thank you.');
            }           
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
      /**
     * API to add Jobs Schedule with provider login
     *
     * @return [string] message
     */
    public function addJobsSchedule(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
        $schedules=array();
        $provider = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $provider=$provider->where(['id'=>$user->id])->first();
        if($provider)
        { 
             $validator = Validator::make($data, [
                'booking_id'=>'required', 
                'schedules'=>'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            //$schedules_data=Schedule::where(['booking_id'=>$request->booking_id,'user_id'=>$provider->id])->get();
            /*if(count($schedules_data)>0)
            {
               Schedule::where(['booking_id'=>$request->booking_id,'user_id'=>$provider->id])->delete();
               $schedules_data=json_decode(stripslashes($request->schedules));
              if(!empty($schedules_data))
              {               
                foreach ($schedules_data as $key => $schedule) 
                { 
                   $fill_data=array('booking_id'=>$request->booking_id,'user_id'=>$provider->id,'date'=>$schedule->date,'start_time'=>$schedule->start_time,'end_time'=>$schedule->end_time,'service_title'=>$schedule->service_title,'requirements'=>$schedule->requirements,'price'=>$schedule->price,'is_complete'=>0);
                   Schedule::create($fill_data);   
                }
              }
            }else
            {*/
              $schedules_data=json_decode(stripslashes($request->schedules));
              if(!empty($schedules_data))
              {               
                foreach ($schedules_data as $key => $schedule) 
                { 
                   $fill_data=array('booking_id'=>$request->booking_id,'user_id'=>$provider->id,'date'=>$schedule->date,'start_time'=>$schedule->start_time,'end_time'=>$schedule->end_time,'service_title'=>$schedule->service_title,'requirements'=>$schedule->requirements,'price'=>$schedule->price,'is_complete'=>0);              
                   Schedule::create($fill_data);   
                }
              }
           // }            
            $response=array('status'=>true,'message'=>'Schedules added.');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to get PackagesUser of provider
     *
     * @return [string] message
     */
    public function getProviderByPackage(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $review_data=array();
        
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        $package_data=array();
        
       
        if($seeker)
        {
            $validator = Validator::make($data, [
                'subcategory_id'=>'required', 
                'package_id'=>'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $subcategory_id=isset($request->subcategory_id)?$request->subcategory_id:'';
            $package_id=isset($request->package_id)?$request->package_id:'';
            $experience=isset($request->experience)?$request->experience:'';
            $min_price=isset($request->min_price)?$request->min_price:'';
            $max_price=isset($request->max_price)?$request->max_price:'';
            $security_check=isset($request->security_check)?$request->security_check:'';
            $packages= PackageUser::with(['user','user.review','user.profile','user.profile.experience_level','user.media','package'=>function($query) use ($subcategory_id) {              
              $query->where('category_id',$subcategory_id);  
              $query->where('is_active',true);             
            }])->where('package_id',$package_id);
            if($experience)
            {
              $packages->whereHas('user.profile', function($query) use ($experience) {    
              //$query->where('year_experience',$experience);   
              $query->where('experience_level_id',$experience);          
              });
            }
            $rating_asc='asc';
            $rating='desc';
            if($min_price)
            {
              $packages->where('price', '>=', $min_price );
            }
            if($max_price)
            {
              $packages->where('price', '<=', $max_price ); 
            }
            if($security_check)
            {

              $packages->whereHas('user', function($query) use ($security_check) {    
              $query->where('is_verify',$security_check);            
              });
            }
           /*if($rating_asc)
            {
              $packages->whereHas('user.review', function($query) use ($rating_asc) {   
                // $query->select('rating as sumrating');   
                $query->orderBy('rating', 'ASC');
              });
            }*/
            // $packages=$packages->user()->review()->avg('rating_for_user');
            $packages=$packages->get();
            if(count($packages)>0)
            {
              foreach ($packages as $key => $package) 
              {
                $provider_id=isset($package->user->id)?$package->user->id:0;    
                if($provider_id>0)
                {
                  $profile_picture='';    
                  $rating=0.0;   
                  $age='';                             
                  if(isset($package->user) && $package->user->getMedia('profile_picture')->count() > 0 && file_exists($package->user->getFirstMedia('profile_picture')->getPath()))
                  {
                    $profile_picture=$package->user->getFirstMedia('profile_picture')->getFullUrl();
                  }                    
                    $provider_review=Review::where(array('user_id'=>$provider_id))->get();
                    if(count($provider_review)>0)
                    {
                      $no_of_count=count($provider_review); 
                      $provider_rating=$provider_review->sum('rating');
                      $rating = $provider_rating / $no_of_count;
                      $rating=(round($rating,2));
                    }
                    if(isset($package->user->profile->dob) && $package->user->profile->dob!='')
                    {
                      $age = (date('Y') - date('Y',strtotime($package->user->profile->dob)));
                    }
                    $age=(string)$age; 
                  
                  
                   $package_data[]=array('id'=>isset($package->user->id)?$package->user->id:'',
                                         'name'=>isset($package->user->name)?$package->user->name:'',
                                         'email'=>isset($package->user->email)?$package->user->email:'',
                                         'package_price'=>isset($package->price)?(string)$package->price:'',
                                         'mobile_number'=>isset($package->user->mobile_number)?$package->user->mobile_number:'',
                                         'is_verify'=>isset($package->user->is_verify)?$package->user->is_verify:'',
                                         'profile_picture'=>$profile_picture,
                                         'dob'=>isset($package->user->profile->dob)?$package->user->profile->dob:'',
                                         'age'=>$age,
                                         'radius'=>isset($package->user->profile->radius)?$package->user->profile->radius:'',
                                         'year_experience_id'=>isset($package->user->profile->experience_level->id)?$package->user->profile->experience_level->id:'',
                                         'year_experience'=>isset($package->user->profile->experience_level->title)?$package->user->profile->experience_level->title:'',
                                         'rating'=>$rating);
                }
               }
                $response=array('status'=>true,'data'=>$package_data,'message'=>'Record found.');
            }else{
                $response=array('status'=>false,'message'=>'no record found.');
            }             
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);

      }    
    /**
     * API to add package booking
     *
     * @return [string] message
    */
    public function bookingPackage(Request $request){

        $user = Auth::user(); 
        $data = $request->all(); 
        $device_token=array();
        if($user)
        {
            $rules = [  
                'user_id'=>'required', 
                'package_id'=>'required',  
                'date'=>'required',
                'time'=>'required',
                'location'=>'required',
                'latitude'=>'required',
                'longitude'=>'required',
                'category_id'=>'required',                 
                'subcategory_id'=>'required',
                'budget'=>'required',
                'quantity'=>'required',                 
                'total_package_amount'=>'required',       
            ]; 
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $bookingdatetime=$data['date'].' '.$data['time'];
            $data['datetime']=$bookingdatetime;
            $data['requested_id']=isset($user->id)?$user->id:0;
            $data['status']='requested';
            $data['is_package']=true;
            $provider_id=isset($data['user_id'])?$data['user_id']:0;

            $userprofile=User::with('profile')->where('id',$provider_id)->first();
            $todays_datetime=date('Y-m-d H:i:s');
            $update_tentative_starttime=''; 
            $update_tentative_endtime='';
          
            $tentative_hour=isset($userprofile->profile->tentative_hour)?$userprofile->profile->tentative_hour:'';
            if($tentative_hour=='')
            {
              $tentative_hour=getSetting('tentative_hour');
            }
            if($tentative_hour!='')
            {
               $tentative_addhour='+'.$tentative_hour." hour";
               $tentative_minushour='-'.$tentative_hour." hour";
               $update_tentative_endtime= date('Y-m-d H:i:s',strtotime($tentative_addhour,strtotime($bookingdatetime)));
               $update_tentative_starttime= date('Y-m-d H:i:s',strtotime($tentative_minushour,strtotime($bookingdatetime)));
            }else
            {
              $update_tentative_starttime=$bookingdatetime;
              $update_tentative_endtime=$bookingdatetime;
            }     
           
          $checkbooking= Booking::where(array('is_package'=>true,'user_id'=>$provider_id))->whereBetween('datetime', [$update_tentative_starttime, $update_tentative_endtime])->get();
            
          if(($todays_datetime<$bookingdatetime) && ($checkbooking->count()==0))
           {  

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

            $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Package request sent successfully');
            if($data['user_id'])
            {              
                  // start notification code                
                  $notification_providerdata=User::where('id',$data['user_id'])->first();
                  if($notification_providerdata)
                  {
                    $notification_title=config('constants.NOTIFICATION_NEW_BOOKING_PACKAGE_SUBJECT');
                    $notification_message=config('constants.NOTIFICATION_NEW_BOOKING_PACKAGE_MESSAGE');
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }                        
                    }else
                    {
                      if(!empty($notification_providerdata->device_token))
                      {
                        $device_token[]=$notification_providerdata->device_token;
                      }
                    } 
                    if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                    {                     
                       sendIphoneNotifications($notification_title,$notification_message,$device_token);
                    }/*else
                    {
                       //sendIphoneNotification($title,$message,$token);
                    } */
                  }
                  //end notification code 
              }
            }else
            {
              $response=array('status'=>false,'message'=>'Job date not available!');
            } 

        }else
        {
                $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }  
    function getProviderScheduleList(Request $request)
    {
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();             
        if($seeker)
        {
            $rules = [  
                'booking_id'=>'required', 
                'user_id'=>'required'
            ]; 
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $type=isset($request->type)?$request->type:'';
            $user_id=isset($request->user_id)?$request->user_id:'';
            
            $bookings= Booking::with(['schedule'])->whereHas('schedule', function($query) use ($user_id) {    
                $query->where('user_id',$user_id);            
               })->where(['requested_id'=>$seeker->id,'id'=>$request->booking_id]);  
            
            $bookings=$bookings->first();
            
            $booking_data=$booking_schedule=array();
            $age=$provider_profile_picture='';
            $rating=0.0;  
            if($bookings)
            {
              if(count($bookings->schedule)>0)
              {
                foreach ($bookings->schedule as $key => $schedule) 
                { 
                  $booking_schedule[]=array('id'=>$schedule->id,
                                          'booking_id'=>$schedule->booking_id,
                                          'user_id'=>$schedule->user_id,
                                          'date'=>$schedule->date,
                                          'start_time'=>$schedule->start_time,
                                          'end_time'=>$schedule->end_time,
                                          'service_title'=>$schedule->service_title,
                                          'requirements'=>$schedule->requirements,
                                          'price'=>$schedule->price,
                                          'is_complete'=>$schedule->is_complete);
                }
              }  
              $providerdata=User::with(['profile','media'])->where('id',$user_id)->first();
               if(isset($providerdata) && $providerdata->getMedia('profile_picture')->count() > 0 && file_exists($providerdata->getFirstMedia('profile_picture')->getPath()))
              {
                $provider_profile_picture=$providerdata->getFirstMedia('profile_picture')->getFullUrl();
              }  
              if(isset($providerdata->profile->dob) && $providerdata->profile->dob!='')
              {

                $age = (date('Y') - date('Y',strtotime($providerdata->profile->dob)));          
              }
              /*rating*/
              if($providerdata->profile->display_seeker_reviews==true)
              {
                $provider_review=Review::where(array('user_id'=>$user_id))->get();
                if(count($provider_review)>0)
                {
                  $no_of_count=count($provider_review); 
                  $provider_rating=$provider_review->sum('rating');
                  $rating = $provider_rating / $no_of_count;
                  $rating=(round($rating,2));
                }
              }          
              /*rating*/
              $booking_data=array('id'=>$bookings->id,
                                  'title'=>$bookings->title,
                                  'description'=>$bookings->description,
                                  'schedules'=>$booking_schedule,
                                  'budget'=>isset($bookings->budget)?(string)$bookings->budget:'',
                                  'request_for_quote_budget'=>isset($bookings->request_for_quote_budget)?(string)$bookings->request_for_quote_budget:'',
                                  'is_rfq'=>$bookings->is_rfq,
                                  'is_hourly'=>$bookings->is_hourly,
                                  'min_budget'=>isset($bookings->min_budget)?(string)$bookings->min_budget:'',
                                  'max_budget'=>isset($bookings->max_budget)?(string)$bookings->max_budget:'',
                                  'is_package'=>$bookings->is_package,
                                  'quantity'=>$bookings->quantity,
                                  'datetime'=>$bookings->datetime,
                                  'requirement'=>$bookings->requirement,
                                  'total_package_amount'=>isset($bookings->total_package_amount)?(string)$bookings->total_package_amount:'',
                                  'name'=>isset($providerdata->name)?$providerdata->name:'',
                                  'email'=>isset($providerdata->email)?$providerdata->email:'',
                                  'age'=>(string)$age,
                                  'rating'=>$rating,
                                  'profile_picture'=>$provider_profile_picture
                                  );
              $response=array('status'=>true,'booking'=>$booking_data,'message'=>'Schedules available.');

            }else
            {
              $response=array('status'=>false,'message'=>'schedule not available.');
            }

            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
   }
   function updateProvidersSchedule(Request $request)
    {
        $user = Auth::user(); 
        $data = $request->all(); 
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();  
        if($seeker)
        {
            $rules = [  
                'schedule_id'=>'required', 
                'booking_id'=>'required',
                'user_id'=>'required',
                'is_complete'=>'required'
            ]; 
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $schedule = Schedule::where(array('id'=>$request->schedule_id,'booking_id'=>$request->booking_id,'user_id'=>$request->user_id))->first();    
            if($schedule)
            {
                $schedule_data=array('is_complete'=>$request->is_complete,'verified_by'=>$seeker->id);
                $schedule->update($schedule_data);
                $response=array('status'=>true,'message'=>'schedule updated');
            }else
            {
                $response=array('status'=>false,'message'=>'schedule not found.');
            }            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }

    function updateJob(Request $request)
    {
        $user = Auth::user(); 
        $data = $request->all();
        //$role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $role_id=config('constants.ROLE_TYPE_PROVIDER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();  
        if($seeker)
        {
            $rules = [  
                'booking_id'=>'required',
                'user_id'=>'required',
                'type'=>'required',
                'otp'=>'required',
            ]; 
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $type=isset($request->type)?$request->type:'';
            $otp=$request->otp;
            $check_transaction = Transaction::where(array('booking_id'=>$request->booking_id,'user_id'=>$request->user_id,'status'=>config('constants.PAYMENT_STATUS_SUCCESS')))->first();   
            if($check_transaction)
            {
                if($type=='is_package')
                {
                   $booking = Booking::where(array('id'=>$request->booking_id,'user_id'=>$request->user_id,'otp'=>$otp))->first(); 
                   if($booking)
                   {
                      $booking_data=array('status'=>config('constants.PAYMENT_STATUS_COMPLETED'),'job_status'=>config('constants.PAYMENT_STATUS_COMPLETED'));
                      $booking->update($booking_data);
                      $response=array('status'=>true,'message'=>'Job Updated.');
                   }else
                   {
                      $response=array('status'=>false,'message'=>'something went wrong!');
                   } 
                }else if($type=='is_hourly')
                {
                   $schedules = Schedule::where(array('booking_id'=>$request->booking_id,'user_id'=>$request->user_id))->get();                 
                   if(count($schedules)>0)
                   {
                     foreach ($schedules as $key => $schedule) 
                     {
                        $schedule_data=array('is_complete'=>1,'verified_by'=>$seeker->id);
                        $schedule->update($schedule_data);
                     }
                   } 
                   $booking = Booking::where(array('id'=>$request->booking_id,'user_id'=>$request->user_id,'otp'=>$otp))->first(); 
                   if($booking)
                   {
                      $booking_data=array('status'=>config('constants.PAYMENT_STATUS_COMPLETED'),'job_status'=>config('constants.PAYMENT_STATUS_COMPLETED'));
                      $booking->update($booking_data);
                      $response=array('status'=>true,'message'=>'Job Updated.');
                   }else
                   {
                      $response=array('status'=>false,'message'=>'something went wrong!');
                   } 
                }else if($type=='is_rfq')
                {
                   $schedules = Schedule::where(array('booking_id'=>$request->booking_id,'user_id'=>$request->user_id))->get();                 
                   if(count($schedules)>0)
                   {
                     foreach ($schedules as $key => $schedule) 
                     {
                        $schedule_data=array('is_complete'=>1,'verified_by'=>$seeker->id);
                        $schedule->update($schedule_data);
                     }
                   } 

                   $booking_user=BookingUser::where(array('booking_id'=>$request->booking_id,'user_id'=>$request->user_id,'status'=>config('constants.PAYMENT_STATUS_ACCEPTED')))->first();
                   $booking= Booking::where(array('id'=>$request->booking_id,'otp'=>$otp))->first();
                   if($booking)
                   {
                      $booking_data=array('status'=>config('constants.PAYMENT_STATUS_COMPLETED'));
                      $booking_user->update($booking_data);
                      $booking= Booking::where('id',$request->booking_id)->first();
                      $booking_data=array('status'=>config('constants.PAYMENT_STATUS_COMPLETED'),'job_status'=>config('constants.PAYMENT_STATUS_COMPLETED'));
                      $booking->update($booking_data);
                      $response=array('status'=>true,'message'=>'Job Updated.');
                   }else
                   {
                      $response=array('status'=>false,'message'=>'something went wrong!');
                   }   
                }
            }else
            {
               $response=array('status'=>false,'message'=>'something went wrong!');
            }

        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get provider details according to Id 
     *
     * @return [string] message
     */
    public function getProviderProfile(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $provider=$certification_data=$provider_data=array();
        $user_id=$user->id;
        if($user)
        {         

           $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city','category_user.category','certification','package_user','hourly_charge'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first();            
          $subcategories=[];
          $categories=[];
          if(count($provider->category_user)>0)
          {
            foreach ($provider->category_user as $key => $providerdata) 
            {
              if($providerdata->category->parent_id!=0){
                $subcategories[]=array('id'=>$providerdata->category->id,
                                     'title'=>$providerdata->category->title,
                                     'parent_id'=>$providerdata->category->parent_id,
                                     'is_active'=>$providerdata->category->is_active);
            }
             if($providerdata->category->parent_id==0){
                $categories[]=array('id'=>$providerdata->category->id,
                                     'title'=>$providerdata->category->title,
                                     'parent_id'=>$providerdata->category->parent_id,
                                     'is_active'=>$providerdata->category->is_active);
            }
           }  
          }
        
          $certification_img='';
          if(count($provider->certification))
          {
            foreach ($provider->certification as $key => $certification) {
               if(isset($certification) && $certification->getMedia('certification')->count() > 0 && file_exists($certification->getFirstMedia('certification')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('certification')->getFullUrl();
              }
               if(isset($certification) && $certification->getMedia('diploma')->count() > 0 && file_exists($certification->getFirstMedia('diploma')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('diploma')->getFullUrl();
              }
               if(isset($certification) && $certification->getMedia('degree')->count() > 0 && file_exists($certification->getFirstMedia('degree')->getPath()))
              {           
                 $certification_img=$certification->getFirstMedia('degree')->getFullUrl();
              }
              $certification_data[]=array('id'=>$certification->id,
                                        'title'=>$certification->title,
                                        'type'=>$certification->type,
                                        'img'=>$certification_img);
            }
          }
          $certificate_conduct='';
          $nca='';
          if(isset($provider) && $provider->getMedia('certificate_conduct')->count() > 0 && file_exists($provider->getFirstMedia('certificate_conduct')->getPath()))
          {           
            $certificate_conduct=$provider->getFirstMedia('certificate_conduct')->getFullUrl();
          } 
          if(isset($provider) && $provider->getMedia('nca')->count() > 0 && file_exists($provider->getFirstMedia('nca')->getPath()))
          {           
            $nca=$provider->getFirstMedia('nca')->getFullUrl();
          } 
          $rating=0.0;
          $provider['certification_data']=$certification_data;
          $provider['nca']=$nca;
          $provider['certificate_conduct']=$certificate_conduct;
                  
             
          $profile_picture='';
          $age="";                
          if(isset($provider) && $provider->getMedia('profile_picture')->count() > 0 && file_exists($provider->getFirstMedia('profile_picture')->getPath()))
          {
            $profile_picture=$provider->getFirstMedia('profile_picture')->getFullUrl();
          }  
          if(isset($provider->profile->dob) && $provider->profile->dob!='')
          {

            $age = (date('Y') - date('Y',strtotime($provider->profile->dob)));          
          }
          /*rating*/
          if($provider->profile->display_seeker_reviews==true)
          {
            $provider_review=Review::where(array('user_id'=>$user_id))->get();
            if(count($provider_review)>0)
            {
              $no_of_count=count($provider_review); 
              $provider_rating=$provider_review->sum('rating');
              $rating = $provider_rating / $no_of_count;
              $rating=(round($rating,2));
            }
          }          
          /* rating */

          $age=(string)$age;
          $rating=$rating;
          unset($provider['media']);
          unset($provider['certification']);
          $packages=[];
          $hourly=[];
          if(count($provider->package_user)>0)
          {
            foreach ($provider->package_user as $key => $package) 
            {
              
                   $packages[]=array('id'=>$package->package->id,
                                     'title'=>$package->package->title,
                                     'price'=>(string)$package->price,
                                     'is_active'=>$package->package->is_active);
                      
           }  
          }
          if(count($provider->hourly_charge)>0)
          {
            foreach ($provider->hourly_charge as $key => $charge) 
            {
              
              $hourly[]=array('id'=>isset($charge->id)?$charge->id:'',
                              'hours'=>isset($charge->hours)?$charge->hours:'',
                              'price'=>isset($charge->price)?(string)$charge->price:'',
                              'type'=>isset($charge->type)?$charge->type:'');
                      
           }  
          }
          $provider_works_photo = $provider->getMedia('works_photo');  
          $works_photo_Images=array(); 
          if (count($provider_works_photo) > 0) 
          {
            foreach ($provider_works_photo as $key => $works_photo) {
               $works_photo_Images[]=array('id'=>$works_photo->id,
                                      'name'=>$works_photo->name,
                                      'file_name'=>$works_photo->file_name,
                                      'image_path'=>$works_photo->getFullUrl());
            }
          }
          $provider_data=array('id'=>isset($provider->id)?$provider->id:'',
                               'name'=>isset($provider->name)?$provider->name:'',
                               'mobile_number'=>isset($provider->mobile_number)?$provider->mobile_number:'',
                               'email'=>isset($provider->email)?$provider->email:'',
                               'location'=>isset($provider->profile->work_address)?$provider->profile->work_address:'',
                               'latitude'=>isset($provider->profile->latitude)?$provider->profile->latitude:'',
                               'longitude'=>isset($provider->profile->longitude)?$provider->profile->longitude:'',
                               'radius'=>isset($provider->profile->radius)?$provider->profile->radius:'',
                               'passport_number'=>isset($provider->profile->passport_number)?$provider->profile->passport_number:'',
                               'residential_address'=>isset($provider->profile->residential_address)?$provider->profile->residential_address:'',
                               'experience_level_id'=>isset($provider->profile->experience_level->id)?$provider->profile->experience_level->id:'',
                               'experience_level_title'=>isset($provider->profile->experience_level->title)?$provider->profile->experience_level->title:'',
                               'reference'=>isset($provider->profile->reference)?$provider->profile->reference:'',
                               'facebook_url'=>isset($provider->profile->facebook_url)?$provider->profile->facebook_url:'',
                               'twitter_url'=>isset($provider->profile->twitter_url)?$provider->profile->twitter_url:'',
                               'instagram_url'=>isset($provider->profile->instagram_url)?$provider->profile->instagram_url:'',
                               'fundi_is_middlemen'=>$provider->profile->fundi_is_middlemen,
                               'fundi_have_tools'=>$provider->profile->fundi_have_tools,
                               'fundi_have_smartphone'=>$provider->profile->fundi_have_smartphone,
                               'screen_name'=>isset($provider->screen_name)?$provider->screen_name:'',
                               'profile_picture'=>$profile_picture,
                               'certificate_conduct'=>$certificate_conduct,
                               'nca'=>$nca,
                               'certification_data'=>$certification_data,
                               'subcategories'=>$subcategories,
                               'categories'=>$categories,
                               'is_rfq'=>$provider->profile->is_rfq,
                               'is_package'=>$provider->profile->is_package,
                               'is_hourly'=>$provider->profile->is_hourly,
                               'packages'=>$packages,
                               'hourly'=>$hourly,
                               'works_photo'=>$works_photo_Images
                               );          
         
          $response=array('status'=>true,'data'=>$provider_data,'message'=>'Record found');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to update provider details according to Id 
     *
     * @return [string] message
     */
    public function updateProviderProfile(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $provider=$certification_data=$provider_data=array();
        $user_id=$user->id;
        if($user)
        { 
           $rules = [  
                'name'=>'required',
            ]; 
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $provider= User::with(['profile','media','profile.experience_level','category_user.category','certification'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first();  
            if ($request->hasFile('profile_picture'))
            {
                    $file = $request->file('profile_picture');
                    $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                    $provider->addMedia($file)->toMediaCollection('profile_picture');
            }
            $user_data=array('name'=>isset($data['name'])?$data['name']:'',
                             'screen_name'=>isset($data['screen_name'])?$data['screen_name']:'');   
            $provider->update($user_data);
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>isset($data['location'])?$data['location']:'',
                                    'latitude'=>isset($data['latitude'])?$data['latitude']:'',
                                    'longitude'=>isset($data['longitude'])?$data['longitude']:'',
                                    'radius'=>isset($data['radius'])?$data['radius']:'',
                                    'passport_number'=>isset($data['passport_number'])?$data['passport_number']:'',
                                    'residential_address'=>isset($data['residential_address'])?$data['residential_address']:'',
                                    'reference'=>isset($data['reference'])?$data['reference']:'',
                                    'facebook_url'=>isset($data['facebook_url'])?$data['facebook_url']:'',
                                    'instagram_url'=>isset($data['instagram_url'])?$data['instagram_url']:'',
                                    'twitter_url'=>isset($data['twitter_url'])?$data['twitter_url']:'',
                                    'fundi_is_middlemen'=>isset($data['fundi_is_middlemen'])?$data['fundi_is_middlemen']:0,
                                    'fundi_have_tools'=>isset($data['fundi_have_tools'])?$data['fundi_have_tools']:0,
                                    'fundi_have_smartphone'=>isset($data['fundi_have_smartphone'])?$data['fundi_have_smartphone']:0,
                                    'experience_level_id'=>isset($data['experience_level_id'])?$data['experience_level_id']:'');
                $provider->profiles()->update($profile_data);
            }
            if ($request->hasFile('certificate_conduct')){
                $file = $request->file('certificate_conduct');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $provider->addMedia($file)->toMediaCollection('certificate_conduct');
            }
            if ($request->hasFile('nca')){
                $file = $request->file('nca');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $provider->addMedia($file)->toMediaCollection('nca');
            }
            $certification_img=Certification::where(['user_id'=>$provider->id,'type'=>'certification'])->first();
            $diploma_img=Certification::where(['user_id'=>$provider->id,'type'=>'diploma'])->first();
            $degree_img=Certification::where(['user_id'=>$provider->id,'type'=>'degree'])->first();

            $degree_title=isset($data['degree_title'])?$data['degree_title']:'';
            $diploma_title=isset($data['diploma_title'])?$data['diploma_title']:'';
            $certification_title=isset($data['certification_title'])?$data['certification_title']:'';

            if($certification_img)
              {
                $certification_data=array('title'=>$certification_title);
                $certification_img->update($certification_data);
               
              }else
              {
                 $certification_img=Certification::create(array('title'=>$certification_title,'type'=>'certification','user_id'=>$provider->id));
              }
              if($degree_img)
              {
                $degree_data=array('title'=>$degree_title);
                $degree_img->update($degree_data);
               
              }else
              {
                 $degree_img=Certification::create(array('title'=>$degree_title,'type'=>'degree','user_id'=>$provider->id));
              }
              if($diploma_img)
              {
                $diploma_data=array('title'=>$diploma_title);
                $diploma_img->update($diploma_data);
               
              }else
              {
                 $diploma_img=Certification::create(array('title'=>$degree_title,'type'=>'diploma','user_id'=>$provider->id));
              }

              if ($request->hasFile('certification')){
                $file = $request->file('certification');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $certification_img->addMedia($file)->toMediaCollection('certification');
              }
              if ($request->hasFile('diploma')){
                $file = $request->file('diploma');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $diploma_img->addMedia($file)->toMediaCollection('diploma');
              } 
              if ($request->hasFile('degree')){
                $file = $request->file('degree');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $degree_img->addMedia($file)->toMediaCollection('degree');
              }
             

            $response=array('status'=>true,'message'=>'Profile updated!');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get experience level
     *
     * @return [string] message
     */
    public function getExperienceLevel(Request $request){
     
        $experience_levels=array();     
        $experience_levels= ExperienceLevel::get(['id','title']);
        if(count($experience_levels))
        {  
            $response=array('status'=>true,'experience_levels'=>$experience_levels,'message'=>'Record found!');
        }else
        {
            $response=array('status'=>false,'message'=>'Record not found');
        } 
        return response()->json($response);
    }
    /**
     * API to update provider details according to Id 
     *
     * @return [string] message
     */
    public function updateProviderProfileInfo(Request $request){
        $user = Auth::user(); 
        $data = $request->all();        
        $user_id=$user->id;
        if($user)
        { 
            $rules = [  
                 'name'=>'required',
                 'email'             => 'required|email|unique:'.with(new User)->getTable().',email,'.$user->getKey(),          
                 'mobile_number'     => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number,'.$user->getKey()                 
            ]; 
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $user= User::with(['profile','media'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first(); 
            if ($request->hasFile('profile_picture'))
            {
                $file = $request->file('profile_picture');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('profile_picture');
            }
            $user_data=array('id'=>$request->id,
                             'name'=>$request->name,
                             'email'=>$request->email,
                             'mobile_number'=>$request->mobile_number);   
            $user->update($user_data);
            $response=array('status'=>true,'message'=>'Profile updated!');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
      /**
     * API to update provider details according to Id 
     *
     * @return [string] message
     */
    public function updateProviderPersonal(Request $request){
        $user = Auth::user(); 
        $data = $request->all();        
        $user_id=$user->id;
        if($user)
        { 
            $rules = [  
                  'location' => 'required',
                  'latitude' => 'required',
                  'longitude' => 'required',
                  'radius' => 'required',
                  'passport_number' => 'nullable',
                  'residential_address'=>'nullable',
                  'experience_level_id'=>'required',
                  'reference'=>'nullable',
                  'facebook_url'=>'nullable',
                  'instagram_url'=>'nullable',
                  'twitter_url'=>'nullable',
            ]; 
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $profile = Profile::where(array('user_id'=>$user_id));
           
             $fundi_is_middlemen=$request->fundi_is_middlemen;
             $fundi_have_tools=$request->fundi_have_tools;
             $fundi_have_smartphone=$request->fundi_have_smartphone;
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$request->location ,'radius'=>$request->radius,'latitude'=>$request->latitude,'longitude'=>$request->longitude,'experience_level_id'=>$request->experience_level_id,'facebook_url'=>$request->facebook_url,'twitter_url'=>$request->twitter_url,'instagram_url'=>$request->instagram_url,'fundi_is_middlemen'=>$fundi_is_middlemen,'fundi_have_tools'=>$fundi_have_tools,'fundi_have_smartphone'=>$fundi_have_smartphone);
                $profile->update($profile_data);
            }
            $certification_img=Certification::where(['user_id'=>$user_id,'type'=>'certification'])->first();
            $diploma_img=Certification::where(['user_id'=>$user_id,'type'=>'diploma'])->first();
            $degree_img=Certification::where(['user_id'=>$user_id,'type'=>'degree'])->first();
            $degree_title=isset($request->degree_text)?$request->degree_text:'';
            $diploma_title=isset($request->diploma_text)?$request->diploma_text:'';
            $certification_title=isset($request->certification_text)?$request->certification_text:'';
            
            if($certification_img)
              {
                $certification_data=array('title'=>$certification_title);
                $certification_img->update($certification_data);
               
              }else
              {
                 $certification_img=Certification::create(array('title'=>$certification_title,'type'=>'certification','user_id'=>$user_id));
              }
              if($degree_img)
              {
                $degree_data=array('title'=>$degree_title);
                $degree_img->update($degree_data);
               
              }else
              {
                 $degree_img=Certification::create(array('title'=>$degree_title,'type'=>'degree','user_id'=>$user_id));
              }
              if($diploma_img)
              {
                $diploma_data=array('title'=>$diploma_title);
                $diploma_img->update($diploma_data);
               
              }else
              {
                 $diploma_img=Certification::create(array('title'=>$degree_title,'type'=>'diploma','user_id'=>$user_id));
              }
              if ($request->hasFile('certification')){
                 $file = $request->file('certification');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $certification_img->addMedia($request->file('certification'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('certification');
              } 
              if ($request->hasFile('degree')){
                   $file = $request->file('degree');
                   $customname = time() . '.' . $file->getClientOriginalExtension();
                   $degree_img->addMedia($request->file('degree'))
                     ->usingFileName($customname)               
                     ->toMediaCollection('degree');
              } 
              if ($request->hasFile('diploma')){
                   $file = $request->file('diploma');
                   $customname = time() . '.' . $file->getClientOriginalExtension();
                   $diploma_img->addMedia($request->file('diploma'))
                     ->usingFileName($customname)               
                     ->toMediaCollection('diploma');
              } 
            $response=array('status'=>true,'message'=>'Profile updated!');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to update provider cat sub details according to Id 
     *
     * @return [string] message
     */
    public function updateProviderCatSubInfo(Request $request){
        $user = Auth::user(); 
        $data = $request->all();        
        $user_id=$user->id;
        if($user)
        { 
            $rules = [  
                  'category_id' => 'required',
                  'subcategory_id' => 'required',
                  'packages' => 'required',
                  'hourly' => 'required'
            ]; 
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
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
            $is_hourly=isset($request->is_hourly)?$request->is_hourly:0;
            $is_package=isset($request->is_package)?$request->is_package:0;
            $is_rfq=isset($request->is_rfq)?$request->is_rfq:0;

            $profile = Profile::where(array('user_id'=>$user_id));
            if(intval($user_id) > 0)
            {
                $profile_data=array('is_hourly'=>$is_hourly,'is_package'=>$is_package,'is_rfq'=>$is_rfq);
                $profile->update($profile_data);
            }            
            if($is_package==true)
            {
              PackageUser::where('user_id',$user_id)->delete();
              $packagesdata=json_decode(stripslashes($request->packages));
              if(intval($packagesdata) > 0 && !empty($packagesdata))
              { 
                foreach ($packagesdata as $key => $data) {                
                  $user->package_user()->create(['package_id'=>$data->package_id,'price'=>$data->price]);  
                }
              }
            } 
            if($is_hourly==true)
            {            
              HourlyCharge::where('user_id',$user_id)->delete();
              $hourlydata=json_decode(stripslashes($request->hourly));
              if(!empty($hourlydata))
              {               
                foreach ($hourlydata as $key => $data) {             
                  $user->hourly_charge()->create(['user_id'=>$user_id,'hours'=>$data->duration,'price'=>$data->price,'type'=>$data->type]);  
                }
              }
            }             
           $response=array('status'=>true,'message'=>'Profile updated!');
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to delete media work photo
     *
     * @return [string] message
     */
    public function deleteWorkphoto(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all();        
             
        $userdata=User::with(['media'])->where('id',$user->id)->first();
        if($userdata)
        {
            $validator = Validator::make($data, [
                'image_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $image_id=isset($data['image_id'])?$data['image_id']:'';
            $media = $userdata->media->find($image_id);
            if($media)
            {
                $media->delete();
                $response=array('status'=>true,'message'=>'Work photo deleted.');
            }else
            {
                $response=array('status'=>false,'message'=>'something went wrong!');
            }           
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }

    /**
     * API to send OTP to start and complete job
     *
     * @return [string] message
    */
    public function sendJobStartOTP(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all();
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        if($seeker)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required',
                'user_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }            
            $otp = rand(100000,999999);
            $booking_id=$request->booking_id;
            $user_id=$request->user_id;
            $bookings=Booking::where(['id'=>$booking_id])->first();  
            if($bookings)
            {
              $booking_data=array('otp'=>$otp);
              $bookings->update($booking_data);
              $data=array('otp'=>$otp);
              $response=array('status'=>true,'data'=>$data,'message'=>'You OTP has been sent successfully to provider');

              //start notification code                
              $notification_providerdata=User::where('id',$user_id)->first();
              if($notification_providerdata)
              {
              $notification_title='New schedule job OTP sent!';
              $notification_message='OTP is '.$otp. ' to verify with seeker.';
              if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
              {
                if(!empty($notification_providerdata->device_token))
                {
                  $device_token[]=$notification_providerdata->device_token;
                }      
                            
              }else
              {
                if(!empty($notification_providerdata->device_token))
                {
                  $device_token[]=$notification_providerdata->device_token;
                }                           
              } 
              if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
              {                     
                 sendIphoneNotifications($notification_title,$notification_message,$device_token);
              }
              }
              //end notification code              
            }else
            {
              $response=array('status'=>false,'message'=>'something went wrong!');
            }            
        }  
        else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }

      /**
     * API to check OTP to for start job
     *
     * @return [string] message checkJobStartedOTP
    */
    public function checkJobStartOTP(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all();
        $response_data=array();
        $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');
        $seeker = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $seeker=$seeker->where(['id'=>$user->id])->first();
        if($seeker)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required', 
                'user_id'=>'required',
                'otp'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            } 
            $booking_id=$request->booking_id;            
            $user_id=$request->user_id;
            $otp=$request->otp;
            $bookings=Booking::where(['id'=>$booking_id,'otp'=>$otp])->first();  
            if($bookings)
            {
               
               $response_data=array('otp'=>$otp);
               $booking_data=array('job_status'=>config('constants.JOB_STATUS_STARTED'));
               $bookings->update($booking_data);
               $response=array('status'=>true,'data'=>$response_data,'message'=>'OTP verified. now job started');

                //start notification code                
                $notification_providerdata=User::where('id',$user_id)->first();
                if($notification_providerdata)
                {
                 
                $notification_title='OTP verified.';
                $notification_message='OTP verified! job schedule started.';
                if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                {
                  if(!empty($notification_providerdata->device_token))
                  {
                    $device_token[]=$notification_providerdata->device_token;
                  }      
                              
                }else
                {
                  if(!empty($notification_providerdata->device_token))
                  {
                    $device_token[]=$notification_providerdata->device_token;
                  }                           
                } 
                if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                {                     
                   sendIphoneNotifications($notification_title,$notification_message,$device_token);
                }
                }
                //end notification code 
            }else
            {
               $response=array('status'=>false,'message'=>'something went wrong!');
            }
           
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    } 
    /**
     * API to send OTP to complete job
     *
     * @return [string] message sendJobCompleteOTP
    */
    public function sendJobCompleteOTP(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all();
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
        $provider = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $provider=$provider->where(['id'=>$user->id])->first();
       
        if($provider)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required',
                'user_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            } 
            $otp = rand(100000,999999);
            $booking_id=$request->booking_id;
            $user_id=$request->user_id;
            $bookings=Booking::where(['id'=>$booking_id])->first(); 
            if($bookings)
            {
               $booking_data=array('otp'=>$otp);
               $bookings->update($booking_data);
               $data=array('otp'=>$otp);
               $response=array('status'=>true,'data'=>$data,'message'=>'You OTP has been sent successfully to seeker');
                //start notification code                
                $notification_providerdata=User::where('id',$bookings->requested_id)->first();
                if($notification_providerdata)
                {
                 $notification_title='New complete job OTP sent!';
                 $notification_message='OTP is '.$otp. ' to verify with provider.';
                if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                {
                  if(!empty($notification_providerdata->device_token))
                  {
                    $device_token[]=$notification_providerdata->device_token;
                  }      
                              
                }else
                {
                  if(!empty($notification_providerdata->device_token))
                  {
                    $device_token[]=$notification_providerdata->device_token;
                  }                           
                } 
                if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
                {                     
                   sendIphoneNotifications($notification_title,$notification_message,$device_token);
                }
                }
                 //end notification code 
            }else
            {
              $response=array('status'=>false,'message'=>'something went wrong!');
            }
           

        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
    /**
     * API to check OTP to complete job otp
     *
     * @return [string] message
    */
    public function checkJobCompleteOTP(Request $request){ 
        $user = Auth::user(); 
        $data = $request->all();
        $role_id =  config('constants.ROLE_TYPE_PROVIDER_ID');
        $provider = User::with(['roles'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
        });
        $provider=$provider->where(['id'=>$user->id])->first();

        if($provider)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required', 
                'user_id'=>'required',
                'otp'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            } 
            $booking_id=$request->booking_id;
            $otp=$request->otp;
            $response_data=array('otp'=>$otp);            
            $bookings=Booking::where(['id'=>$booking_id,'otp'=>$otp])->first();  
            if($bookings)
            {
               $response_data=array('otp'=>$otp);
               $booking_data=array('job_status'=>config('constants.JOB_STATUS_COMPLETED'));
               $bookings->update($booking_data);
               $response=array('status'=>true,'data'=>$response_data,'message'=>'OTP verified. now job completed');

              //start notification code                
              $notification_providerdata=User::where('id',$bookings->requested_id)->first();
              if($notification_providerdata)
              {
              $notification_title='OTP verified.';
              $notification_message='OTP verified! job schedule completed.';
              if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
              {
                if(!empty($notification_providerdata->device_token))
                {
                  $device_token[]=$notification_providerdata->device_token;
                }      
                            
              }else
              {
                if(!empty($notification_providerdata->device_token))
                {
                  $device_token[]=$notification_providerdata->device_token;
                }                           
              } 
              if($notification_providerdata->device_type==config('constants.DEVICE_TYPE_IOS'))
              {                     
                 sendIphoneNotifications($notification_title,$notification_message,$device_token);
              }
              }
              //end notification code  

            }else
            {
               $response=array('status'=>false,'message'=>'something went wrong!');
            }      
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
      }
}
