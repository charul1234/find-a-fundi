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
                $response=array('status'=>false,'packages'=>$packagesdata,'message'=>'Record not found');
            }
        }else
        {
                $response=array('status'=>false,'packages'=>$packagesdata,'message'=>'Oops! Invalid credential.');
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
            }
           $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'booking'=>'','message'=>$validator->errors()->first()]);
            }
            $provider_id=isset($data['user_id'])?$data['user_id']:0;
            $data['user_id']=$provider_id;
            $data['datetime']=$data['date'].' '.$data['time'];
            $data['requested_id']=isset($user->id)?$user->id:0;
            $data['status']='requested';
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
           $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city','category_user.category'])
            ->whereHas('profile', function($query) use ($user_id) {    
              $query->where('user_id',$user_id);            
            })->first();            
          $subcategories=[];
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
          /*$certificate_conduct = $provider->getMedia('certificate_conduct');
          $provider['certificate_conduct']=''; 
          $provider['certificate_conduct_name']='';

          
          if(isset($provider) && $provider->getMedia('certificate_conduct')->count() > 0 && file_exists($provider->getFirstMedia('certificate_conduct')->getPath()))
          {           
            $provider['certificate_conduct']=$provider->getFirstMedia('certificate_conduct')->getFullUrl();
            $provider['certificate_conduct_name']=$provider->getFirstMedia('certificate_conduct')->name;
          } */
          $certifications=Certification::where('user_id',$user_id)->get();
          $certificationdata='';      
          $degreedata='';  
          $deplomadata='';  
          if(count($certifications)>0)
          {
             foreach ($certifications as $key => $certification) {
              if($certification->type=='certification')
              {
                $certificationdata=$certification->title;
              }
              if($certification->type=='degree')
              {
                $degreedata=$certification->title;
              }
              if($certification->type=='deploma')
              {
                $deplomadata=$certification->title;
              }
               
             }
          }
          $provider['certification_text']=$certificationdata;
          $provider['degree_text']=$degreedata;
          $provider['diploma_text']=$deplomadata;
          
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
          $provider['age']=(string)$age;
          unset($provider['media']);

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
            'category_id' => 'required',
            'subcategory_id' => 'required',
           ]);
           
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

           $location=isset($data['location'])?$data['location']:'';
           $latitude=isset($data['latitude'])?$data['latitude']:'';
           $longitude=isset($data['longitude'])?$data['longitude']:'';
           $radius=isset($data['radius'])?$data['radius']:'';
           $passport_number=isset($data['passport_number'])?$data['passport_number']:'';
           $residential_address=isset($data['residential_address'])?$data['residential_address']:'';
           $year_experience=isset($data['year_experience'])?$data['year_experience']:'';
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

           $user_id=$user->id;
           if($user_id){ 
                $userdata=array('screen_name'=>$screen_name);                
                $user->update($userdata);
           }
            
            $profile = Profile::where(array('user_id'=>$user_id));
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$location,'latitude'=>$latitude,'longitude'=>$longitude,'radius'=>$radius,'passport_number'=>$passport_number,'residential_address'=>$residential_address,'year_experience'=>$year_experience,'reference'=>$reference,'facebook_url'=>$facebook_url,'instagram_url'=>$instagram_url,'twitter_url'=>$twitter_url,'fundi_is_middlemen'=>$fundi_is_middlemen,'fundi_have_tools'=>$fundi_have_tools,'fundi_have_smartphone'=>$fundi_have_smartphone);
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
            if ($request->hasFile('certification')){
                $file = $request->file('certification');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('certification');
            }
          
            if ($request->hasFile('diploma')){
                $file = $request->file('diploma');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('diploma');
            }
            if ($request->hasFile('degree')){
                $file = $request->file('degree');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('degree');
            }
           
            if ($request->hasFile('works_photo')){
                 $files = $request->file('works_photo');
                  foreach ($files as $file) {
                     $customname = time() . '.' . $file->getClientOriginalExtension();
                     $user->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('works_photo');
               }
            }
            
            /*$certification_data=json_decode(stripslashes($request->certification_data));
            if(intval($certification_data) > 0 && !empty($certification_data))
            { 
              foreach ($certification_data as $key => $data) {                
                $user->Certification()->create(['user_id'=>$user_id,'title'=>$data->title,'type'=>$data->type]);  
              }
            }*/
            if(isset($certification_text) && $certification_text!='')
            {
              $user->Certification()->create(['user_id'=>$user_id,'title'=>$certification_text,'type'=>'certification']); 
            }
            if(isset($diploma_text) && $diploma_text!='')
            {
              $user->Certification()->create(['user_id'=>$user_id,'title'=>$diploma_text,'type'=>'diploma']); 
            }
            if(isset($degree_text) && $degree_text!='')
            {
              $user->Certification()->create(['user_id'=>$user_id,'title'=>$degree_text,'type'=>'degree']); 
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

            $providers= CategoryUser::with(['category','user','user.profile','user.hourly_charge'])
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
              if(isset($provider->user) && $provider->user->getMedia('profile_picture')->count() > 0 && file_exists($provider->user->getFirstMedia('profile_picture')->getPath()))
              {
                 $provider['profile_picture']=$provider->user->getFirstMedia('profile_picture')->getFullUrl();
              }else
              {
                 $provider['profile_picture']= asset(config('constants.NO_IMAGE_URL'));
              }
              
                  
                $Kilometer_distance=  $this->distance($provider->user->profile->latitude,$provider->user->profile->longitude , $latitude,$longitude , "K");
                $radius=floatval($provider->user->profile->radius);
                $Kilometer_distance=round($Kilometer_distance, 2);
               //$address =$provider->user->profile->work_address;
               //$address."-".$Kilometer_distance."-".$radius."-u".$provider->user->profile->user_id."-radi".(floatval($provider->user->profile->radius)); echo "<br/>";
               $rating=0.0;
               
               if($provider->user->profile->radius!='null' && $provider->user->profile->radius!='')
               {
               if($radius>=$Kilometer_distance)
                {
                   //$providersdata[]=$provider;     
                   $providersdata[]=array('user_id'=>$provider->user->id,
                                          'name'=>$provider->user->name,
                                          'email'=>$provider->user->email,
                                          'mobile_number'=>$provider->user->mobile_number,
                                          'is_hourly'=>$provider->user->profile->is_hourly,
                                          'is_rfq'=>$provider->user->profile->is_rfq,
                                          'profile_picture'=>$provider['profile_picture'],
                                          'rating'=>$rating
                                          );              
                }
               }              
            }            

            if ($validator->fails()) {
                return response()->json(['status'=>false,'providers'=>'','message'=>$validator->errors()->first()]);
            }
             if(count($providersdata))
            { 

                $response=array('status'=>true,'providers'=>$providersdata,'message'=>'Record found');
            }else
            {
                $response=array('status'=>false,'providers'=>$providersdata,'message'=>'Record not found');
            }
        }else
        {
            $response=array('status'=>false,'providers'=>$providersdata,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get all providers jobs listing 
     *
     * @return [string] message
    */
    public function getProvidersJob(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $bookingdata=$booking_data=$bookings=$bookingtype=array();
        $type=isset($request->type)?$request->type:'';
        if($user)
        {          
            $validator = Validator::make($data, [
                'type'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'bookingdata'=>$booking_data,'message'=>$validator->errors()->first()]);
            }

            $end_limit =config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $bookings= Booking::with(['category','user','user.profile','subcategory'])->where(['requested_id'=>$user->id,'is_hourly'=>true]);
            if($type==config('constants.PAYMENT_STATUS_PENDING'))
            { 
              //$bookings=$bookings->where('datetime','<',date('Y-m-d H:i:s'))->where('status'=>config('constants.PAYMENT_STATUS_ACCEPTED'));
              $bookings=$bookings->where(array('status'=>config('constants.PAYMENT_STATUS_PENDING'),'is_quoted'=>true));
            }elseif($type==config('constants.PAYMENT_STATUS_REQUESTED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_REQUESTED'));
            }elseif($type==config('constants.PAYMENT_STATUS_COMPLETED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_COMPLETED'));
            }else
            {
              $bookings=$bookings->where('datetime','=',date('Y-m-d H:i:s'));
            }
            $bookings=$bookings->orderBy('datetime','desc');
            
            $start_limit=(isset($request->page)?$request->page:0)*$end_limit;
            $bookings=$bookings->offset($start_limit)->limit($end_limit)->get();

           if(count($bookings)>0)
           {
            foreach ($bookings as $key => $booking) 
             {  
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
              $profile_picture='';
              if(isset($booking->user) && $booking->user->getMedia('profile_picture')->count() > 0 && file_exists($booking->user->getFirstMedia('profile_picture')->getPath()))
              {
                $profile_picture=$booking->user->getFirstMedia('profile_picture')->getFullUrl();
              }else
              {
                  $profile_picture = asset(config('constants.NO_IMAGE_URL'));
              } 
              

                  $bookingtype[$type][]=array(
                                      'booking_id'=>$booking->id,
                                      'category_id'=>$booking->category_id,
                                      'user_id'=>$booking->user_id,
                                      'title'=>$booking->title,
                                      'description'=>$booking->description,
                                      'location'=>$booking->location,
                                      'latitude'=>$booking->latitude,
                                      'longitude'=>$booking->longitude,
                                      'budget'=>$booking->budget,
                                      'is_rfq'=>$booking->is_rfq,
                                      'request_for_quote_budget'=>$booking->request_for_quote_budget,
                                      'is_hourly'=>$booking->is_hourly,
                                      'min_budget'=>$booking->min_budget,
                                      'max_budget'=>$booking->max_budget,
                                      'datetime'=>$booking->datetime,
                                      'requested_id'=>$booking->requested_id,
                                      'categories'=>$categories,
                                      'subcategories'=>$subcategories,
                                      'name'=>$booking->user->name,
                                      'email'=>$booking->user->email,
                                      'mobile_number'=>$booking->user->mobile_number,
                                      'profile_picture'=>$profile_picture
                                      ); 
                 
             }
             $booking_data=$bookingtype;
           }
            if(count($booking_data)>0)
            {
              $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'record found');
            }else
            {
              $booking_data[$type]=$bookingtype;
              $response=array('status'=>false,'bookingdata'=>$booking_data,'message'=>'no record found');
            }
            
        }else
        {
            $booking_data[$type]=$bookingtype;
            $response=array('status'=>false,'bookingdata'=>$bookingdata,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);
    }
    /**
     * API to get all my provider jobs listing 
     *
     * @return [string] message
    */
    public function getMyJobs(Request $request){
        $user = Auth::user(); 
        $data = $request->all(); 
        $bookingdata=$booking_data=$bookings=$bookingtype=array();
        $type=isset($request->type)?$request->type:'';
        if($user)
        {          
            $validator = Validator::make($data, [
                'type'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'bookingdata'=>$booking_data,'message'=>$validator->errors()->first()]);
            }
            //echo $user->id;
            $end_limit =config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $bookings= Booking::with(['category','user','user.profile','subcategory']);
            
            if($type==config('constants.PAYMENT_STATUS_REQUESTED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_REQUESTED'));
            }elseif($type==config('constants.PAYMENT_STATUS_COMPLETED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_COMPLETED'));
            }elseif($type==config('constants.PAYMENT_STATUS_PENDING'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_PENDING'));
            }elseif($type==config('constants.PAYMENT_STATUS_DECLINED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_DECLINED'));
            }else
            {
              $bookings=$bookings->where('datetime','=',date('Y-m-d H:i:s'));
            }
            $bookings=$bookings->orderBy('datetime','desc');
            if($user->id!='')
            {
              
              $bookings->whereRaw("(user_id= '$user->id' OR user_id=0)");
            }
            
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $bookings=$bookings->offset($start_limit)->limit($end_limit)->get();
            echo $user->id;
            //print_r($bookings->all());
            //die;
            
           if(count($bookings)>0)
           {
            foreach ($bookings as $key => $booking) 
             {  
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
              $booking_latitude=$booking->latitude;
              $booking_longitude=$booking->longitude;
              $provider_user_id=isset($booking->user->profile->user_id)?$booking->user->profile->user_id:'';
              $provider_name=$provider_email=$provider_mobile_number=$provider_profile_picture=$provider_latitude=$provider_longitude=$provider_radius=$provider_location='';
              if($provider_user_id)
              {
                $provider_latitude=$booking->user->profile->latitude;
                $provider_longitude=$booking->user->profile->longitude; 
                $provider_radius=$booking->user->profile->radius;   
                $provider_location=$booking->user->profile->work_address;           
                $provider_name=$booking->user->name;
                $provider_email=$booking->user->email;
                $provider_mobile_number=$booking->user->mobile_number;
                if(isset($booking->user) && $booking->user->getMedia('profile_picture')->count() > 0 && file_exists($booking->user->getFirstMedia('profile_picture')->getPath()))
                {
                    $provider_profile_picture=$booking->user->getFirstMedia('profile_picture')->getFullUrl();
                }else
                {
                    $provider_profile_picture = asset(config('constants.NO_IMAGE_URL'));
                }
               }
                $providerdata=User::with(['profile'])->where('id',$user->id)->get();
                print_r("<pre>");
                print_r($providerdata->profile);

                $Kilometer_distance=  $this->distance($provider_latitude,$provider_longitude, $booking_latitude,$booking_longitude , "K");
                $provider_radius=floatval($provider_radius);
                $Kilometer_distance=round($Kilometer_distance, 2);               
                //echo $provider_location."-kk".$Kilometer_distance."-r".$provider_radius."-u".$provider_user_id."Id".$booking->id; echo "<br/>";
                //echo  "Provider ra".$provider_radius."KK".$Kilometer_distance."B".$booking->id;echo "<br/>";

                if($provider_radius!='null' && $provider_radius!='')
                {
                 if($provider_radius>=$Kilometer_distance)
                  {  
                       $bookingtype[$type][]=array(
                                      'booking_id'=>$booking->id,
                                      'type'=>$booking_type,
                                      'category_id'=>$booking->category_id,
                                      'user_id'=>$booking->user_id,
                                      'title'=>$booking->title,
                                      'description'=>$booking->description,
                                      'location'=>$booking->location,
                                      'latitude'=>$booking->latitude,
                                      'longitude'=>$booking->longitude,
                                      'budget'=>$booking->budget,
                                      'is_rfq'=>$booking->is_rfq,
                                      'request_for_quote_budget'=>$booking->request_for_quote_budget,
                                      'is_hourly'=>$booking->is_hourly,
                                      'min_budget'=>$booking->min_budget,
                                      'max_budget'=>$booking->max_budget,
                                      'datetime'=>$booking->datetime,
                                      'requested_id'=>$booking->requested_id,
                                      'categories'=>$categories,
                                      'subcategories'=>$subcategories,
                                      'status'=>$booking->status,
                                      'name'=>$provider_name,
                                      'email'=>$provider_email,
                                      'mobile_number'=>$provider_mobile_number,
                                      'profile_picture'=>$provider_profile_picture
                                      );                
                  }
               }                 
             }
             $booking_data=$bookingtype;
           }
            if(count($booking_data)>0)
            {
              $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'record found');
            }else
            {
              $booking_data[$type]=$bookingtype;
              $response=array('status'=>false,'bookingdata'=>$booking_data,'message'=>'no record found');
            }
            
        }else
        {
            $booking_data[$type]=$bookingtype;
            $response=array('status'=>false,'bookingdata'=>$bookingdata,'message'=>'Oops! Invalid credential.');
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
