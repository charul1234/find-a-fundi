@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Providers</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
{!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.providers.store'],'class' => 'form-horizontal','id' => 'frmUser']) !!}
        
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add Provider</h6>
        </div>
        <div class="card-body">
        <div class="col-md-12 form-group">
                  <label for="is_verify">{{ Form::checkbox('is_verify', '1', old('is_verify'),['id'=>'is_verify']) }}</label>
                  <label for="is_verify"> Verify Provider Account </label>
                   
        </div>
          <div class="card card-information">
            <div class="alert alert-secondary col-md-12" role="alert"><div class="row"><div class="col-md-6">      <label for="is_personal_verified">{{ Form::checkbox('is_personal_verified', '1', old('is_personal_verified'),['id'=>'is_personal_verified']) }}</label>
                        <label for="is_default">Personal Information Verified </label></div><div class="col-md-6"> <div class="text-left font-weight-bold">Personal Information</div></div></div>
</div>
         <!--  <div class="card-header text-center">Personal Information</div> -->
            <div class="card-body"><!-- <h5 class="card-title ml-2">Personal Information</h5> -->
<div class="row">
    <div class="col-md-5">
 <div class="form-group {{$errors->has('name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="name">Name <span style="color:red">*</span></label>
                 <div class="col-md-10">
                    {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('name'))
                    <strong for="name" class="help-block">{{ $errors->first('name') }}</strong>
                    @endif
                </div>
            </div>
             <div class="form-group {{$errors->has('mobile_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="mobile_number">Mobile Number <span style="color:red">*</span></label>
                <div class="col-md-10">
                    {!! Form::text('mobile_number',old('mobile_number'), ['class' => 'form-control', 'placeholder' => 'Mobile Number']) !!}
                    @if($errors->has('mobile_number'))
                    <strong for="mobile_number" class="help-block">{{ $errors->first('mobile_number') }}</strong>
                    @endif
                </div>
            </div>
             <div class="form-group {{$errors->has('email') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="email">Email <!-- <span style="color:red">*</span> --></label>
                <div class="col-md-10">
                    {!! Form::text('email',old('email'), ['class' => 'form-control autoFillOff', 'placeholder' => 'Email']) !!}
                    @if($errors->has('email'))
                    <strong for="email" class="help-block">{{ $errors->first('email') }}</strong>
                    @endif
                </div>
            </div>

           <!--  <div class="form-group">
                 <div class="col-md-9">
                    <label>
                        {{Form::checkbox('reset_password', TRUE, null,['id'=>'reset_password'])}}
                        {{ __('Reset Password') }}
                    </label>
                </div>
            </div> -->

            <div  id="password_container">
                <div class="form-group {{$errors->has('password') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                    <label class="col-md-10 control-label" for="password">New Password <span style="color:red">*</span></label>
                    <div class="col-md-10">
                        {!! Form::password('password',['class' => 'form-control autoFillOff', 'placeholder' => 'New Password', 'id'=>'password']) !!}
                        @if($errors->has('password'))
                        <strong for="password" class="help-block">{{ $errors->first('password') }}</strong>
                        @endif
                    </div>
                </div>
                <div class="form-group {{$errors->has('password_confirmation') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                    <label class="col-md-10 control-label" for="password_confirmation">Confirm Password <span style="color:red">*</span></label>
                    <div class="col-md-10">
                        {!! Form::password('password_confirmation', ['class' => 'form-control autoFillOff', 'placeholder' => 'Confirm Password']) !!}
                        @if($errors->has('password_confirmation'))
                        <strong for="password_confirmation" class="help-block">{{ $errors->first('password_confirmation') }}</strong>
                        @endif
                    </div>
                </div>

            </div>
             <div class="form-group {{$errors->has('address_line_1') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="address_line_1">Address line 1 </label>
                <div class="col-md-10">
                    {!! Form::textarea('address_line_1',old('address_line_1'), ['class' => 'form-control', 'placeholder' => 'Address line 1','rows'=>'1','id' =>'address_line_1' ]) !!}
                    @if($errors->has('address_line_1'))
                    <strong for="address_line_1" class="help-block">{{ $errors->first('address_line_1') }}</strong>
                    @endif
                </div>
            </div>
      

   
          
    </div>   
    <div class="col-md-7">
        <div class="row">
            <div class="col-md-7"> 

            </div>
            <div class="col-md-5">   <div class="form-group {{$errors->has('profile_picture') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="title">Profile Picture </label>
                <div class="col-md-12">
                     {{ Form::file('profile_picture') }}
                    @if($errors->has('profile_picture'))
                    <strong for="profile_picture" class="help-block">{{ $errors->first('profile_picture') }}</strong>
                    @endif
                </div>
            </div>

            </div>
        </div> 
    </div>  
</div> 

<div class="row">
    <div class="col-md-5">

           
            <div class="form-group {{$errors->has('address') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="address">Address <span style="color:red">*</span></label>
                <div class="col-md-10">
                    {!! Form::textarea('address',old('address'), ['class' => 'form-control', 'placeholder' => 'Address','rows'=>'1','id' =>'address' ]) !!}
                    @if($errors->has('address'))
                    <strong for="address" class="help-block">{{ $errors->first('address') }}</strong>
                    @endif
                </div>
            </div>
                       <div class="form-group {{$errors->has('zip_code') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="zip_code">Zipcode </label>
                <div class="col-md-10">
                    {!! Form::text('zip_code', old('zip_code'), ['class' => 'form-control', 'placeholder' => 'Zip code']) !!}
                    @if($errors->has('zip_code'))
                    <strong for="zip_code" class="help-block">{{ $errors->first('zip_code') }}</strong>
                    @endif
                </div>
            </div>
              <div class="form-group {{$errors->has('personal_admin_remarks') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-10 control-label" for="personal_admin_remarks">Admin Remarks </label>
                <div class="col-md-10">
                    {!! Form::textarea('personal_admin_remarks',old('personal_admin_remarks'), ['class' => 'form-control', 'placeholder' => 'Admin Remarks','rows'=>'1','id' =>'personal_admin_remarks' ]) !!}
                    @if($errors->has('personal_admin_remarks'))
                    <strong for="personal_admin_remarks" class="help-block">{{ $errors->first('personal_admin_remarks') }}</strong>
                    @endif
                </div>
            </div>

    </div>   
    <div class="col-md-7">
        <div class="row">
            <div class="col-md-7"> 

<div class="row">
<div class="col-md-12">
 <div class="form-group {{$errors->has('latitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="latitude">Latitude </label>
                <div class="col-md-12">
                    {!! Form::text('latitude',old('latitude'), ['class' => 'form-control', 'placeholder' => 'Latitude','id' =>'latitude' ]) !!}
                    @if($errors->has('latitude'))
                    <strong for="latitude" class="help-block">{{ $errors->first('latitude') }}</strong>
                    @endif
                </div>
            </div>
</div>
<div class="col-md-12">
 <div class="form-group {{$errors->has('longitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="longitude">Longitude </label>
                <div class="col-md-12">
                    {!! Form::text('longitude',old('longitude'), ['class' => 'form-control', 'placeholder' => 'Longitude','id' =>'longitude']) !!}
                    @if($errors->has('longitude'))
                    <strong for="longitude" class="help-block">{{ $errors->first('longitude') }}</strong>
                    @endif
                </div>
            </div>
</div>
<div class="col-md-12">
 <div class="form-group {{$errors->has('personal_admin_rating') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="personal_admin_rating">Admin Rating </label>
                <div class="col-md-12">
                  
                    {!! Form::select('personal_admin_rating', $adminRating, old('personal_admin_rating'), ['id'=>'personal_admin_rating', 'class' => 'form-control', 'placeholder' => 'Admin Rating (1 to 5)']) !!}
                    @if($errors->has('personal_admin_rating'))
                    <strong for="personal_admin_rating" class="help-block">{{ $errors->first('personal_admin_rating') }}</strong>
                    @endif
                </div>
            </div>
</div>
</div>

            </div>
            <div class="col-md-5"> 

            </div>
        </div> 
    </div>  
</div> 



           
 

            
          
        </div> 
          </div>
            
             <div class="card mt-3 card-information-technical"> 

      <div class="alert alert-secondary col-md-12" role="alert"><div class="row"><div class="col-md-6">      <label for="is_technical_verified">{{ Form::checkbox('is_technical_verified', '1', old('is_technical_verified',isset($user->profile->is_technical_verified)?$user->profile->is_technical_verified:0),['id'=>'is_technical_verified']) }}</label>
                        <label for="is_default">Technical Information Verified </label></div><div class="col-md-6"> <div class="text-left font-weight-bold">Technical Information</div></div></div>
</div>


    <div class="card-body border mb-2 border-primary">

<!--  -->


      <div class="row">
 <div class="form-group col-md-3 ml-3 ">
    <h6 class="m-0 font-weight-bold text-primary">Evidence of Expertise</h6>

 </div>
  <div class="form-group col-md-6 row ">
<div class="col-md-2 mr-2 mt-2 "><strong><?php //echo isset($rating)?$rating:'';?></strong></div>
<div class="col-md-3 mr-3">
     
     </div>


 </div>
</div>
   
                 <div class="col-md-9 mb-3">
<div class="form-group {{$errors->has('image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="image">Works photo </label>
                <div class="col-md-9">
                  {{ Form::file('image[]', array('multiple'=>true,'accept'=>'image/*'))   }}
                    @if($errors->has('image'))
                    <p class="help-block">
                        <strong>{{ $errors->first('image') }}</strong>
                    </p>
                    @endif
                </div>
            </div>
                 </div> 
 
 <div class="row">
<div class="col-md-3"><div class="form-group {{$errors->has('facebook_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="facebook_url">Facebook url </label>
                <div class="col-md-12">
                    {!! Form::text('facebook_url',old('facebook_url'), ['class' => 'form-control', 'placeholder' => 'Facebook url ']) !!}
                    @if($errors->has('facebook_url'))
                    <strong for="facebook_url" class="help-block">{{ $errors->first('facebook_url') }}</strong>
                    @endif
                </div>
            </div></div>
<div class="col-md-3"> <div class="form-group {{$errors->has('instagram_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="instagram_url">Instagram url </label>
                <div class="col-md-12">
                    {!! Form::text('instagram_url',old('instagram_url'), ['class' => 'form-control', 'placeholder' => 'Instagram url']) !!}
                    @if($errors->has('instagram_url'))
                    <strong for="instagram_url" class="help-block">{{ $errors->first('instagram_url') }}</strong>
                    @endif
                </div>
            </div></div>
<div class="col-md-3"> <div class="form-group {{$errors->has('twitter_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="twitter_url">Twitter url </label>
                <div class="col-md-12">
                    {!! Form::text('twitter_url',old('twitter_url'), ['class' => 'form-control', 'placeholder' => 'Twitter url']) !!}
                    @if($errors->has('twitter_url'))
                    <strong for="twitter_url" class="help-block">{{ $errors->first('twitter_url') }}</strong>
                    @endif
                </div> 
            </div></div>



 </div>
<div class="row">
<div class="col-md-3">
        <div class="col-md-12 form-group">
      <label for="is_academy_trained">{{ Form::checkbox('is_academy_trained', '1', old('is_academy_trained'),['id'=>'is_academy_trained']) }}</label>
                        <label for="is_default">Academy Trained </label>
                   
                      
            </div>
</div>
<div class="col-md-6">
    <div class="form-group {{$errors->has('experience_level') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-6 control-label" for="section">Year of Experience <span style="color:red">*</span></label>
                 <div class="col-md-6"> 
                    {!! Form::select('experience_level', $experience_levels, old('experience_level'), ['id'=>'experience_level', 'class' => 'form-control', 'placeholder' => 'Year of Experience']) !!}
                    @if($errors->has('experience_level'))
                    <p class="help-block">
                        <strong>{{ $errors->first('experience_level') }}</strong>
                    </p>
                    @endif
                </div>


            </div> 
</div>
</div>
        

           
            
    </div>
  <div class="card-body border border-primary">
   
      <div class="row">
<div class="col-md-4">
       <div class="form-group col-md-12 ml-3 ">
             <h6 class="m-0 font-weight-bold text-primary">Declaration</h6>
             </div>
              <div class="">
 
    <div class="card-body">
  <div class="col-md-12 form-group">
      <label for="fundi_is_middlemen">{{ Form::checkbox('fundi_is_middlemen', '1', old('fundi_is_middlemen'),['id'=>'fundi_is_middlemen']) }}</label>
                        <label for="is_default">I am not middlemen. </label>
                   
                      
            </div>
              <div class="col-md-12 form-group">
      <label for="fundi_have_tools">{{ Form::checkbox('fundi_have_tools', '1', old('fundi_have_tools'),['id'=>'fundi_have_tools']) }}</label>
                        <label for="is_default">I have all the required tools to do their job.  </label>
            </div>
               <div class="col-md-12 form-group">
      <label for="fundi_have_tools">{{ Form::checkbox('fundi_have_smartphone', '1', old('fundi_have_smartphone'),['id'=>'fundi_have_smartphone']) }}</label>
                        <label for="is_default">I have a smartphone.  </label>
            </div>
    </div>
  </div>
</div>
<div class="col-md-8">
          <div class=" ">
              <h6 class="ml-4 font-weight-bold text-primary">Certificate</h6>
   <!--  <div class="card-header"></div> -->
    <div class="card-body">
       <div class="row">
   <div class="col-md-6">
      <div class="form-group {{$errors->has('degree_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="degree_title">Degree </label>
                <div class="col-md-9">
                    {!! Form::text('degree_title',old('degree_title'), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('degree_title'))
                    <strong for="document_number" class="help-block">{{ $errors->first('degree_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
   <div class="col-md-6 row">
     <div class="col-md-8">
           {{ Form::file('degree') }}
</div>
     <div class="col-md-4">
                    @if($errors->has('degree'))
                    <strong for="degree" class="help-block">{{ $errors->first('degree') }}</strong>
                    @endif                  
                  
                    </div>
   </div>
 <div class="col-md-6">
      <div class="form-group {{$errors->has('diploma_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="diploma_title">Diploma</label>
                <div class="col-md-9">
                    {!! Form::text('diploma_title',old('diploma_title'), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('diploma_title'))
                    <strong for="diploma_title" class="help-block">{{ $errors->first('diploma_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
    <div class="col-md-6 row">
       <div class="col-md-8">
           {{ Form::file('diploma') }}
           </div>
     <div class="col-md-4">
                    @if($errors->has('diploma'))
                    <strong for="diploma" class="help-block">{{ $errors->first('diploma') }}</strong>
                    @endif  
                    </div>
   </div>
    <div class="col-md-6">
      <div class="form-group {{$errors->has('certification_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="certification_title">Certification</label>
                <div class="col-md-9">
                    {!! Form::text('certification_title',old('certification_title'), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('certification_title'))
                    <strong for="certification_title" class="help-block">{{ $errors->first('certification_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
      <div class="col-md-6 row">
          <div class="col-md-8">
           {{ Form::file('certification') }}
           </div>
     <div class="col-md-4">
                    @if($errors->has('certification'))
                    <strong for="diploma" class="help-block">{{ $errors->first('certification') }}</strong>
                    @endif                  
                  
                    </div>
   </div>

      <div class="col-md-6">
 <div class="form-group {{$errors->has('certificate_conduct') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
        <label class="col-md-9 control-label" for="title">Upload Certificate of conduct </label>
        <div class="col-md-9">
             {{ Form::file('certificate_conduct') }}
            @if($errors->has('certificate_conduct'))
            <strong for="profile_picture" class="help-block">{{ $errors->first('certificate_conduct') }}</strong>
            @endif
        </div>
    </div>
</div>

       </div>
       </div>
    </div>
</div>


      
      </div>
    </div>  


 </div>
           
 
      <div class="card mt-3">
    <div class="card-header">  {{Form::checkbox('security_check',1,old('security_check'), ['id'=>'security_check'])}}
                        {{ __('Security check') }} </div>
    <div class="card-body">
<div  id="security_container">
<div class="form-group {{$errors->has('company_name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <?php  $company_name=isset($providerCompany->name)?$providerCompany->name:''; 
                       $remarks=isset($providerCompany->remarks)?$providerCompany->remarks:'';
                       $document_number=isset($providerCompany->document_number)?$providerCompany->document_number:'';
                       $is_payment_received=isset($providerCompany->is_payment_received)?$providerCompany->is_payment_received:'';?>
                
                <label class="col-md-3 control-label" for="company_name">Company Name <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('company_name',old('company_name'), ['class' => 'form-control', 'placeholder' => 'Company Name']) !!}
                    @if($errors->has('company_name'))
                    <strong for="company_name" class="help-block">{{ $errors->first('company_name') }}</strong>
                    @endif
                </div>               
            </div>
<div class="form-group {{$errors->has('company_logo') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="company_logo">Upload logo<span style="color:red">*</span></label>
                <div class="col-md-9">
                    {{ Form::file('company_logo') }}
                    @if($errors->has('company_logo'))
                    <strong for="company_logo" class="help-block">{{ $errors->first('company_logo') }}</strong>
                    @endif
                  
                </div>               
            </div>
 <div class="form-group {{$errors->has('document_image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="document_image">Upload Document<span style="color:red">*</span></label>
                <div class="col-md-9">
                    {{ Form::file('document_image') }}
                    @if($errors->has('document_image'))
                    <strong for="document_image" class="help-block">{{ $errors->first('document_image') }}</strong>
                    @endif
                   
                </div>               
            </div>
            <div class="form-group {{$errors->has('document_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="document_number">Document Number <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('document_number',old('document_number'), ['class' => 'form-control', 'placeholder' => 'Document Number']) !!}
                    @if($errors->has('document_number'))
                    <strong for="document_number" class="help-block">{{ $errors->first('document_number') }}</strong>
                    @endif
                </div>               
            </div>
        <div class="row">
 <div class="form-group col-md-6  {{$errors->has('remarks') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="remarks">Remarks <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::textarea('remarks',old('remarks'), ['class' => 'form-control', 'placeholder' => 'Remarks','rows'=>'2']) !!}
                    @if($errors->has('remarks'))
                    <strong for="address" class="help-block">{{ $errors->first('remarks') }}</strong>
                    @endif
                </div>
            </div>
             <div class="col-md-6 form-group mt-5">
              <label for="is_default"></label>   
                          <label for="is_payment_received">{{ Form::checkbox('is_payment_received', '1', old('is_payment_received'),['id'=>'is_payment_received']) }}</label>
                        <label for="is_default">Payment Received </label>                        
                       
            </div> 
             <div class="col-md-6 form-group">
              <label class="col-md-3 control-label" for="passport_number">Passport Number </label>   
                        <div class="col-md-9">
                    {!! Form::text('passport_number',old('passport_number'), ['class' => 'form-control', 'placeholder' => 'Passport Number']) !!}
                    @if($errors->has('passport_number'))
                    <strong for="passport_number" class="help-block">{{ $errors->first('passport_number') }}</strong>
                    @endif
                </div>     
            </div> 
            <div class="col-md-6 form-group">
              <label class="col-md-3 control-label" for="passport_image">Passport Image </label>   
                 <div class="col-md-9">
                    {{ Form::file('passport_image') }}
                    @if($errors->has('passport_image'))
                    <strong for="passport_image" class="help-block">{{ $errors->first('passport_image') }}</strong>
                    @endif
                  </div>     
                 </div> 
            </div>
        

        
                
</div>
    </div>  
    </div>    



         <div class="card mt-3">
    <div class="card-header">Categories of service</div>
    <div class="card-body">
       <div class="row">
   <div class="col-md-6">
     <div class="form-group {{$errors->has('category_id') ? ' has-error' : ''}}">
                    <label class="col-md-3 control-label" for="destination_id">Category <span style="color:red">*</span></label>
                    <div class="">
                      {!! Form::select('category_id', $categories, old('category_id'), ['id'=>'category_id', 'class' => 'form-control', 'placeholder' => 'Select Category']) !!} 
<!--  {!! Form::select('category_id[]', $categories, old('category_id'), ['class' => 'form-control','id'=>'category_id','title'=>'Choose one or more from the following...','data-actions-box'=>'true', 'data-live-search'=>'true','data-error-container'=>'#category_id-errors','multiple'=>'multiple']) !!} -->
                   
                       @if($errors->has('category_id'))
                        <p class="help-block">
                            <strong>{{ $errors->first('category_id') }}</strong>
                        </p>
                        @endif 
                    </div>
            </div>
   </div>
   <div class="col-md-6">
    <div class="form-group {{$errors->has('subcategory_id') ? ' has-error' : ''}}">
                    <label class="col-md-3 control-label" for="subcategory_id">Sub Category <span style="color:red">*</span></label>
                    <div class="col-md-9">
                      <!--   {!! Form::select('subcategory_id', [], old('subcategory_id'), ['id'=>'subcategory_id', 'class' => 'form-control', 'placeholder' => 'Select Sub Category','multiple'=>'multiple']) !!} -->
                          {!! Form::select('subcategory_id[]', [], old('subcategory_id'), ['class' => 'form-control citys','id'=>'subcategory_id','title'=>'Choose one or more from the following...','data-actions-box'=>'true', 'data-live-search'=>'true','data-error-container'=>'#subcategory_id-errors','multiple'=>'multiple']) !!}  

                        @if($errors->has('subcategory_id'))
                        <p class="help-block">
                            <strong>{{ $errors->first('subcategory_id') }}</strong>
                        </p>
                        @endif
                    </div>
            </div>
  
   </div>

       </div>
       </div>
    </div>
      
           
         
           
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.providers.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.container-fluid -->


@endsection
@section('styles')
<style type="text/css">
.checked {
  color: orange;
}
.greybg{
background-color:#eee;
}
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="{{ asset('admin-theme/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}">

@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('admin-theme/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>

<!-- <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC57llKODVp39WCmsq8xu-WLM9XjPXeLCs&libraries&libraries=places"></script> -->
<script type="text/javascript">
//google.maps.event.addDomListener(window, 'load',initialize);
/*function initialize() 
{
    var input = document.getElementById('address');
    var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        document.getElementById('latitude').value = place.geometry.location.lat();
        document.getElementById('longitude').value = place.geometry.location.lng();
    });
}*/
</script>
<script type="text/javascript">
jQuery(document).ready(function(){  
    jQuery('#security_check').change(function(){
        securityChecked();
    }).trigger('change');

    jQuery('#frmUser').validate({
        rules: {
            name: {
                required: true
            },
            email: {
                required: false,
                email:true
            },
            mobile_number: {
                required: true,
                number:true
            },
            experience_level: {
               required: true
            },
            address: {
                required: true
            },
            password: {
                required: true
            },
            password_confirmation: {
                required: true,
                equalTo: "#password"
            },
            company_name: {
                 required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },                     
            company_logo: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },           
            remarks: {
                 required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },                     
            document_image: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },            
            document_number: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },
            category_id:{
                required: true
            },
            "subcategory_id[]":{
                required: true
            }
        }
    });
$('select[name=category_id]').change(function() {
        var category_id = $(this).val();
        jQuery.post("{{ route('admin.packages.getSubCategories') }}",{'category_id':category_id},function(response){
            $('#subcategory_id').html('');
            $('#subcategory_id').html(response.subcategories);
            //jQuery('[name="subcategory_id[]"]').html(''); 
            jQuery('[name="subcategory_id[]"]').selectpicker('refresh');
        })
    }).trigger('change');
$("input[name='is_personal_verified']").change(function(){
    if($(this).is(":checked")){
        $('.card-information').addClass("greybg"); 
    }else{
        $('.card-information').removeClass("greybg");  
    }
});
$("input[name='is_technical_verified']").change(function(){
    if($(this).is(":checked")){
        $('.card-information-technical').addClass("greybg"); 
    }else{
        $('.card-information-technical').removeClass("greybg");  
    }
});

});
function securityChecked(){
    jQuery('#security_container').slideDown("slow");
    jQuery('#security_container').hide();
    if(jQuery('#security_check').prop('checked')==true){
        jQuery('#security_container').show();
    }
}
</script>
@endsection