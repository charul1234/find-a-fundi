@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Cities</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.cities.update',$city->id],'class' => 'form-horizontal','id' => 'frmCity']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit City</h6>
        </div>
        <div class="card-body"> 
            <div class="form-group {{$errors->has('country_id') ? ' has-error' : ''}}">
                    <label class="col-md-6 control-label" for="country_id">Country <span style="color:red">*</span></label>
                    <div class="col-md-6">
                    {!! Form::select('country_id', $countries, old('country_id',isset($city->country_id)?$city->country_id:''), ['id'=>'country_id', 'class' => 'form-control', 'placeholder' => 'Select Country']) !!}

                    @if($errors->has('country_id'))
                    <p class="help-block">
                        <strong>{{ $errors->first('country_id') }}</strong>
                    </p>
                    @endif 
                     </div>
            </div>            
            <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="title">Name <span style="color:red">*</span></label>
                 <div class="col-md-6">
                    {!! Form::text('title', old('title',$city->title), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>          
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.cities.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
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
    jQuery('#frmCity').validate({
        rules: { 
            country_id: {
                required: true
            },           
            title: {
                required: true
            }
        }
    });
});
</script>
@endsection