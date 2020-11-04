@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Seekers</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
{!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.seekers.update',$user->id],'class' => 'form-horizontal','id' => 'frmUser']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Seeker</h6>
        </div>
        <div class="card-body">
            <div class="form-group {{$errors->has('name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="name">Name <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('name', old('name',$user->name), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('name'))
                    <strong for="name" class="help-block">{{ $errors->first('name') }}</strong>
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

            <div class="form-group {{$errors->has('mobile_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="mobile_number">Mobile Number <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('mobile_number',old('mobile_number',$user->mobile_number), ['class' => 'form-control', 'placeholder' => 'Mobile Number']) !!}
                    @if($errors->has('mobile_number'))
                    <strong for="mobile_number" class="help-block">{{ $errors->first('mobile_number') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('address') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="address">Address <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::textarea('address',old('address',isset($user->profile->work_address)?$user->profile->work_address:''), ['class' => 'form-control', 'placeholder' => 'Address','rows'=>'1']) !!}
                    @if($errors->has('address'))
                    <strong for="address" class="help-block">{{ $errors->first('address') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('latitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="latitude">Latitude </label>
                <div class="col-md-9">
                    {!! Form::text('latitude',old('latitude',isset($user->profile->latitude)?$user->profile->latitude:''), ['class' => 'form-control', 'placeholder' => 'Latitude']) !!}
                    @if($errors->has('latitude'))
                    <strong for="latitude" class="help-block">{{ $errors->first('latitude') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('longitude') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="longitude">Longitude </label>
                <div class="col-md-9">
                    {!! Form::text('longitude',old('longitude',isset($user->profile->longitude)?$user->profile->longitude:''), ['class' => 'form-control', 'placeholder' => 'Longitude']) !!}
                    @if($errors->has('longitude'))
                    <strong for="longitude" class="help-block">{{ $errors->first('longitude') }}</strong>
                    @endif
                </div>
            </div>

             <div class="form-group {{$errors->has('tentative_hour') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="tentative_hour">Tentative hour </label>
                <div class="col-md-9">
                    {!! Form::text('tentative_hour',old('tentative_hour',isset($user->profile->tentative_hour)?$user->profile->tentative_hour:''), ['class' => 'form-control', 'placeholder' => 'example : 4']) !!}
                    @if($errors->has('tentative_hour'))
                    <strong for="tentative_hour" class="help-block">{{ $errors->first('tentative_hour') }}</strong>
                    @endif
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
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.seekers.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.container-fluid -->
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#reset_password').change(function(){
        resetPassword();
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
            address: {
                required: true
            },
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
            }
        }
    });
});
function resetPassword(){
    jQuery('#password_container').hide();
    if(jQuery('#reset_password').prop('checked')==true){
        jQuery('#password_container').show();
    }
}
</script>
@endsection