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
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
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
            //if is_rfq type 
           /* $is_rfq=isset($data['is_rfq'])?$data['is_rfq']:'';
            if($subcategories!='')
            {
              $subcategories=explode(',',$subcategories);
              if($is_rfq==1)
              {
                 $providers= User::with(['category_user','profile','hourly_charge','roles'])
                  ->whereHas('profile', function($query) use ($is_rfq) {             
                      $query->where('is_rfq',$is_rfq);                                 
                  }) 
                  ->whereHas('category_user', function($query) use ($subcategory_id) {
                     $query->whereIn('category_id',$subcategory_id);
                  });   
                   $providers->whereHas('roles', function($query) use ($role_id) {
                      $query->where('id', config('constants.ROLE_TYPE_PROVIDER_ID'));
                  })->get();
              }

            }*/
            //need to send request or notification to all providers user that are belong to Lat long address.               

            $response=array('status'=>true,'booking'=>$booking->id,'message'=>'Send request saved successfully.');
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
           $provider= User::with(['profile','media','profile.experience_level','profile.payment_option','profile.city','category_user.category','certification'])
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
          //print_r($provider->certification);
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
          $provider['age']=(string)$age;
          $provider['rating']=$rating;
          unset($provider['media']);
          unset($provider['certification']);

          $response=array('status'=>true,'data'=>$provider,'message'=>'Record found');
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
            $role_id =  config('constants.ROLE_TYPE_SEEKER_ID');

            /*$providers= CategoryUser::with(['category','user','user.profile','user.hourly_charge'])
            ->whereHas('category', function($query) use ($subcategory_id) {             
              $query->whereIn('category_id', $subcategory_id);            
            })  */
           $providers= User::with(['category_user','profile','hourly_charge','roles'])
           /*->whereHas('category_user', function($query) use ($subcategory_id) {             
              $query->whereIn('category_id', $subcategory_id);            
            }) */
            /*->whereHas('category_user', function($query) use ($subcategory_id) {             
              $query->whereIn('category_id', $subcategory_id);            
            }) */
            
            ->whereHas('profile', function($query) use ($is_hourly,$is_rfq) {
              if($is_hourly==true)
              {
                $query->where('is_hourly',$is_hourly); 
              }
              if($is_rfq==true)
              {
                $query->where('is_rfq',$is_rfq); 
              }                       
            }) 

            ->whereHas('category_user', function($query) use ($subcategory_id) {
               $query->whereIn('category_id',$subcategory_id);
            });   
             $providers->whereHas('roles', function($query) use ($role_id) {
                $query->where('id', config('constants.ROLE_TYPE_PROVIDER_ID'));
            });      
            //->whereIn('category_id',$subcategory_id);
            $start_limit=(isset($request->start_limit)?$request->start_limit:0)*$end_limit;
            $providers=$providers->offset($start_limit)->limit($end_limit)->get();
            $providersdata=[];
            foreach ($providers as $key => $provider) {              
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
               //$address =$provider->user->profile->work_address;
               //$address."-".$Kilometer_distance."-".$radius."-u".$provider->user->profile->user_id."-radi".(floatval($provider->user->profile->radius)); echo "<br/>";
               $rating=0.0;
               
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
     * API to get all providers jobs listing 
     *
     * @return [string] message
    */
    public function getJob(Request $request){
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
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }

            $end_limit =config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $bookings= Booking::with(['category','user','user.profile','subcategory'])->where(['requested_id'=>$user->id]);
            if($type==config('constants.PAYMENT_STATUS_PENDING'))
            { 
              //$bookings=$bookings->where('datetime','<',date('Y-m-d H:i:s'))->where('status'=>config('constants.PAYMENT_STATUS_ACCEPTED'));
              //$bookings=$bookings->where(array('status'=>config('constants.PAYMENT_STATUS_PENDING'),'is_quoted'=>true));
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_REQUESTED'));
            }elseif($type==config('constants.PAYMENT_STATUS_REQUESTED'))
            {
              //$bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_REQUESTED'));
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_ACCEPTED'));
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
                                      'name'=>isset($booking->user->name)?$booking->user->name:'',
                                      'email'=>isset($booking->user->email)?$booking->user->email:'',
                                      'mobile_number'=>isset($booking->user->mobile_number)?$booking->user->mobile_number:'',
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
              $response=array('status'=>false,'message'=>'no record found');
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
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            //echo $user->id;
            $end_limit =config('constants.DEFAULT_WEBSERVICE_PAGINATION_ENDLIMIT');
            $bookings= Booking::with(['category','user','user.profile','subcategory']);//,'booking_user'

            
            if($type==config('constants.PAYMENT_STATUS_REQUESTED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_REQUESTED'));
            }elseif($type==config('constants.PAYMENT_STATUS_ACCEPTED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_ACCEPTED'));
            }elseif($type==config('constants.PAYMENT_STATUS_COMPLETED'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_COMPLETED'));
            }elseif($type==config('constants.PAYMENT_STATUS_PENDING'))
            {
              $bookings=$bookings->where('status',config('constants.PAYMENT_STATUS_QUOTED'));
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

            //print_r($bookings->booking_user);
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

                $provider_latitude=isset($booking->user->profile->latitude)?$booking->user->profile->latitude:'';
                $provider_longitude=isset($booking->user->profile->longitude)?$booking->user->profile->longitude:''; 
                $provider_radius=isset($booking->user->profile->radius)?$booking->user->profile->radius:'';   
                $provider_location=isset($booking->user->profile->work_address)?$booking->user->profile->work_address:'';           
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
                $providerdata=User::with(['profile'])->where('id',$user->id)->first();
                if($provider_latitude=='' || $provider_longitude=='' || $provider_radius=='')
                {
                  $provider_latitude=$providerdata->profile->latitude;
                  $provider_longitude=$providerdata->profile->longitude;
                  $provider_radius=$providerdata->profile->radius;
                }                 

                $Kilometer_distance=  $this->distance($provider_latitude,$provider_longitude, $booking_latitude,$booking_longitude , "K");
                $provider_radius=floatval($provider_radius);
                $Kilometer_distance=round($Kilometer_distance, 2);               
                //echo $provider_location."-kk".$Kilometer_distance."-r".$provider_radius."-u".$provider_user_id."Id".$booking->id; echo "<br/>";
                //echo  "Provider ra".$provider_radius."KK".$Kilometer_distance."B".$booking->id;echo "<br/>";
                if($type==config('constants.PAYMENT_STATUS_REQUESTED'))
                {
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
                }else
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
             $booking_data=$bookingtype;
           }
            if(count($booking_data)>0)
            {
              $response=array('status'=>true,'bookingdata'=>$booking_data,'message'=>'record found');
            }else
            {
              $booking_data[$type]=$bookingtype;
              $response=array('status'=>false,'message'=>'no record found');
            }
            
        }else
        {
            $booking_data[$type]=$bookingtype;
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
        /*$userdata=User::with(['roles','profile','media'])->whereHas('roles', function($query) use ($role_id){
              $query->where('id', $role_id);
            })->where('id',$user->id)->first();*/      
        $userdata=User::with(['profile','media'])->where('id',$user->id)->first();
        if($userdata)
        {
            $validator = Validator::make($data, [
                'booking_id'=>'required', 
            ]);

            if ($validator->fails()) {
                return response()->json(['status'=>false,'message'=>$validator->errors()->first()]);
            }
            $booking= Booking::where('id',$request->booking_id)->first(); 
            $is_rfq=isset($booking->is_rfq)?$booking->is_rfq:0; 
            if(($user->roles->first()->id==config('constants.ROLE_TYPE_PROVIDER_ID')) && ($is_rfq==0))
                { 
                  $booking=$booking->where('user_id',$userdata->id);
                }else if(($user->roles->first()->id==config('constants.ROLE_TYPE_SEEKER_ID')) && ($is_rfq==0))
                {
                  $booking=$booking->where('requested_id',$userdata->id);
                }

                /*else if($is_rfq==1)
                {
                  $booking=$booking;
                }*/
           //     echo $booking->id;
           //$booking=$booking->first();
           /* $boking_id=isset($booking->id)?$booking->id:'';
            if($boking_id)
            {
              $booking=$booking->where('id',$boking_id)->first();
            }else
            {
              $booking=$booking->first();
            }  */ 
            //echo $user->id;
            $boking_id=isset($request->booking_id)?$request->booking_id:'';
            $booking=$booking->where('id',$boking_id)->first();
            
           
           if($booking)
            {
              if($is_rfq==0)
              {
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
                                                      'budget'=>$booking->budget,
                                                      'is_rfq'=>$booking->is_rfq,
                                                      'is_quoted'=>$booking->is_quoted,
                                                      'request_for_quote_budget'=>$booking->request_for_quote_budget,
                                                      'is_hourly'=>$booking->is_hourly,
                                                      'estimated_hours'=>$booking->estimated_hours,
                                                      'min_budget'=>$booking->min_budget,
                                                      'max_budget'=>$booking->max_budget,
                                                      'datetime'=>$booking->datetime,
                                                      'created_at'=>$booking->created_at),
                                      'rfq_data'=>$rfq_bookinguserData);
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
               
                            
                //unset($user_data['media']);  
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
                                                      'budget'=>$booking->budget,
                                                      'is_rfq'=>$booking->is_rfq,
                                                      'is_quoted'=>$booking->is_quoted,
                                                      'request_for_quote_budget'=>$booking->request_for_quote_budget,
                                                      'is_hourly'=>$booking->is_hourly,
                                                      'estimated_hours'=>$booking->estimated_hours,
                                                      'min_budget'=>$booking->min_budget,
                                                      'max_budget'=>$booking->max_budget,
                                                      'datetime'=>$booking->datetime,
                                                      'created_at'=>$booking->created_at),
                                      'rfq_data'=>$rfq_bookinguserData);
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
              //$booking_user = BookingUser::where(['user_id'=>$userdata->id,'booking_id'=>$booking->id])->first();
              $booking_data=array('user_id'=>$userdata->id,
                                   'booking_id'=>$booking->id,
                                   'status'=>config('constants.PAYMENT_STATUS_DECLINED'),
                                   'reason'=>$request->reason);
              $booking->booking_user()->create($booking_data);
              /*if($booking_user)
              {

                $booking_user->update($booking_data);
              }else
              {
                BookingUser::create($booking_data);
              }  */            
              
              $response=array('status'=>true,'data'=>$booking->id,'message'=>'Job declined done');
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
              /*if($request->type=='is_package')
              {
                  $booking=$booking->where('is_package',1);
              }else*/
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
                    foreach ($files as $file) 
                    {
                       $customname = time() . '.' . $file->getClientOriginalExtension();
                       $booking->addMedia($file)
                         ->usingFileName($customname)
                         ->toMediaCollection('booking_works_photo');
                    }
                 }
                 //send notification to seeker job accepted by provider
                 $response=array('status'=>true,'data'=>$booking->id,'message'=>'Job Quoted done');
              }else
              {
                 $response=array('status'=>false,'message'=>'no record found');
              } 

            }else if($request->type=='is_rfq') 
            {             
              $booking=$booking->where('is_rfq',1)->first();              
              if($booking)
              {
                 $bookingUser= BookingUser::where(['booking_id'=>$request->booking_id,'user_id'=>$user->id])->first();
                 if($bookingUser)
                 {
                   $booking_user=array('is_rfq'=>1,
                                       'budget'=>$request->price,
                                       'service_datetime'=>$request->service_datetime,
                                       'requirement'=>$request->requirement,  
                                       'is_quoted'=>1,
                                       'status'=>config('constants.PAYMENT_STATUS_QUOTED'),
                                       'comment'=>$request->comment
                                       );
                   $bookingUser->update($booking_user);
                   if ($request->hasFile('works_photo'))
                   {
                     $files = $request->file('works_photo');
                      foreach ($files as $file) 
                      {
                         $customname = time() . '.' . $file->getClientOriginalExtension();
                         $booking_users->addMedia($file)
                           ->usingFileName($customname)
                           ->toMediaCollection('booking_works_photo');
                      }
                   } 
                   $response=array('status'=>true,'data'=>$booking->id,'message'=>'Job Quoted done');
                 }else
                 {
                   $response=array('status'=>false,'message'=>'no request found');
                 }        
                 
              }else
              {
                   $response=array('status'=>false,'message'=>'no record found');
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
     

}
