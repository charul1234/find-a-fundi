@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Advertisements</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
        {!! Form::open(['method' => 'POST','files'=>true,'route' => ['admin.advertisements.update',$advertisement->id],'class' => 'form-horizontal','id' => 'frmAdvertisement']) !!}
            @method('PUT')
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Edit Advertisement</h6>
        </div>
        <div class="card-body">           
        
        <div class="form-group {{$errors->has('page_name') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-9 control-label" for="page_name">Page Name <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('page_name', old('page_name',isset($advertisement->page_name)?$advertisement->page_name:''), ['class' => 'form-control', 'placeholder' => 'Page Name']) !!}
                    @if($errors->has('page_name'))
                    <strong for="page_name" class="help-block">{{ $errors->first('page_name') }}</strong>
                    @endif
                </div>
            </div>

            <div class="form-group {{$errors->has('section') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-9 control-label" for="section">Section <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::select('section', $sections, old('section',isset($advertisement->section)?$advertisement->section:''), ['id'=>'section', 'class' => 'form-control', 'placeholder' => 'Select Section']) !!}
                    @if($errors->has('section'))
                    <p class="help-block">
                        <strong>{{ $errors->first('section') }}</strong>
                    </p>
                    @endif
                </div>
            </div>

             <div class="form-group {{$errors->has('title') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-9 control-label" for="title">Title <span style="color:red">*</span></label>
                 <div class="col-md-9">
                    {!! Form::text('title', old('title',isset($advertisement->title)?$advertisement->title:''), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
                    @if($errors->has('title'))
                    <strong for="title" class="help-block">{{ $errors->first('title') }}</strong>
                    @endif
                </div>
            </div>

             <div class="form-group {{$errors->has('discription') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                <label class="col-md-3 control-label" for="address">Description </label>
                <div class="col-md-9">
                    {!! Form::textarea('discription',old('discription',isset($advertisement->discription)?$advertisement->discription:''), ['class' => 'form-control', 'placeholder' => 'Description','rows'=>'3']) !!}
                    @if($errors->has('discription'))
                    <strong for="discription" class="help-block">{{ $errors->first('discription') }}</strong>
                    @endif
                </div>
            </div>

             <div class="row col-md-9">                 
                    <div class="col-md-6 form-group {{$errors->has('start_date') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label for="start_date">Start Date <span style="color:red">*</span></label>
                        {!! Form::text('start_date', old('start_date'), ['id'=>'start_date', 'class' => 'form-control', 'placeholder' => 'Start Date']) !!}

                        @if($errors->has('start_date'))
                        <p class="help-block">
                            <strong>{{ $errors->first('start_date') }}</strong>
                        </p>
                        @endif
                    </div>   
                <div class="col-md-6 form-group {{$errors->has('end_date') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label for="end_date">End Date <span style="color:red">*</span></label>
                        {!! Form::text('end_date', old('end_date'), ['id'=>'end_date', 'class' => 'form-control', 'placeholder' => 'End Date']) !!}

                        @if($errors->has('end_date'))
                        <p class="help-block">
                            <strong>{{ $errors->first('end_date') }}</strong>
                        </p>
                        @endif
              
                     </div> 
                 </div>


                   @php $image_required = true; @endphp
                    @if(isset($advertisement) && $advertisement->getMedia('image')->count() > 0 && file_exists($advertisement->getFirstMedia('image')->getPath()))
                        @php $image_required = false; @endphp
                    <div class="col-md-2 form-group">
                        <img width="100%" src="{{ $advertisement->getFirstMedia('image')->getFullUrl() }}" />
                    </div>
                    @endif
                    <div class="col-md-9 form-group {{$errors->has('image') ? config('constants.ERROR_FORM_GROUP_CLASS') : ''}}">
                        <label for="image">Image File @if($image_required==true)<span style="color:red">*</span> @endif</label>
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
            <a href="{{route('admin.advertisements.index')}}"  class="btn btn-responsive btn-danger btn-sm">{{ __('Cancel') }}</a>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<!-- /.container-fluid -->
@endsection
@section('styles')
<link href="{{ asset('admin-theme/vendor/datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="{{ asset('admin-theme/vendor/jquery-ui/css/jquery-ui.css') }}" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('admin-theme/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}">
@endsection
@section('scripts')
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/jquery.validate.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-validation/dist/additional-methods.min.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/jquery-ui/js/jquery-ui.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('#frmAdvertisement').validate({
        rules: {
            page_name:{
                required: true
            },
            section:{
                required: true
            },
            title:{
                required: true
            },
            start_date:{
                required: true
            },
            end_date:{
                required: true
            },               
            @if($image_required==true)            
            image: {
                required: true
            },
            @endif
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
});
 $("#start_date").datepicker({
       format: 'mm/dd/yy',       
       autoclose: true,
       onSelect: function (selected) {
         $("#end_date").datepicker("option", "minDate", $("#start_date").val());
    }
 }).val("{{ old('start_date', (isset($advertisement->start_date) && $advertisement->start_date!="")?date('m/d/Y',strtotime($advertisement->start_date)):'') }}");  

 $("#end_date").datepicker({
       format: 'mm/dd/yy',
       //todayHighlight: true,
       autoclose: true,
 }).val("{{ old('end_date', (isset($advertisement->end_date) && $advertisement->end_date!="")?date('m/d/Y',strtotime($advertisement->end_date)):'') }}"); ;
</script>
@endsection