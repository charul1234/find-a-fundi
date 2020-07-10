<?php

namespace App\Http\Controllers\Admin;

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
            ->rawColumns(['media.name','is_active','action'])
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
        return view('admin.providers.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $rules = [
           /* 'role_id'           => 'required', */
            'name'              => 'required', 
            'email'             => 'required|email|unique:'.with(new User)->getTable().',email',
            'profile_picture'   => 'image',
            'password'          => 'required|confirmed',
            'mobile_number'     => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number',
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();
            $data['password'] = Hash::make($request->password);
                        
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
            if(intval($user_id) > 0)
            {
                $profile_data=array('user_id'=>$user_id,'work_address'=>$request->address ,'latitude'=>$request->latitude,'longitude'=>$request->longitude);
                Profile::create($profile_data);
            }

            $role = Role::where('id',config('constants.ROLE_TYPE_PROVIDER_ID'))->first();
            if (isset($role->id)) {
                $user->assignRole($role);
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
        $user = User::with('profile')->findOrFail($id);        
        return view('admin.providers.edit',compact('user'));
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
        $rules = [
            'name'              => 'required', 
            'email'             => 'required|email|unique:'.with(new User)->getTable().',email,'.$user->getKey(),
            'profile_picture'   => 'image',
            'mobile_number'     => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number,'.$user->getKey(),
            'address'           => 'required',
            'latitude'          => 'nullable',
            'longitude'         => 'nullable',
        ];
       
        if (isset($request->reset_password) && $request->reset_password==TRUE) {
            $rules['password'] = 'required|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();

            if (isset($request->reset_password) && $request->reset_password==TRUE) {
                $data['password'] = Hash::make($request->password);
            }else{
                unset($data['password']);
            }

            $user->update($data);
            if ($request->hasFile('profile_picture')){
             $file = $request->file('profile_picture');
             $customname = time() . '.' . $file->getClientOriginalExtension();
             $user->addMedia($request->file('profile_picture'))
               ->usingFileName($customname)               
               ->toMediaCollection('profile_picture');
            } 
            $user_id=$user->id;
            $profile = Profile::where(array('user_id'=>$user_id));
            if(intval($user_id) > 0)
            {
                $profile_data=array('work_address'=>$request->address ,'latitude'=>$request->latitude,'longitude'=>$request->longitude);
                $profile->update($profile_data);
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
        $providerCompanies=Company::query()->with('media')->where(['user_id'=>$id])->get();
        $providerCertifications=Certification::query()->with('media')->where(['user_id'=>$id])->get();       
        return view('admin.providers.view',compact('user','id','providerCompanies','providerCertifications'));
    }

    
}
