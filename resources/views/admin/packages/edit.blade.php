@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Packages</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.packages.update',$package->id],'class' => 'form-horizontal','id' => 'frmPackage']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Package</h6>
        </div>
        <div class="card-body">           

            <!-- <div class="form-group {{$errors->has('category_id') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="category_id">Category <span style="color:red">*</span></label>
                <div class="col-md-9">
                    {!! Form::select('category_id', $categories, old('category_id',$package->category_id), ['id'=>'category_id', 'class' => 'form-control', 'placeholder' => 'Select Category']) !!}
                    @if($errors->has('category_id'))
                    <strong for="category_id" class="help-block">{{ $errors->first('category_id') }}</strong>
                    @endif
                </div>
            </div> -->

            <div class="form-group {{$errors->has('category_id') ? ' has-error' : ''}}">
                    <label class="col-md-3 control-label" for="destination_id">Category <span style="color:red">*</span></label>
                    <div class="col-md-9">
                        {!! Form::select('category_id', $categories, old('category_id',isset($parent_id)?$parent_id:''), ['id'=>'category_id', 'class' => 'form-control', 'placeholder' => 'Select Category']) !!}

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
                        {!! Form::select('subcategory_id', [], old('subcategory_id',isset($package->category_id)?$package->category_id:''), ['id'=>'subcategory_id', 'class' => 'form-control', 'placeholder' => 'Select Sub Category']) !!}

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
                    {!! Form::text('title', old('title',$package->title), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('duration') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="duration">Duration <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    <div class="input-group mb-3">
                        {!! Form::text('duration', old('duration',$package->duration), ['class' => 'form-control', 'placeholder' => 'Duration','data-error-container'=>"#duration-errors"]) !!}
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
                    {!! Form::textarea('description', old('description',$package->description), ['class' => 'form-control', 'placeholder' => 'Description', 'rows'=>'2']) !!}
                    @if($errors->has('description'))
                    <strong for="description" class="help-block">{{ $errors->first('description') }}</strong>
                    @endif
                </div>
            </div>


             @php $image_required = true; @endphp
                @if(isset($package) && $package->getMedia('image')->count() > 0 && file_exists($package->getFirstMedia('image')->getPath()))
                 @php $image_required = false; @endphp
                
                <div class="col-md-12 row form-group">
                    <?php $images=$package->getMedia('image');
                 if (count($images) > 0) 
                    {
                        foreach ($images as $media) { ?>
                        <input type="hidden" name="all_media_id[]" id="all_media_id" value="<?php echo isset($media->id)?$media->id:'';?>" >
                         <div class="col-md-1 mb-3">
                        <a href="javascript:void(0);" class="remove_button" title="Remove"> <i class="fa text-danger fa-minus-circle" aria-hidden="true"></i></a>
                        <input type="hidden" name="media_id[]" id="media_id" value="<?php echo isset($media->id)?$media->id:'';?>" >
                         <img width="60" height="60" src="{{ $media->getFullUrl() }}" />
                     </div>
                        <?php                                           
                         }
                    }
                ?>
                </div>
            @endif          

            <div class="form-group {{$errors->has('image') ? ' has-error' : ''}}">
                    <label class="col-md-12 control-label" for="image">Images 
                    </label>
                    <div class="col-md-12">
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
$(wrapper).on('click', '.remove_button', function(e){ //Once remove button is clicked
            if (!confirm("Do you want to delete")){
              return false;
            }
            e.preventDefault();
            $(this).parent('div').remove(); //Remove field html
            x--; //Decremen t field counter
        });
$('select[name=category_id]').change(function() {
        var category_id = $(this).val();
        var subcategory_id = '<?php echo isset($package->category_id)?$package->category_id:0?>';
        jQuery.post("{{ route('admin.packages.getSubCategories') }}",{'category_id':category_id,'subcategory_id':subcategory_id},function(response){
            $('#subcategory_id').html('');
            $('#subcategory_id').html(response.subcategories);
        })
    }).trigger('change');
});
</script>
@endsection