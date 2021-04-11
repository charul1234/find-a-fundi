@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Bookings</h1>    
    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">            
            <h6 class="m-0 font-weight-bold text-primary">Bookings</h6>
        </div>

        <div class="card-body">
          <div class="well mb-3">
                {!! Form::open(['method' => 'POST', 'class' => 'form-inline', 'id' => 'frmFilter']) !!}
                <div class="form-group mr-sm-2 mb-2">
                  <?php $job_types=array('is_rfq'=>'RFQ','is_hourly'=>'Hourly','is_package'=>'Package'); ?>
                    {!! Form::select('job_type',$job_types, old('job_type'), ['class' => 'form-control','placeholder'=> __('Select Job Type')]) !!}                    
                </div>   
                <div class="form-group mr-sm-2 mb-2">
                  <?php $job_status=array('requested'=>'Requested','accepted'=>'Accepted','quoted'=>'Quoted','declined'=>'Declined','completed'=>'Completed'); ?>
                    {!! Form::select('job_status',$job_status, old('job_status'), ['class' => 'form-control','placeholder'=> __('Select Status')]) !!}                    
                </div>  
                 <div class="form-group mr-sm-2 mb-2">
                    {!! Form::text('date_range', old('date_range'), ['class' => 'form-control col-12','placeholder'=> __('DD/MM/YYYY - DD/MM/YYYY'), 'autocomplete'=>'off']) !!}                    
                </div> 

                <button type="submit" class="btn btn-responsive btn-primary mr-sm-2 mb-2">{{ __('Filter') }}</button>
                <a href="javascript:;" onclick="resetFilter();" class="btn btn-responsive btn-danger mb-2">{{ __('Reset') }}</a>
                {!! Form::close() !!}
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="bookings">
                    <thead>
                        <tr>  
                          <th>Provider</th>                          
                          <th>Title</th>     
                          <th>Datetime</th>
                          <th>Location</th>   
                          <th>Type</th>  
                          <th>Status</th>
                          <th>RFQ Budget</th> 
                          <th>Hourly Budget</th>
                          <th>Estimated hour</th>
                          <th>Seeker</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                          <th>Provider</th>                          
                          <th>Title</th>    
                          <th>Datetime</th>
                          <th>Location</th>   
                          <th>Type</th>  
                          <th>Status</th>
                          <th>RFQ Budget</th> 
                          <th>Hourly Budget</th>
                          <th>Estimated hour</th>
                          <th>Seeker</th>
                          <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
@endsection

@section('styles')
<!-- Custom styles for this page -->
<link href="{{ asset('admin-theme/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link href="{{ asset('admin-theme/vendor/daterangepicker/daterangepicker.css') }}" rel="stylesheet">
@endsection

@section('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-theme/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/moment/js/moment.min.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/daterangepicker/daterangepicker.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){

  $('[name=date_range]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    }); 
    jQuery('[name=date_range]').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });
    jQuery('[name=date_range]').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
    });
    getBookings();
    jQuery('#frmFilter').submit(function(){
        getBookings();
        return false;
    });
});
function resetFilter(){
        jQuery('#frmFilter :input:not(:button, [type="hidden"])').val('');
        getBookings();
    }
 
function getBookings(){
    var job_type = jQuery('#frmFilter [name=job_type]').val();
    var job_status = jQuery('#frmFilter [name=job_status]').val();
    var date_range = jQuery('#frmFilter [name=date_range]').val();
    jQuery('#bookings').dataTable().fnDestroy(); 
    jQuery('#bookings tbody').empty();
    jQuery('#bookings').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.bookings.getBookings') }}',
            method: 'POST',
            data : {
              job_type : job_type,
              job_status : job_status,
              date_range: date_range
            }
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50,100,"All"]
        ],
        columns: [
            {data: 'user_id', name: 'user_id'}, 
            {data: 'title', name: 'title'},  
            {data: 'datetime', name: 'datetime'},
            {data: 'location', name: 'location'},
            {data: 'type', name: 'type'},    
            {data: 'job_status', name: 'job_status'},         
            {data: 'request_for_quote_budget', name: 'request_for_quote_budget'},
            {data: 'hourly_budget', name: 'hourly_budget'},
            {data: 'estimated_hours', name: 'estimated_hours'},
            {data: 'requested_id', name: 'requested_id'},
            {data: 'action', name: 'action', orderable: false, searchable: false, "width": "5%"},
        ],
        order: [[0, 'desc']]
    });
}
</script>
@endsection