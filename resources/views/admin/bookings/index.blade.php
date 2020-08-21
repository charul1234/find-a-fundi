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
                  <?php $job_types=array('is_rfq'=>'RFQ','is_hourly'=>'Hourly'); ?>
                    {!! Form::select('job_type',$job_types, old('job_type'), ['class' => 'form-control','placeholder'=> __('Select Job Type')]) !!}                    
                </div>   

                <button type="submit" class="btn btn-responsive btn-primary mr-sm-2 mb-2">{{ __('Filter') }}</button>
                <a href="javascript:;" onclick="resetFilter();" class="btn btn-responsive btn-danger mb-2">{{ __('Reset') }}</a>
                {!! Form::close() !!}
            </div>
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="bookings">
                    <thead>
                        <tr>                          
                          <th>Title</th>  
                          <th>Description</th>   
                          <th>Datetime</th>
                          <th>Location</th>   
                          <th>Type</th>  
                          <th>RFQ Budget</th> 
                          <th>Hourly Budget</th>
                          <th>Estimated hour</th>
                          <th>Quoted</th>
                          <th>Seeker</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>                          
                          <th>Title</th>  
                          <th>Description</th>   
                          <th>Datetime</th>
                          <th>Location</th>   
                          <th>Type</th>  
                          <th>RFQ Budget</th> 
                          <th>Hourly Budget</th>
                          <th>Estimated hour</th>
                          <th>Quoted</th>
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
@endsection

@section('scripts')
<!-- Page level plugins -->
<script src="{{ asset('admin-theme/vendor/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin-theme/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
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
    jQuery('#bookings').dataTable().fnDestroy(); 
    jQuery('#bookings tbody').empty();
    jQuery('#bookings').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.bookings.getBookings') }}',
            method: 'POST',
            data : {
              job_type : job_type
            }
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50,100,"All"]
        ],
        columns: [
            {data: 'title', name: 'title'}, 
            {data: 'description', name: 'description'}, 
            {data: 'datetime', name: 'datetime'},
            {data: 'location', name: 'location'},
            {data: 'type', name: 'type'},            
            {data: 'request_for_quote_budget', name: 'request_for_quote_budget'},
            {data: 'hourly_budget', name: 'hourly_budget'},
            {data: 'estimated_hours', name: 'estimated_hours'},
            {data: 'is_quoted', name: 'is_quoted'},
            {data: 'requested_id', name: 'requested_id'},
            {data: 'action', name: 'action', orderable: false, searchable: false, "width": "10%"},
        ],
        order: [[0, 'desc']]
    });
}
</script>
@endsection