@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">FAQs</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.faqs.update',$faq->id],'class' => 'form-horizontal','id' => 'frmFaq']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit FAQ</h6>
        </div>
        <div class="card-body">    
             <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-9 control-label" for="title">Title <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('title', old('title',isset($faq->title)?$faq->title:''), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>

             <div class="form-group {{$errors->has('description') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="address">Description </label>
                <div class="col-md-9">
                    {!! Form::textarea('description',old('description',isset($faq->description)?$faq->description:''), ['class' => 'form-control', 'placeholder' => 'Description','class'=>'ckeditor']) !!}
                    @if($errors->has('description'))
                    <strong for="description" class="help-block">{{ $errors->first('description') }}</strong>
                    @endif
                </div>
            </div>   
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.faqs.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.container-fluid -->
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('admin-theme/vendor/ckeditor/js/ckeditor.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#frmFaq').validate({
        rules: {
            title:{
                required: true
            },
            description:{
                required: true
            }
        }
    });
});
</script>
@endsection