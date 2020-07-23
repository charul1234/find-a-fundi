<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Validator;
use Auth;
use File;

use App\Notifications\SendOTP;
use App\User;
use App\OtpUser;
use App\PasswordReset;
use App\Profile;
use App\CategoryUser;

class AuthController extends Controller
{
    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function index(Request $request){ 
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:'.with(new User)->getTable().',email',
            'mobile_number' => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number',
            'country_id' => 'required', 
            'city_id' => 'required', 
            'address' => 'required', 
            'latitude' => 'required', 
            'longitude' => 'required', 
            'otp' => 'required',  
            'profile_picture' => 'image|mimes:jpeg,jpg,png|max:'.config('medialibrary.max_file_size') / 1024,  
            'password' => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }
        $otpuser = OtpUser::where(['otp'=>$request->otp,'mobile_number'=>$request->mobile_number])->first();
        if (!$otpuser) {
            return response()->json(['status'=>FALSE, 'message'=>'OTP is incorrect.']);
        }
        $device_token=isset($request->device_token)?$request->device_token:'';
        $device_type=isset($request->device_type)?$request->device_type:'';
        $input = ['name'=>$request->name, 'email'=>$request->email, 'mobile_number'=>$request->mobile_number, 'is_active'=>TRUE,'device_token'=>$device_token,'device_type'=>$device_type];
        $input['password'] = bcrypt($request->password);
        $user = User::create($input); 
        if($user){
            $user->assignRole(config('constants.ROLE_TYPE_SEEKER_ID'));

            //upload profile picture
            if ($request->hasFile('profile_picture')){
                $file = $request->file('profile_picture');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('profile_picture');
            }

            $user->profiles()->create(['city_id'=>$request->city_id, 'work_address'=>$request->address, 'latitude'=>$request->latitude, 'longitude'=>$request->longitude, 'display_seeker_reviews'=>(isset($request->display_seeker_reviews) && $request->display_seeker_reviews == TRUE)?TRUE:FALSE]);
            
            // For store access token of user
            $tokenResult = $user->createToken('Login Token');
            $token = $tokenResult->token;

            $response['status'] = TRUE; 
            $response['message'] = "You has been successfully registered.";
            $response['user'] = $user->getUserDetail();
            $response['access_token'] = $tokenResult->accessToken;
            $response['token_type'] = 'Bearer';
            $response['expires_at'] = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();
            return response()->json($response);
        }else{
            return response()->json(['status'=>FALSE, 'message'=>'Something wrong in registration.']);
        }
    }

    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function sendOTP(Request $request){ 
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number'
        ]);

        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }
        $input = array_map('trim', $request->all());
        $input['otp'] = rand(100000,999999);
        $userotp = OtpUser::updateOrCreate(['mobile_number'=>$request->mobile_number],$input); 
        if($userotp){
            $response['status'] = TRUE; 
            $response['otp'] = $input['otp']; 
            $response['message'] = "You OTP has been sent successfully.";
            return response()->json($response);
        }else{
            return response()->json(['status'=>FALSE, 'message'=>'Something wrong in registration.']);
        }
    }

    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 

    public function login(Request $request){        
        $rules =   ['email' => 'required', 
                    'password' => 'required',
                    'role_id' => 'required'];
                    
        $messages = [];
        $validator = Validator::make($request->all(), $rules, $messages)->setAttributeNames(['email'=>'email or mobile number']);        
        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);    
        }       

        $email = $request->input('email');
        $fieldType = filter_var($email, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile_number';
        $credentials= [$fieldType => $email, 'password'=>$request->get('password')];

        if(Auth::attempt($credentials)){
            $user = Auth::user();

            if(!$user->hasRole((Integer)$request->role_id))
                return response()->json(['status'=>FALSE, 'message'=>trans('auth.failed')]);

            if($user->is_active==false){
                return response()->json(['status'=>FALSE, 'message'=>trans('auth.noactive')]);
            }
            if($user){ 
                $userdata=array('device_token'=>$request->device_token,
                              'device_type'=>$request->device_type);                
                $user->update($userdata);
            }
            $message='';
            if($user->is_verify==false)
            {
                if($user->screen_name==config('constants.SCREEN_NAME1') || $user->screen_name=='')
                {
                    $message='Logged in successfully. please complete your profile.';
                }
                if($user->screen_name==config('constants.SCREEN_NAME2'))
                {
                    $message='Logged in successfully. your account under review, admin will notify you.';
                }                
            }else
            {
                $message='Logged in successfully.';
            }
            // For store access token of user
            $tokenResult = $user->createToken('Login Token');
            $token = $tokenResult->token;

            $response['status'] = TRUE; 
            $response['message'] = $message;
            $response['user'] = $user->getUserDetail();
            $response['access_token'] = $tokenResult->accessToken;
            $response['token_type'] = 'Bearer';
            $response['expires_at'] = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();
            return response()->json($response); 
        }else{
            return response()->json(['status'=>FALSE, 'message'=>trans('auth.failed')]); 
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request){
        $request->user()->token()->revoke();
        $response['status'] = TRUE;  
        $response['message'] = "Successfully logged out";
        return response()->json($response);
    }

    /** 
     * forgotPassword api 
     * 
     * @return \Illuminate\Http\Response 
    */ 

    public function forgotPassword(Request $request){        
        $rules =   ['email' => 'required'];
        $credentials= ['email' => $request->get('email')];
        
        $messages = [
            'email.required' => "The email field is required.",
        ];
        $validator = Validator::make($request->all(), $rules,$messages);
        
        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);    
        }

        $user=User::where($credentials)->first();

        if($user){
            PasswordReset::where('email', $user->email)->delete();
            $code = rand(100000,999999);
            $passwordReset = PasswordReset::create(
                [
                    'email' => $user->email,
                    'token' => $code
                ]
            );

            if ($user && $passwordReset)
                $user->notify(
                    new SendOTP('forget_password', $passwordReset->token)
                );
            
            $response['status'] = TRUE; 
            $response['response'] = array('code'=>$code);
            $response['message'] = "We have sent a verification code on your email";
            return response()->json($response); 
        }else{
            return response()->json(['status'=>FALSE, 'message'=>'Account details not found.']);
        }
    }

    /** 
     * resetPassword api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'token' => 'required', 
            'password' => 'required'
        ]);
        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user)
            return response()->json([
                'status'=>FALSE, 
                'message' => 'We can\'t find a user with that e-mail address.'
            ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset)
            return response()->json([
                'status'=>FALSE, 
                'message' => 'This password reset token is invalid.'
            ]);

        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->where('email', $passwordReset->email)->delete();

        $response['status'] = TRUE;
        $response['message'] = "Update Password successfully.";
        return response()->json($response); 
    }

    /**
     * update user
     *
     * @return [string] message
     */
    public function updateProfile(Request $request){
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:'.with(new User)->getTable().',email,'.$user->getKey(),
            'profile_picture' => 'image'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);
        }

        $data = $request->all();
        $user->update($data);

        $response['status'] = TRUE;  
        $response['user'] = $user->getUserDetail();
        $response['message'] = "Profile updated Successfully.";
        return response()->json($response);
    }
    /** 
     * Provider Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function providerRegistration(Request $request){ 
         
        $validator = Validator::make($request->all(), [ 
            'name' => 'required', 
            'email' => 'required|email|unique:'.with(new User)->getTable().',email',
            'mobile_number' => 'required|numeric|unique:'.with(new User)->getTable().',mobile_number',   
            'category_id' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }        
        $device_token=isset($request->device_token)?$request->device_token:'';
        $device_type=isset($request->device_type)?$request->device_type:'';
        $input = ['name'=>$request->name, 'email'=>$request->email, 'mobile_number'=>$request->mobile_number, 'is_active'=>true,'device_token'=>$device_token,'device_type'=>$device_type];
        $input['password'] = bcrypt($request->password);


        $user = User::create($input); 
        if($user){
             //upload profile picture
            if ($request->hasFile('profile_picture')){
                $file = $request->file('profile_picture');
                $customimagename  = time() . '.' . $file->getClientOriginalExtension();
                $user->addMedia($file)->toMediaCollection('profile_picture');
            }
            $user->assignRole(config('constants.ROLE_TYPE_PROVIDER_ID'));
           
            $user_id=$user->id;
            if(intval($user_id) > 0)
            {
                $profile_data=array('user_id'=>$user_id);
                $user->profiles()->create($profile_data);
            }
            $category_id=$request->category_id;
            
            if(intval($category_id) > 0)
            {
               $user->category_user()->create(['user_id'=>$user_id,'category_id'=>$category_id]);
            }      
            $subcategory_ids=$request->subcategory_id;   
            $subcategory_ids=explode(',',$subcategory_ids);         
            if(count($subcategory_ids)>0)
            {
                  foreach ($subcategory_ids as $key => $subcategory_id) 
                  {                     
                     $user->category_user()->create(['user_id'=>$user_id,'category_id'=>$subcategory_id]);   
                  }                              
            }
               
            // For store access token of user
            $tokenResult = $user->createToken('Login Token');
            $token = $tokenResult->token;

            $response['status'] = TRUE; 
            $response['message'] = "You has been successfully registered. Wait until admin verify your registration";
            $response['user'] = $user->getUserDetail();
            $response['access_token'] = $tokenResult->accessToken;
            $response['token_type'] = 'Bearer';
            $response['expires_at'] = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();
            return response()->json($response);
        }else{
            return response()->json(['status'=>FALSE, 'message'=>'Something wrong in registration.']);
        }
    }
    /** 
     * mobileVerify api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function mobileVerify(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required',
            'token' => 'required'
        ]);
        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }
        
        if($user)
        {
            $otpuser = OtpUser::where(['otp'=>$request->token,'mobile_number'=>$request->mobile_number])->first();            
            if($otpuser)
            {
                $user_id=$user->id;
                if($user_id){ 
                    $userdata=array('is_mobile_verify'=>true);  
                    $user->update($userdata);
                }
                $response=array('status'=>true,'message'=>'User verified.');
            }else
            {
                $response=array('status'=>false,'message'=>'OTP is incorrect.');
            }           
            
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response);           
    }
    /** 
     * Provider send OTP api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function sendProviderOTP(Request $request){ 
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|numeric'
        ]);

        if ($validator->fails()) { 
            return response()->json(['status'=>FALSE, 'message'=>$validator->errors()->first()]);            
        }
        $user = User::where('mobile_number', $request->input('mobile_number'))->first();
        if($user)
        {

            $input = array_map('trim', $request->all());
            $input['otp'] = rand(100000,999999);
            $userotp = OtpUser::updateOrCreate(['mobile_number'=>$request->mobile_number],$input); 
            if($userotp){
                $response['status'] = TRUE; 
                $response['otp'] = $input['otp']; 
                $response['message'] = "You OTP has been sent successfully.";
            }
        }else
        {
            $response=array('status'=>false,'message'=>'Oops! Invalid credential.');
        }        
        return response()->json($response); 
    }
}
