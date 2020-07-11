@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Categories</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.categories.update',$category->id],'class' => 'form-horizontal','id' => 'frmCategory']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Category</h6>
        </div>
        <div class="card-body">
            <div class="form-group {{$errors->has('parent_id') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="parent_id">Parent Category <span style="color:red"></span></label>
                <div class="col-md-9">
                    {!! Form::select('parent_id', $categories, old('parent_id', isset($category->parent_id)?$category->parent_id:''), ['id'=>'parent_id', 'class' => 'form-control', 'placeholder' => 'Select Parent Category']) !!}
                    @if($errors->has('parent_id'))
                    <strong for="parent_id" class="help-block">{{ $errors->first('parent_id') }}</strong>
                    @endif
                </div>
            </div>
            <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="title">Name <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('title', old('title',$category->title), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>
            @php $image_required = true; @endphp
            @if(isset($category) && $category->getMedia('image')->count() > 0 && file_exists($category->getFirstMedia('image')->getPath()))
                @php $image_required = false; @endphp
            <div class="col-md-2 form-group">
                <img width="100%" src="{{ $category->getFirstMedia('image')->getFullUrl() }}" />
            </div>
            @endif
            <div class="col-md-9 form-group {{$errors->has('image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label for="image">Image File </label>
                <div class="clearfix"></div>
                {!! Form::file('image', ['id'=>'image']) !!}

                @if($errors->has('image'))
                <p class="help-block">
                    <strong>{{ $errors->first('image') }}</strong>
                </p>
                @endif                       
            </div>
        </div> 
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.categories.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
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
    jQuery('#frmCategory').validate({
        rules: {
            title: {
                required: true
            }
        }
    });
});
</script>
@endsection