@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Providers</h1>    
    <!-- Content Row -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
             <a href="{{route('admin.providers.create')}}" class="btn btn-primary btn-sm btn-icon-split float-right">
                <span class="icon text-white-50">
                  <i class="fas fa-plus"></i>
                </span>
                <span class="text">Add Provider</span>
            </a>
            <h6 class="m-0 font-weight-bold text-primary">Providers</h6>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0" id="users">
                    <thead>
                        <tr>
                          <th>Name</th>
                          <th>Email</th> 
                          <th>Mobile No.</th>                                               
                          <th>Profile</th>
                          <th>Created Date/Time</th>   
                          <th>Verified</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                          <th>Name</th>
                          <th>Email</th>   
                          <th>Mobile No.</th>                                             
                          <th>Profile</th>
                          <th>Created Date/Time</th>   
                          <th>Verified</th>
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
    getUsers();
});
 
function getUsers(){
    jQuery('#users').dataTable().fnDestroy();
    jQuery('#users tbody').empty();
    jQuery('#users').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.providers.getUsers') }}',
            method: 'POST'
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50,100,"All"]
        ],
        columns: [   
            {data: 'user_name', name: 'name'},
            {data: 'email', name: 'email'},
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'media.name', name: 'media.name', class: 'text-center', orderable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'is_verify', name: 'is_verify', class: 'text-center', "width": "5%"},
            {data: 'is_active', name: 'is_active', class: 'text-center', "width": "5%"},
            {data: 'action', name: 'action', orderable: false, searchable: false, "width": "10%"},
        ],
        order: [[0, 'desc']]
    });
}
</script>
@endsection