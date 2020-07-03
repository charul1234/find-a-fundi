@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Countries</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.countries.store'],'class' => 'form-horizontal','id' => 'frmCountry']) !!}
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add Country</h6>
        </div>
        <div class="card-body">           

            <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="title">Title <span style="color:red">*</span></label>
                 <div class="col-md-6">
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>
            <div class="col-md-12 form-group">
                        <label for="is_default">Make Default Country</label>
                         <div class="clearfix"></div>
                        <label for="is_default">{{ Form::checkbox('is_default', '1', old('is_default'),['id'=>'is_default']) }} Default</label>
                    </div>
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.countries.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
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
    jQuery('#frmCountry').validate({
        rules: {
            title:{
                required: true
            }
        }
    });
});
</script>
@endsection