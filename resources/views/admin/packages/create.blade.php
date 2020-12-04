@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Packages </h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.packages.store'],'class' => 'form-horizontal','id' => 'frmPackage']) !!}
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Add Package</h6>
        </div>
        <div class="card-body">

           <!-- <div class="form-group {{$errors->has('category_id') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="category_id">Category <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::select('category_id', $categories, old('category_id'), ['id'=>'category_id', 'class' => 'form-control', 'placeholder' => 'Select Category']) !!}
                    @if($errors->has('category_id'))
                    <strong for="category_id" class="help-block">{{ $errors->first('category_id') }}</strong>
                    @endif
                </div>
            </div> -->
            <div class="form-group {{$errors->has('category_id') ? ' has-error' : ''}}">
                    <label class="col-md-3 control-label" for="destination_id">Category <span style="color:red">*</span></label>
                    <div class="col-md-9">
                        {!! Form::select('category_id', $categories, old('category_id'), ['id'=>'category_id', 'class' => 'form-control', 'placeholder' => 'Select Category']) !!}

                        @if($errors->has('category_id'))
                        <p class="help-block">
                            <strong>{{ $errors->first('category_id') }}</strong>
                        </p>
                        @endif
                    </div>
            </div>
            <div class="form-group {{$errors->has('subcategory_id') ? ' has-error' : ''}}">
                    <label class="col-md-3 control-label" for="subcategory_id">Sub Category <span style="color:red">*</span></label>
                    <div class="col-md-9">
                        {!! Form::select('subcategory_id', [], old('subcategory_id'), ['id'=>'subcategory_id', 'class' => 'form-control', 'placeholder' => 'Select Sub Category']) !!}

                        @if($errors->has('subcategory_id'))
                        <p class="help-block">
                            <strong>{{ $errors->first('subcategory_id') }}</strong>
                        </p>
                        @endif
                    </div>
            </div>

            <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="title">Name <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('duration') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="duration">Duration <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    <div class="input-group mb-3">
                        {!! Form::text('duration', old('duration'), ['class' => 'form-control', 'placeholder' => 'Duration','data-error-container'=>"#duration-errors"]) !!}
                        <div class="input-group-append">
                          <span class="input-group-text">Hours</span>
                        </div>
                    </div>
                    <span id="duration-errors"></span>
                    @if($errors->has('duration'))
                    <strong for="duration" class="help-block">{{ $errors->first('duration') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('description') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="description">Description <span style="color:red"></span></label>
                 <div class="col-md-9">
                    {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Description', 'rows'=>'2']) !!}
                    @if($errors->has('description'))
                    <strong for="description" class="help-block">{{ $errors->first('description') }}</strong>
                    @endif
                </div>
            </div>

             <div class="form-group {{$errors->has('image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="image">Images </label>
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
        <div class="card-footer">
            <button type="submit" class="btn btn-responsive btn-primary btn-sm">{{ __('Submit') }}</button>
            <a href="{{route('admin.packages.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.container-fluid -->
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('admin-theme/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#frmPackage').validate({
        rules: {
            category_id:{
                required: true
            },
            subcategory_id:{
                required: true
            },
            title:{
                required: true
            },
            duration:{
                required: true,
                number:true
            }
        },
        errorPlacement: function (error, element) { // render error placement for each input type
            if (element.attr("data-error-container")) { 
                jQuery(element.attr("data-error-container")).html(error)
            }else{
                error.insertAfter(element); // for other inputs, just perform default behavior
            }
        }
    });
    $('select[name=category_id]').change(function() {
        var category_id = $(this).val();
        jQuery.post("{{ route('admin.packages.getSubCategories') }}",{'category_id':category_id},function(response){
            $('#subcategory_id').html('');
            $('#subcategory_id').html(response.subcategories);
        })
    }).trigger('change');
});
</script>
@endsection