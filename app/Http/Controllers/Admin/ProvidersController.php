<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Profile;
use Validator;
use Auth;
use DataTables;
use Config;
use Form;
use DB;
use App\ExperienceLevel;
use App\HourlyCharge;
use App\Package;
use App\Company;
use App\Certification;
use App\Review;
use App\Transaction;
use App\Schedule;
use App\Booking;
use Edujugon\PushNotification\PushNotification;
use App\Notifications\PushNotifications;

class ProvidersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){  

        return view('admin/providers/index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsers(Request $request){
        $role_id =config('constants.ROLE_TYPE_PROVIDER_ID'); 

        $users = User::with(['roles','media','profile'])->whereHas('roles', function($query){
            $query->where('id','!=' ,config('constants.ROLE_TYPE_SUPERADMIN_ID'));
        });

        if(intval($role_id) > 0)
            $users->whereHas('roles', function($query) use ($role_id) {
                $query->where('id', config('constants.ROLE_TYPE_PROVIDER_ID'));
            });

        $users = $users->select(DB::raw('users.*, users.name as user_name'));
        $users=$users->orderBy('id','DESC');

        return DataTables::of($users)
            ->orderColumn('media.name', '-name $1')
            ->editColumn('created_at', function($user){
                return date(config('constants.DATETIME_FORMAT'), strtotime($user->created_at));
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereRaw("LOWER(DATE_FORMAT(users.created_at,'".config('constants.MYSQL_DATETIME_FORMAT')."')) like ?", ["%$keyword%"]);
            })
            ->editColumn('media.name', function ($user) {
                if(isset($user) && $user->getMedia('profile_picture')->count() > 0 && file_exists($user->getFirstMedia('profile_picture')->getPath()))
                {
                    $image = $user->getFirstMedia('profile_picture')->getFullUrl();
                }else{
                    $image = asset(config('constants.NO_IMAGE_URL'));
                }
                return '<img src="'.$image.'" width="100">';
            })
            ->editColumn('is_active', function ($user) {
                if($user->is_active == TRUE )
                {
                    return "<a href='".route('admin.providers.status',$user->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.providers.status',$user->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->editColumn('is_verify', function ($user) {
                if($user->is_verify == TRUE )
                {
                    return "Yes";
                }else{                    
                    return "No";
                }
            })
            ->addColumn('action', function ($user) {
                return
                        //view
                       '<a href="'.route('admin.providers.view',[$user->id]).'" class="btn btn-info btn-circle btn-sm"><i class="fas fa-eye"></i></a> '.

                        // edit
                        '<a href="'.route('admin.providers.edit',[$user->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                        // Delete
                          Form::open(array(
                                      'style' => 'display: inline-block;',
                                      'method' => 'DELETE',
                                       'onsubmit'=>"return confirm('Do you really want to delete?')",
                                      'route' => ['admin.providers.destroy', $user->id])).
                          ' <button type="submit" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>'.
                          Form::close();
            })
            ->rawColumns(['media.name','is_active','is_verify','action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(){
        $roles = Role::where('id', config('constants.ROLE_TYPE_PROVIDER_ID'))->get()->pluck('name', 'id')->map(function($value, $key){
            return ucwords($value);
        });
        $experience_levels=ExperienceLevel::get()->pluck('title', 'id')->map(function($value, $key){
            return ucwords($value);
        });
        return view('admin.providers.create', compact('roles','experience_levels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

        $media_max_size = config('medialibrary.max_file_size') / 1024; 
        $rules = [
           /* 'role_id'           => 'required', */
            'name'              => 'required', 
            'email'             => 'required|email|unique:'.with(new User)->getTable().',email',
            'profile_picture'   => 'image',
            'password'          => 'required|confirmed',
            'mobile_number'     => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number',
            'experience_level'  => 'required',
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
        ];
        if (isset($request->security_check) && $request->security_check==TRUE) {
            //  $rules1 = [
            $rules['company_name']        = 'required';
            $rules['company_logo']        = [
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['company_logo']=[
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['remarks']        = 'required';
            $rules['document_image'] =[
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['document_number']        = 'required';           
            

            if (isset($company) && ($company->getMedia('company_logo')->count()==0 || ($company->getMedia('company_logo')->count() >0 && !file_exists($company->getFirstMedia('company_logo')->getPath())))) {
                $rules['company_logo'] = [
                    'required',             
                    'file',
                    'image'
                ];
            }
            if (isset($company) && ($company->getMedia('document_image')->count()==0 || ($company->getMedia('document_image')->count() >0 && !file_exists($company->getFirstMedia('document_image')->getPath())))) {
                $rules['document_image'] = [
                    'required',             
                    'file',
                    'image'
                ];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();
            $data['password'] = Hash::make($request->password);
            $data['is_verify']=isset($request->is_verify)?$request->is_verify:0;   
            $data['is_active']=1;         
            $user = User::create($data);
            if ($request->hasFile('profile_picture')){
            $file = $request->file('profile_picture');
            $customname = time() . '.' . $file->getClientOriginalExtension();
            $user
               ->addMedia($request->file('profile_picture'))
               ->usingFileName($customname)               
               ->toMediaCollection('profile_picture');
            }
            $user_id=$user->id;
            $fundi_is_middlemen=isset($request->fundi_is_middlemen)?$request->fundi_is_middlemen:0;
            $fundi_have_tools=isset($request->fundi_have_tools)?$request->fundi_have_tools:0;
            $fundi_have_smartphone=isset($request->fundi_have_smartphone)?$request->fundi_have_smartphone:0;
            $security_check=isset($request->security_check)?$request->security_check:0;
            if(intval($user_id) > 0)
            {
                $profile_data=array('user_id'=>$user_id,'work_address'=>$request->address ,'latitude'=>$request->latitude,'longitude'=>$request->longitude,'experience_level_id'=>$request->experience_level,'facebook_url'=>$request->facebook_url,'twitter_url'=>$request->twitter_url,'instagram_url'=>$request->instagram_url,'fundi_is_middlemen'=>$fundi_is_middlemen,'fundi_have_tools'=>$fundi_have_tools,'fundi_have_smartphone'=>$fundi_have_smartphone,'security_check'=>$security_check);
                $user->profiles()->create($profile_data);
            }

            $role = Role::where('id',config('constants.ROLE_TYPE_PROVIDER_ID'))->first();
            if (isset($role->id)) {
                $user->assignRole($role);
            }
            $company_name=isset($request->company_name)?$request->company_name:'';
            $remarks=isset($request->remarks)?$request->remarks:'';
            $document_number=isset($request->document_number)?$request->document_number:'';
            $is_payment_received=isset($request->is_payment_received)?$request->is_payment_received:0;
            $company_data=array('user_id'=>$user_id,
                                'name'=>$company_name,
                                'remarks'=>$remarks,
                                'document_number'=>$document_number,
                                'is_payment_received'=>$is_payment_received,
                                'is_active'=>1);
            $company = Company::create($company_data);
            $degree_title=isset($request->degree_title)?$request->degree_title:'';
            $diploma_title=isset($request->diploma_title)?$request->diploma_title:'';
            $certification_title=isset($request->certification_title)?$request->certification_title:'';

            $certification=Certification::create(array('title'=>$certification_title,'type'=>'certification','user_id'=>$user_id));

            $degree=Certification::create(array('title'=>$degree_title,'type'=>'degree','user_id'=>$user_id));

            $diploma=Certification::create(array('title'=>$degree_title,'type'=>'diploma','user_id'=>$user_id));

            if ($request->hasFile('company_logo')){
                 $file = $request->file('company_logo');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $company->addMedia($request->file('company_logo'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('company_logo');
            } 
            
            if ($request->hasFile('certificate_conduct')){
                 $file = $request->file('certificate_conduct');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $user->addMedia($request->file('certificate_conduct'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('certificate_conduct');
            } 
            if ($request->hasFile('document_image')){
                 $file = $request->file('document_image');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $company->addMedia($request->file('document_image'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('document_image');
            } 
            //company end data
            if ($request->hasFile('certification')){
                 $file = $request->file('certification');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $certification->addMedia($request->file('certification'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('certification');
            } 
            if ($request->hasFile('degree')){
                 $file = $request->file('degree');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $degree->addMedia($request->file('degree'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('degree');
            } 
            if ($request->hasFile('diploma')){
                 $file = $request->file('diploma');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $diploma->addMedia($request->file('diploma'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('diploma');
            } 
            if ($request->hasFile('image')){
                 $files = $request->file('image');
                  $i=0;
                  foreach ($files as $file) {
                     $customname = time().$i. '.' . $file->getClientOriginalExtension();
                     $user->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('works_photo');
                       $i++;
               }
            }

            $request->session()->flash('success',__('global.messages.add'));
            return redirect()->route('admin.providers.index');
        }else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user){
        return redirect()->route('admin.providers.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id){        
        $user = User::with('profile','media')->findOrFail($id);
        $experience_levels=ExperienceLevel::get()->pluck('title', 'id')->map(function($value, $key){
            return ucwords($value);
        });
        $works_photo=$user->getMedia('works_photo');  
        $providerCompany=Company::query()->with('media')->where(['user_id'=>$id])->first();   
        $providerdegree=Certification::query()->with('media')->where(['user_id'=>$id,'type'=>'degree'])->first();  
        $providerdiploma=Certification::query()->with('media')->where(['user_id'=>$id,'type'=>'diploma'])->first(); 
        $providercertification=Certification::query()->with('media')->where(['user_id'=>$id,'type'=>'certification'])->first(); 
        $provider_review=Review::with(['user'])->where(array('user_id'=>$id))->get();
        $rating='';
        if(count($provider_review)>0)
        {
          $no_of_count=count($provider_review); 
          $provider_rating=$provider_review->sum('rating');
          $rating = $provider_rating / $no_of_count;
          $rating=(round($rating,2));
        }

        return view('admin.providers.edit',compact('user','providerCompany','experience_levels','providerdegree','works_photo','providerdiploma','providercertification','rating','provider_review'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id){
     

        $user = User::findOrFail($id);
        $media_max_size = config('medialibrary.max_file_size') / 1024; 
        $rules = [
            'name'              => 'required', 
            'email'             => 'required|email|unique:'.with(new User)->getTable().',email,'.$user->getKey(),
            'profile_picture'   => 'image',
            'mobile_number'     => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number,'.$user->getKey(),
            'experience_level'  => 'required',
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable'            
        ];
        $company=Company::where(['user_id'=>$id])->first(); 
        $certification_img=Certification::where(['user_id'=>$id,'type'=>'certification'])->first();
        $diploma_img=Certification::where(['user_id'=>$id,'type'=>'diploma'])->first();
        $degree_img=Certification::where(['user_id'=>$id,'type'=>'degree'])->first();
        
       
        if (isset($request->reset_password) && $request->reset_password==TRUE) {
            $rules['password'] = 'required|confirmed';
        }
        if (isset($request->security_check) && $request->security_check==TRUE) {
            //  $rules1 = [
            $rules['company_name']        = 'required';
            $rules['company_logo']        = [
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['company_logo']=[
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['remarks']        = 'required';
            $rules['document_image'] =[
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ];
            $rules['document_number']        = 'required';           
            

            if (isset($company) && ($company->getMedia('company_logo')->count()==0 || ($company->getMedia('company_logo')->count() >0 && !file_exists($company->getFirstMedia('company_logo')->getPath())))) {
                $rules['company_logo'] = [
                    'required',             
                    'file',
                    'image'
                ];
            }
            if (isset($company) && ($company->getMedia('document_image')->count()==0 || ($company->getMedia('document_image')->count() >0 && !file_exists($company->getFirstMedia('document_image')->getPath())))) {
                $rules['document_image'] = [
                    'required',             
                    'file',
                    'image'
                ];
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();

            if (isset($request->reset_password) && $request->reset_password==TRUE) {
                $data['password'] = Hash::make($request->password);
            }else{
                unset($data['password']);
            }
            $data['is_verify']=isset($request->is_verify)?$request->is_verify:0;
            $user->update($data);
            if($data['is_verify']==true)
            {
                $device_token=array();
                if($user->device_type==config('constants.DEVICE_TYPE_IOS'))
                {
                    $device_token[]=$user->device_token;
                }else
                {
                    $device_token[]=$user->device_token;
                }
                if(!empty($device_token))
                {                   
                    $title=config('constants.NOTIFICATION_VERIFY_ACCOUNT_SUBJECT');
                    $message=config('constants.NOTIFICATION_VERIFY_ACCOUNT_MESSAGE');
                    $token=$device_token;
                    sendIphoneNotifications($title,$message,$token);
                } 
            }
            if ($request->hasFile('profile_picture')){
             $file = $request->file('profile_picture');
             $customname = time() . '.' . $file->getClientOriginalExtension();
             $user->addMedia($request->file('profile_picture'))
               ->usingFileName($customname)               
               ->toMediaCollection('profile_picture');
            } 
            $user_id=$user->id;
            $profile = Profile::where(array('user_id'=>$user_id));
            $fundi_is_middlemen=isset($request->fundi_is_middlemen)?$request->fundi_is_middlemen:0;
            $fundi_have_tools=isset($request->fundi_have_tools)?$request->fundi_have_tools:0;
            $fundi_have_smartphone=isset($request->fundi_have_smartphone)?$request->fundi_have_smartphone:0;
            $security_check=isset($request->security_check)?$request->security_check:0;
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$request->address ,'latitude'=>$request->latitude,'longitude'=>$request->longitude,'experience_level_id'=>$request->experience_level,'facebook_url'=>$request->facebook_url,'twitter_url'=>$request->twitter_url,'instagram_url'=>$request->instagram_url,'fundi_is_middlemen'=>$fundi_is_middlemen,'fundi_have_tools'=>$fundi_have_tools,'fundi_have_smartphone'=>$fundi_have_smartphone,'security_check'=>$security_check);
                $profile->update($profile_data);
            }
            //company data
            $company_name=isset($request->company_name)?$request->company_name:'';
            $remarks=isset($request->remarks)?$request->remarks:'';
            $document_number=isset($request->document_number)?$request->document_number:'';
            $is_payment_received=isset($request->is_payment_received)?$request->is_payment_received:0;
            $company_data=array('user_id'=>$user_id,
                                'name'=>$company_name,
                                'remarks'=>$remarks,
                                'document_number'=>$document_number,
                                'is_payment_received'=>$is_payment_received,
                                'is_active'=>1);
            if($company)
            {
                 $company->update($company_data);
                
            }else
            {
                 $company = Company::create($company_data);
            }
            $degree_title=isset($request->degree_title)?$request->degree_title:'';
            $diploma_title=isset($request->diploma_title)?$request->diploma_title:'';
            $certification_title=isset($request->certification_title)?$request->certification_title:'';
            
            if($certification_img)
              {
                $certification_data=array('title'=>$certification_title);
                $certification_img->update($certification_data);
               
              }else
              {
                 $certification_img=Certification::create(array('title'=>$certification_title,'type'=>'certification','user_id'=>$id));
              }
              if($degree_img)
              {
                $degree_data=array('title'=>$degree_title);
                $degree_img->update($degree_data);
               
              }else
              {
                 $degree_img=Certification::create(array('title'=>$degree_title,'type'=>'degree','user_id'=>$id));
              }
              if($diploma_img)
              {
                $diploma_data=array('title'=>$diploma_title);
                $diploma_img->update($diploma_data);
               
              }else
              {
                 $diploma_img=Certification::create(array('title'=>$degree_title,'type'=>'diploma','user_id'=>$id));
              }
            
            
            //$degree_img->update($degree_data);
            
           // $diploma_img->update($diploma_data);


            if ($request->hasFile('company_logo')){
                 $file = $request->file('company_logo');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $company->addMedia($request->file('company_logo'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('company_logo');
            } 
            
            if ($request->hasFile('certificate_conduct')){
                 $file = $request->file('certificate_conduct');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $user->addMedia($request->file('certificate_conduct'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('certificate_conduct');
            } 
            if ($request->hasFile('document_image')){
                 $file = $request->file('document_image');
                 $customname = time() . '.' . $file->getClientOriginalExtension();
                 $company->addMedia($request->file('document_image'))
                   ->usingFileName($customname)               
                   ->toMediaCollection('document_image');
            } 
            //company end data
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
            if ($request->hasFile('image')){
                 $files = $request->file('image');
                  $i=0;
                  foreach ($files as $file) {
                     $customname = time().$i. '.' . $file->getClientOriginalExtension();
                     $user->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('works_photo');
                       $i++;
               }
            }

            $request->session()->flash('success',__('global.messages.update'));
            return redirect()->route('admin.providers.index');
        }else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Change status the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function status($id=null){
      $user = User::findOrFail($id);
      if (isset($user->is_active) && $user->is_active==FALSE) {
          $user->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $user->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.providers.index');
    }
    /**
     * Change payment received the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function company_status($status=null,$id=null,$user_id=null){        
      $company = Company::where(['id'=>$id,'user_id'=>$user_id])->first();      
      if (isset($company->is_active) && $company->is_active==FALSE) {
          $company->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $company->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      $user = User::with('profile','profile.experience_level','profile.payment_option','package_user','hourly_charge','company')->findOrFail($user_id);  
      return redirect()->route('admin.providers.view',$user_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){ 
      $user = User::findOrFail($id);   
      Transaction::where('user_id',$id)->delete();
      Schedule::where('user_id',$id)->delete();    
      Booking::where('user_id',$id)->delete();  
      $user->delete();
      session()->flash('danger',__('global.messages.delete'));
      return redirect()->route('admin.providers.index');
    }
    /**
     * Show the form for view the specified resource.
     *
     * @param  \App\User $user
     * @return \Illuminate\Http\Response
     */
    public function view($id){        
        $user = User::with('profile','profile.experience_level','profile.payment_option','package_user','hourly_charge','company')->findOrFail($id); 
        $works_photo=$user->getMedia('works_photo');  
        $providerCompanies=Company::query()->with('media')->where(['user_id'=>$id])->get();
        $providerCertifications=Certification::query()->with('media')->where(['user_id'=>$id])->get();       
        return view('admin.providers.view',compact('user','id','providerCompanies','providerCertifications','works_photo'));
    }

    
}
