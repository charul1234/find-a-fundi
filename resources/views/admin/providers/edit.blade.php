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
             <div class="form-group {{$errors->has('company_name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <?php  $company_name=isset($providerCompany->name)?$providerCompany->name:''; 
                       $remarks=isset($providerCompany->remarks)?$providerCompany->remarks:'';
                       $document_number=isset($providerCompany->document_number)?$providerCompany->document_number:'';
                       $is_payment_received=isset($providerCompany->is_payment_received)?$providerCompany->is_payment_received:'';?>
                 <h6 class="col-md-3 font-weight-bold text-primary">Security Company</h6>
                <label class="col-md-3 control-label" for="company_name">Name of company <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::text('company_name',old('company_name',$company_name), ['class' => 'form-control', 'placeholder' => 'Name of company ']) !!}
                    @if($errors->has('company_name'))
                    <strong for="company_name" class="help-block">{{ $errors->first('company_name') }}</strong>
                    @endif
                </div>               
            </div>
            <div class="form-group {{$errors->has('company_logo') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="company_logo">Company logo <span style="color:red">*</span></label>
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
            <div class="form-group {{$errors->has('remarks') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="remarks">Remarks <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::textarea('remarks',old('remarks',$remarks), ['class' => 'form-control', 'placeholder' => 'Remarks','rows'=>'2']) !!}
                    @if($errors->has('remarks'))
                    <strong for="address" class="help-block">{{ $errors->first('remarks') }}</strong>
                    @endif
                </div>
            </div>
            <div class="form-group {{$errors->has('document_image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="document_image">Document Image <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {{ Form::file('document_image') }}
                    @if($errors->has('document_image'))
                    <strong for="document_image" class="help-block">{{ $errors->first('document_image') }}</strong>
                    @endif
                    @php $document_image_required = true; @endphp
                    @if(isset($providerCompany) && $providerCompany->getMedia('document_image')->count() > 0 && file_exists($providerCompany->getFirstMedia('document_image')->getPath()))
                        @php $document_image_required = false; @endphp
                        <div class="row mt-2">
                            <div class="col-md-1 form-group">
                        <img width="100%" src="{{ $providerCompany->getFirstMedia('document_image')->getFullUrl() }}" />
                          
                    </div>
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
            <div class="col-md-12 form-group">
                        <label for="is_default">Payment Received </label>
                         <div class="clearfix"></div>
                        <label for="is_payment_received">{{ Form::checkbox('is_payment_received', '1', old('is_payment_received',$is_payment_received),['id'=>'is_payment_received']) }}</label>
            </div>
            <div class="col-md-12 form-group">
                        <label for="is_verify">Verify </label>
                         <div class="clearfix"></div>
                        <label for="is_verify">{{ Form::checkbox('is_verify', '1', old('is_verify',isset($user->is_verify)?$user->is_verify:0),['id'=>'is_verify']) }}</label>
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
                required: true
            },
            @if($company_logo_required==true)            
            company_logo: {
                required: true
            },
            @endif
            remarks: {
                required: true
            },
            @if($document_image_required==true)            
            document_image: {
                required: true
            },
            @endif
            document_number: {
                required: true
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