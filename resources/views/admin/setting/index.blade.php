@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{ __('global.setting.setting') }}</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST', 'files'=>true, 'route' => ['admin.settings.store'], 'class' => 'form-horizontal', 'id' => 'frmSetting']) !!}
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('global.setting.edit_setting') }}</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="form-group {{$errors->has('email') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="email">{{ __('global.setting.contact_email') }} <span style="color:red">*</span></label>
                        <div class="col-md-12">
                            {!! Form::text('email',old('email',getSetting('email')), ['class' => 'form-control', 'placeholder' => __('global.setting.contact_email')]) !!}
                            @if($errors->has('email'))
                            <strong for="email" class="help-block">{{ $errors->first('email') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group {{$errors->has('contact_number') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="contact_number">{{ __('global.setting.contact_number') }} <span style="color:red">*</span></label>
                        <div class="col-md-12">
                            {!! Form::text('contact_number',old('contact_number',getSetting('contact_number')), ['class' => 'form-control', 'placeholder' => __('global.setting.contact_number')]) !!}
                            @if($errors->has('contact_number'))
                            <strong for="contact_number" class="help-block">{{ $errors->first('contact_number') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group {{$errors->has('address') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-12" for="address">{{__('global.setting.address') }} <span style="color:red">*</span></label>
                 <div class="col-md-12">
                    {!! Form::textarea('address', old('address',getSetting('address')), ['rows'=>'3','class' => 'form-control', 'placeholder' => __('global.setting.address')]) !!}
                    @if($errors->has('address'))
                    <strong for="address" class="help-block">{{ $errors->first('address') }}</strong>
                    @endif
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <div class="form-group {{$errors->has('facebook_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="facebook_url">{{__('global.setting.facebook_url') }} <span style="color:red"></span></label>
                         <div class="col-md-12">
                            {!! Form::text('facebook_url', old('facebook_url',getSetting('facebook_url')), ['class' => 'form-control', 'placeholder' => __('global.setting.facebook_url')]) !!}
                            @if($errors->has('facebook_url'))
                            <strong for="facebook_url" class="help-block">{{ $errors->first('facebook_url') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group {{$errors->has('twitter_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="twitter_url">{{ __('global.setting.twitter_url') }} <span style="color:red"></span></label>
                         <div class="col-md-12">
                            {!! Form::text('twitter_url', old('twitter_url',getSetting('twitter_url')), ['class' => 'form-control', 'placeholder' => __('global.setting.twitter_url')]) !!}
                            @if($errors->has('twitter_url'))
                            <strong for="twitter_url" class="help-block">{{ $errors->first('twitter_url') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group {{$errors->has('instagram_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="instagram_url">{{__('global.setting.instagram_url') }} <span style="color:red"></span></label>
                         <div class="col-md-12">
                            {!! Form::text('instagram_url', old('instagram_url',getSetting('instagram_url')), ['class' => 'form-control', 'placeholder' => __('global.setting.instagram_url')]) !!}
                            @if($errors->has('instagram_url'))
                            <strong for="instagram_url" class="help-block">{{ $errors->first('instagram_url') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group {{$errors->has('linkedin_url') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="linkedin_url">{{__('global.setting.linkedin_url') }} <span style="color:red"></span></label>
                         <div class="col-md-12">
                            {!! Form::text('linkedin_url', old('linkedin_url',getSetting('linkedin_url')), ['class' => 'form-control', 'placeholder' => __('global.setting.linkedin_url')]) !!}
                            @if($errors->has('linkedin_url'))
                            <strong for="linkedin_url" class="help-block">{{ $errors->first('linkedin_url') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-3">
                    @php $logo = getSetting('logo'); @endphp
                    @if(isset($logo) && $logo!=''  && \Storage::exists(config('constants.SETTING_IMAGE_URL').$logo)) 
                    <div class="form-group">
                        <div class="col-md-12">                            
                            <img width="50" height="50" src="{{ \Storage::url(config('constants.SETTING_IMAGE_URL').$logo) }}">
                        </div>
                    </div>
                    @endif
                    <div class="form-group {{$errors->has('logo') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="title">{{__('global.setting.logo') }} </label>
                        <div class="col-md-12">
                             {{ Form::file('logo') }}
                            @if($errors->has('logo'))
                            <strong for="logo" class="help-block">{{ $errors->first('logo') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    @php $footer_logo = getSetting('footer_logo'); @endphp
                    @if(isset($footer_logo) && $footer_logo!=''  && \Storage::exists(config('constants.SETTING_IMAGE_URL').$footer_logo)) 
                    <div class="form-group">
                        <div class="col-md-12">                            
                            <img width="50" height="50" src="{{ \Storage::url(config('constants.SETTING_IMAGE_URL').$footer_logo) }}">
                        </div>
                    </div>
                    @endif
                    <div class="form-group {{$errors->has('footer_logo') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="title">{{__('global.setting.footer_logo') }} </label>
                        <div class="col-md-12">
                             {{ Form::file('footer_logo') }}
                            @if($errors->has('footer_logo'))
                            <strong for="footer_logo" class="help-block">{{ $errors->first('footer_logo') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    @php $favicon = getSetting('favicon'); @endphp
                    @if(isset($favicon) && $favicon!=''  && \Storage::exists(config('constants.SETTING_IMAGE_URL').$favicon)) 
                    <div class="form-group">
                        <div class="col-md-12">
                            <img width="50" height="50" src="{{ \Storage::url(config('constants.SETTING_IMAGE_URL').$favicon) }}">
                        </div>
                    </div>
                    @endif

                    <div class="form-group {{$errors->has('favicon') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label class="col-md-12" for="title">{{__('global.setting.favicon') }} </label>
                        <div class="col-md-12">
                            {{ Form::file('favicon') }}
                            @if($errors->has('favicon'))
                            <strong for="favicon" class="help-block">{{ $errors->first('favicon') }}</strong>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.dashboard')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
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
    jQuery('#frmSetting').validate({
        rules: {
            email: {
                required: true,
                email:true
            },
            contact_number: {
                required: true
            },
            address: {
                required: true
            }
        }
    });
});
</script>
@endsection