@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Advertisements</h1>    
    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <a href="{{route('admin.advertisements.create')}}" class="btn btn-primary btn-sm btn-icon-split float-right">
                <span class="icon text-white-50">
                  <i class="fas fa-plus"></i>
                </span>
                <span class="text">Add Advertisement</span>
            </a>
            <h6 class="m-0 font-weight-bold text-primary">Advertisements</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="advertisements">
                    <thead>
                        <tr>  
                            <th>Page Name</th>
                            <th>Section</th>                        
                            <th>Title</th> 
                            <th>Start Date</th>
                            <th>End Date</th> 
                            <th>Image</th> 
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>                          
                            <th>Page Name</th>
                            <th>Section</th>                        
                            <th>Title</th> 
                            <th>Start Date</th>
                            <th>End Date</th> 
                            <th>Image</th> 
                            <th>Status</th>
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
    getAdvertisements();
});
 
function getAdvertisements(){
    jQuery('#advertisements').dataTable().fnDestroy();
    jQuery('#advertisements tbody').empty();
    jQuery('#advertisements').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.advertisements.getAdvertisements') }}',
            method: 'POST'
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50,100,"All"]
        ],
        columns: [
            {data: 'page_name', name: 'page_name'},
            {data: 'section', name: 'section'},
            {data: 'title', name: 'title'},   
            {data: 'start_date', name: 'start_date'},
            {data: 'end_date', name: 'end_date'},        
            {data: 'media.name', name: 'media.name'}, 
            {data: 'is_active', name: 'is_active', class: 'text-center', "width": "10%"},
            {data: 'action', name: 'action', orderable: false, searchable: false, "width": "15%"},
        ],
        order: [[0, 'desc']]
    });
}
</script>
@endsection