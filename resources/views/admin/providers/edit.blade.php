@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Providers</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
{!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.providers.update',$user->id],'class' => 'form-horizontal','id' => 'frmUser']) !!}
        @method('PUT') 
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Provider</h6>
        </div>
        <div class="card-body">
        <div class="col-md-12 form-group">
                  <label for="is_verify">{{ Form::checkbox('is_verify', '1', old('is_verify',isset($user->is_verify)?$user->is_verify:0),['id'=>'is_verify']) }}</label>
                  <label for="is_verify"> Verify Provider Account </label>
                   
        </div>
          <div class="card">
          <div class="card-header">Personal Information</div>
            <div class="card-body"><!-- <h5 class="card-title ml-2">Personal Information</h5> -->
            <div class="form-group {{$errors->has('name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="name">Name <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('name', old('name',$user->name), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('name'))
                    <strong for="name" class="help-block">{{ $errors->first('name') }}</strong>
                    @endif
                </div>
            </div>
             <div class="form-group {{$errors->has('mobile_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="mobile_number">Mobile Number <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('mobile_number',old('mobile_number',$user->mobile_number), ['class' => 'form-control', 'placeholder' => 'Mobile Number']) !!}
                    @if($errors->has('mobile_number'))
                    <strong for="mobile_number" class="help-block">{{ $errors->first('mobile_number') }}</strong>
                    @endif
                </div>
            </div>
             <div class="form-group {{$errors->has('email') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="email">Email <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('email',old('email',$user->email), ['class' => 'form-control autoFillOff', 'placeholder' => 'Email']) !!}
                    @if($errors->has('email'))
                    <strong for="email" class="help-block">{{ $errors->first('email') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group">
                 <div class="col-md-9">
                    <label>
                        {{Form::checkbox('reset_password', TRUE, null,['id'=>'reset_password'])}}
                        {{ __('Reset Password') }}
                    </label>
                </div>
            </div>

            <div  id="password_container">
                <div class="form-group {{$errors->has('password') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                    <label class="col-md-3 control-label" for="password">New Password <span style="color:red">*</span></label>
                    <div class="col-md-9">
                        {!! Form::password('password',['class' => 'form-control autoFillOff', 'placeholder' => 'New Password', 'id'=>'password']) !!}
                        @if($errors->has('password'))
                        <strong for="password" class="help-block">{{ $errors->first('password') }}</strong>
                        @endif
                    </div>
                </div>
                <div class="form-group {{$errors->has('password_confirmation') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                    <label class="col-md-3 control-label" for="password_confirmation">Confirm Password <span style="color:red">*</span></label>
                    <div class="col-md-9">
                        {!! Form::password('password_confirmation', ['class' => 'form-control autoFillOff', 'placeholder' => 'Confirm Password']) !!}
                        @if($errors->has('password_confirmation'))
                        <strong for="password_confirmation" class="help-block">{{ $errors->first('password_confirmation') }}</strong>
                        @endif
                    </div>
                </div>
            </div>
            @if(isset($user) && $user->getMedia('certificate_conduct')->count() > 0 && file_exists($user->getFirstMedia('certificate_conduct')->getPath()))
        @php $image_required = false; @endphp
    <div class="col-md-1 form-group">
        <img width="100%" src="{{ $user->getFirstMedia('certificate_conduct')->getFullUrl() }}" />
    </div>
    @endif

    <div class="form-group {{$errors->has('certificate_conduct') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
        <label class="col-md-3 control-label" for="title">Upload Certificate of conduct </label>
        <div class="col-md-9">
             {{ Form::file('certificate_conduct') }}
            @if($errors->has('certificate_conduct'))
            <strong for="profile_picture" class="help-block">{{ $errors->first('certificate_conduct') }}</strong>
            @endif
        </div>
    </div>
            <div class="form-group {{$errors->has('experience_level') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-9 control-label" for="section">Year of Experience <span style="color:red">*</span></label>
                 <div class="col-md-9"> 
                    {!! Form::select('experience_level', $experience_levels, old('experience_level',isset($user->profile->experience_level_id)?$user->profile->experience_level_id:''), ['id'=>'experience_level', 'class' => 'form-control', 'placeholder' => 'Year of Experience']) !!}
                    @if($errors->has('experience_level'))
                    <p class="help-block">
                        <strong>{{ $errors->first('experience_level') }}</strong>
                    </p>
                    @endif
                </div>
            </div>
            <div class="form-group {{$errors->has('address_line_1') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="address_line_1">Address line 1 </label>
                <div class="col-md-9">
                    {!! Form::textarea('address_line_1',old('address_line_1',isset($user->profile->address_line_1)?$user->profile->address_line_1:''), ['class' => 'form-control', 'placeholder' => 'Address line 1','rows'=>'1','id' =>'address_line_1' ]) !!}
                    @if($errors->has('address_line_1'))
                    <strong for="address_line_1" class="help-block">{{ $errors->first('address_line_1') }}</strong>
                    @endif
                </div>
            </div>
            <div class="form-group {{$errors->has('address') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="address">Address <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::textarea('address',old('address',isset($user->profile->work_address)?$user->profile->work_address:''), ['class' => 'form-control', 'placeholder' => 'Address','rows'=>'1','id' =>'address' ]) !!}
                    @if($errors->has('address'))
                    <strong for="address" class="help-block">{{ $errors->first('address') }}</strong>
                    @endif
                </div>
            </div>
            <div class="form-group {{$errors->has('zip_code') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="zip_code">Zipcode </label>
                <div class="col-md-9">
                    {!! Form::text('zip_code', old('zip_code',isset($user->profile->zip_code)?$user->profile->zip_code:''), ['class' => 'form-control', 'placeholder' => 'Zip code']) !!}
                    @if($errors->has('zip_code'))
                    <strong for="zip_code" class="help-block">{{ $errors->first('zip_code') }}</strong>
                    @endif
                </div>
            </div>
<div class="row">
<div class="col-md-4">
 <div class="form-group {{$errors->has('latitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="latitude">Latitude </label>
                <div class="col-md-12">
                    {!! Form::text('latitude',old('latitude',isset($user->profile->latitude)?$user->profile->latitude:''), ['class' => 'form-control', 'placeholder' => 'Latitude','id' =>'latitude' ]) !!}
                    @if($errors->has('latitude'))
                    <strong for="latitude" class="help-block">{{ $errors->first('latitude') }}</strong>
                    @endif
                </div>
            </div>
</div>
<div class="col-md-4">
 <div class="form-group {{$errors->has('longitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="longitude">Longitude </label>
                <div class="col-md-12">
                    {!! Form::text('longitude',old('longitude',isset($user->profile->longitude)?$user->profile->longitude:''), ['class' => 'form-control', 'placeholder' => 'Longitude','id' =>'longitude']) !!}
                    @if($errors->has('longitude'))
                    <strong for="longitude" class="help-block">{{ $errors->first('longitude') }}</strong>
                    @endif
                </div>
            </div>
</div>
</div>


           
           

            @php $image_required = true; @endphp
                @if(isset($user) && $user->getMedia('profile_picture')->count() > 0 && file_exists($user->getFirstMedia('profile_picture')->getPath()))
                    @php $image_required = false; @endphp
                <div class="col-md-1 form-group">
                    <img width="100%" src="{{ $user->getFirstMedia('profile_picture')->getFullUrl() }}" />
                </div>
                @endif
            
            <div class="form-group {{$errors->has('profile_picture') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="title">Profile Picture </label>
                <div class="col-md-9">
                     {{ Form::file('profile_picture') }}
                    @if($errors->has('profile_picture'))
                    <strong for="profile_picture" class="help-block">{{ $errors->first('profile_picture') }}</strong>
                    @endif
                </div>
            </div>



        </div> 
          </div>
            
             <div class="card mt-3">  <div class="card-header">Evidence of Expertise</div>
    <div class="card-body">
      <div class="row">
 <div class="form-group col-md-3  ">
Reviews
 </div>
  <div class="form-group col-md-6 row ">    
<div class="col-md-2 mr-2 mt-2 "><strong> <div class="rating">
</div></strong></div>
<div class="col-md-3 mr-3"> <div type="button"  data-toggle="modal" data-target="#myModal-001">
       
        <?php if(count($provider_review)>0) { ?> <div class="primary-btn btn text-primary"> View All</div>
        <?php } ?>
      </div>
     
     </div>

<div class="modal" id="myModal-001">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header"> Reviews/Rating
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body row col-md-12">
        <?php if(count($provider_review)>0) { ?>
        <div class="col-md-4 text-primary">Seeker name</div><div class="col-md-4 text-primary">Review</div><div class="col-md-4 text-primary">Rating</div>
          <?php foreach ($provider_review as $key => $review) { ?>
            <div class="col-md-4"><?php echo isset($review->user->name)?$review->user->name:''; ?></div>
               <div class="col-md-4"><?php echo isset($review->text)?$review->text:''; ?></div>
               <div class="col-md-4">
          <?php
          if(isset($review->rating))
          {
             for($i=1;$i<6;$i++)
            {?>
              <span class="fa fa-star <?php if($review->rating>=$i){ echo ""; }else { echo "fa-star-o"; } ?>"></span>
            <?php 
            }
          }
          ?></div>
          <?php }
          ?>
        <?php } ?>     
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


 </div>
</div>
   
                 <div class="col-md-9 mb-3">
<div class="row">

  <?php if(isset($works_photo) && !empty($works_photo))
  {
    $i = 1;
    foreach ($works_photo as $key => $photo) {
      ?>
     <div class="col-md-1 mr-4">
      <div type="button"  data-toggle="modal" data-target="#myModal-<?php echo $i; // Displaying the increment ?>">
          <img width="70" height="70" src="{{ $photo->getFullUrl() }}" />
      </div>
     </div>

  

<!-- The Modal -->
<div class="modal" id="myModal-<?php echo $i; // Displaying the increment ?>">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <img width="100%" height="100%" src="{{ $photo->getFullUrl() }}" />
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
    <?php $i++; }
  } ?>

</div>  <div class="form-group {{$errors->has('image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
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
                    {!! Form::text('facebook_url',old('facebook_url',isset($user->profile->facebook_url)?$user->profile->facebook_url:''), ['class' => 'form-control', 'placeholder' => 'Facebook url ']) !!}
                    @if($errors->has('facebook_url'))
                    <strong for="facebook_url" class="help-block">{{ $errors->first('facebook_url') }}</strong>
                    @endif
                </div>
            </div></div>
<div class="col-md-3"> <div class="form-group {{$errors->has('instagram_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="instagram_url">Instagram url </label>
                <div class="col-md-12">
                    {!! Form::text('instagram_url',old('instagram_url',isset($user->profile->instagram_url)?$user->profile->instagram_url:''), ['class' => 'form-control', 'placeholder' => 'Instagram url']) !!}
                    @if($errors->has('instagram_url'))
                    <strong for="instagram_url" class="help-block">{{ $errors->first('instagram_url') }}</strong>
                    @endif
                </div>
            </div></div>
<div class="col-md-3"> <div class="form-group {{$errors->has('twitter_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="twitter_url">Twitter url </label>
                <div class="col-md-12">
                    {!! Form::text('twitter_url',old('twitter_url',isset($user->profile->twitter_url)?$user->profile->twitter_url:''), ['class' => 'form-control', 'placeholder' => 'Twitter url']) !!}
                    @if($errors->has('twitter_url'))
                    <strong for="twitter_url" class="help-block">{{ $errors->first('twitter_url') }}</strong>
                    @endif
                </div> 
            </div></div>
 </div>

           
            
    </div>
 </div>
           
  <div class="card mt-3">
    <div class="card-header">Declaration</div>
    <div class="card-body">
  <div class="col-md-12 form-group">
      <label for="fundi_is_middlemen">{{ Form::checkbox('fundi_is_middlemen', '1', old('fundi_is_middlemen',isset($user->profile->fundi_is_middlemen)?$user->profile->fundi_is_middlemen:''),['id'=>'fundi_is_middlemen']) }}</label>
                        <label for="is_default">I am not middlemen. </label>
                   
                      
            </div>
              <div class="col-md-12 form-group">
      <label for="fundi_have_tools">{{ Form::checkbox('fundi_have_tools', '1', old('fundi_have_tools',isset($user->profile->fundi_have_tools)?$user->profile->fundi_have_tools:''),['id'=>'fundi_have_tools']) }}</label>
                        <label for="is_default">I have all the required tools to do their job.  </label>
            </div>
               <div class="col-md-12 form-group">
      <label for="fundi_have_tools">{{ Form::checkbox('fundi_have_smartphone', '1', old('fundi_have_smartphone',isset($user->profile->fundi_have_smartphone)?$user->profile->fundi_have_smartphone:''),['id'=>'fundi_have_smartphone']) }}</label>
                        <label for="is_default">I have a smartphone.  </label>
            </div>
    </div>
  </div>
      <div class="card mt-3">
    <div class="card-header">  {{Form::checkbox('security_check',1,old('security_check',isset($user->profile->security_check)?$user->profile->security_check:0), ['id'=>'security_check'])}}
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
                    {!! Form::text('company_name',old('company_name',$company_name), ['class' => 'form-control', 'placeholder' => 'Company Name']) !!}
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
                    @php $company_logo_required = true; @endphp
                    @if(isset($providerCompany) && $providerCompany->getMedia('company_logo')->count() > 0 && file_exists($providerCompany->getFirstMedia('company_logo')->getPath()))
                        @php $company_logo_required = false; @endphp
                        <div class="row mt-2">
                            <div class="col-md-1 form-group">
                        <img width="100%" src="{{ $providerCompany->getFirstMedia('company_logo')->getFullUrl() }}" />
                          
                    </div>
                    <div class="col-md-1 mt-3 form-group">
                        <a download href="{{ $providerCompany->getFirstMedia('company_logo')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
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
                    @php $document_image_required = true; @endphp
                    @if(isset($providerCompany) && $providerCompany->getMedia('document_image')->count() > 0 && file_exists($providerCompany->getFirstMedia('document_image')->getPath()))
                        @php $document_image_required = false; @endphp
                        <div class="row mt-2">
                       <!--      <div class="col-md-1 form-group">
                        <img width="100%" src="{{ $providerCompany->getFirstMedia('document_image')->getFullUrl() }}" />
                          
                    </div> -->
                    <div class="col-md-1 mt-3 form-group">
                        <a download href="{{ $providerCompany->getFirstMedia('document_image')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                </div>               
            </div>
            <div class="form-group {{$errors->has('document_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="document_number">Document Number <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('document_number',old('document_number',$document_number), ['class' => 'form-control', 'placeholder' => 'Document Number']) !!}
                    @if($errors->has('document_number'))
                    <strong for="document_number" class="help-block">{{ $errors->first('document_number') }}</strong>
                    @endif
                </div>               
            </div>
        <div class="row">
 <div class="form-group col-md-6  {{$errors->has('remarks') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="remarks">Remarks <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::textarea('remarks',old('remarks',$remarks), ['class' => 'form-control', 'placeholder' => 'Remarks','rows'=>'2']) !!}
                    @if($errors->has('remarks'))
                    <strong for="address" class="help-block">{{ $errors->first('remarks') }}</strong>
                    @endif
                </div>
            </div>
             <div class="col-md-6 form-group mt-5">
              <label for="is_default"></label>   
                          <label for="is_payment_received">{{ Form::checkbox('is_payment_received', '1', old('is_payment_received',$is_payment_received),['id'=>'is_payment_received']) }}</label>
                        <label for="is_default">Payment Received </label>                        
                       
            </div> 
        </div>
        

        
                
</div>
    </div>  
    </div>    

          <div class="card mt-3">
    <div class="card-header">Certification</div>
    <div class="card-body">
       <div class="row">
   <div class="col-md-6">
      <div class="form-group {{$errors->has('degree_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="degree_title">Degree </label>
                <div class="col-md-9">
                    {!! Form::text('degree_title',old('degree_title',isset($providerdegree->title)?$providerdegree->title:''), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('degree_title'))
                    <strong for="document_number" class="help-block">{{ $errors->first('degree_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
   <div class="col-md-6 row">
     <div class="col-md-6">
           {{ Form::file('degree') }}
</div>
     <div class="col-md-6">
                    @if($errors->has('degree'))
                    <strong for="degree" class="help-block">{{ $errors->first('degree') }}</strong>
                    @endif                  
                    @if(isset($providerdegree) && $providerdegree->getMedia('degree')->count() > 0 && file_exists($providerdegree->getFirstMedia('degree')->getPath()))  
                    <div class="row  col-md-6">                     
                    <div class="col-md-1  form-group">
                        <a download href="{{ $providerdegree->getFirstMedia('degree')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                    </div>
   </div>
 <div class="col-md-6">
      <div class="form-group {{$errors->has('diploma_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="diploma_title">Diploma</label>
                <div class="col-md-9">
                    {!! Form::text('diploma_title',old('diploma_title',isset($providerdiploma->title)?$providerdiploma->title:''), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('diploma_title'))
                    <strong for="diploma_title" class="help-block">{{ $errors->first('diploma_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
    <div class="col-md-6 row">
       <div class="col-md-6">
           {{ Form::file('diploma') }}
           </div>
     <div class="col-md-6">
                    @if($errors->has('diploma'))
                    <strong for="diploma" class="help-block">{{ $errors->first('diploma') }}</strong>
                    @endif                  
                    @if(isset($providerdiploma) && $providerdiploma->getMedia('diploma')->count() > 0 && file_exists($providerdiploma->getFirstMedia('diploma')->getPath()))              
                     
                        <div class="row  col-md-6">
                          
                    <div class="col-md-1  form-group">
                        <a download href="{{ $providerdiploma->getFirstMedia('diploma')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                     </div>
   </div>
    <div class="col-md-6">
      <div class="form-group {{$errors->has('certification_title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12 control-label" for="certification_title">Certification</label>
                <div class="col-md-9">
                    {!! Form::text('certification_title',old('certification_title',isset($providercertification->title)?$providercertification->title:''), ['class' => 'form-control', 'placeholder' => '']) !!}
                    @if($errors->has('certification_title'))
                    <strong for="certification_title" class="help-block">{{ $errors->first('certification_title') }}</strong>
                    @endif
                </div>               
            </div>
   </div>
      <div class="col-md-6 row">
          <div class="col-md-6">
           {{ Form::file('certification') }}
           </div>
     <div class="col-md-6">
                    @if($errors->has('certification'))
                    <strong for="diploma" class="help-block">{{ $errors->first('certification') }}</strong>
                    @endif                  
                    @if(isset($providercertification) && $providercertification->getMedia('certification')->count() > 0 && file_exists($providercertification->getFirstMedia('certification')->getPath()))              
                     
                      <div class="row  col-md-6">  
                           
                    <div class="col-md-1 form-group">
                        <a download href="{{ $providercertification->getFirstMedia('certification')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
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
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
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

var ratingValue = '<?php echo isset($rating)?$rating:'';?>',
  rounded = (ratingValue | 0),
  str;

for (var j = 0; j < 5; j++) {
  str = '<i class="fa ';
  if (j < rounded) {
    str += "fa-star";
  } else if ((ratingValue - j) > 0 && (ratingValue - j) < 1) {
    str += "fa-star-half-o";
  } else {
    str += "fa-star-o";
  }
  str += '" aria-hidden="true"></i>';
  $(".rating").append(str);
}

    jQuery('#reset_password').change(function(){
        resetPassword();
    }).trigger('change');
    jQuery('#security_check').change(function(){
        securityChecked();
    }).trigger('change');

    jQuery('#frmUser').validate({
        rules: {
            name: {
                required: true
            },
            email: {
                required: true,
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
            },/*
            latitude: {
                required: true
            },
            longitude: {
                required: true
            },*/
            password: {
                required: function(){
                    if(jQuery('#frmUser #reset_password').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },
            password_confirmation: {
                required: function(){  
                    if(jQuery('#frmUser #reset_password').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                },
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
            @if($company_logo_required==true)            
            company_logo: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },
            @endif
            remarks: {
                 required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },
            @if($document_image_required==true)            
            document_image: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            },
            @endif
            document_number: {
                  required: function(){
                    if(jQuery('#frmUser #security_check').prop('checked')==false){
                        return false;
                    }else{
                        return true;
                    }
                }
            }
        }
    });
});

function securityChecked(){
    jQuery('#security_container').slideDown("slow");
    jQuery('#security_container').hide();
    //alert( jQuery('#security_container').val());
    if(jQuery('#security_check').prop('checked')==true){
        jQuery('#security_container').show();
    }
}
function resetPassword(){
    jQuery('#password_container').hide();
    if(jQuery('#reset_password').prop('checked')==true){
        jQuery('#password_container').show();
    }
}
</script>
@endsection