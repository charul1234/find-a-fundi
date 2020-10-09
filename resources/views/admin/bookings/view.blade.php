@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Booking Information ( {{ isset($booking->title)?$booking->title:'' }} )</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
<div class="card-header py-3">
                    <a href="{{route('admin.bookings.index')}}" class="btn btn-danger btn-sm btn-icon-split float-right">
                        <span class="icon text-white-50">
                          <i class="fas fa-arrow-left"></i>
                        </span>
                        <span class="text">Back</span>
                    </a>
                    <h6 class="m-0 font-weight-bold text-primary">Booking Details</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                      <div class="col-md-6"> 
                    <div class="card">
                              <div class="card-header">Booking Information</div>
                                <div class="card-body">
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Title : </strong>{{ isset($booking->title)?$booking->title:'' }}</label>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Description : </strong>{{ isset($booking->description)?$booking->description:'' }}</label>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Location : </strong>{{ isset($booking->location)?$booking->location:'' }}</label>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Job Type : </strong>{{ isset($job_type)?$job_type:'' }}</label>
                                </div>
                                <?php if($booking->is_rfq==1 || $booking->is_package==1) { ?>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Budget : </strong>{{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->budget)?$booking->budget:'' }}</label>
                                </div>
                                <?php } ?>
                                <?php if($booking->is_hourly==1) { ?>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Estimated Hour : </strong>{{ isset($booking->estimated_hours)?$booking->estimated_hours:'' }}</label>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Min Budget : </strong>{{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->min_budget)?$booking->min_budget:'' }}</label>
                                </div>
                                <div class="form-group">
                                    <label class="col-form-label"><strong>Max Budget : </strong>{{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->max_budget)?$booking->max_budget:'' }}</label>
                                </div>
                                <?php } ?>

                                 <?php if($booking->is_package==1) { ?>
                                 <div class="form-group">
                                    <label class="col-form-label"><strong>Quantity : </strong>{{ isset($booking->quantity)?$booking->quantity:'' }}</label>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-form-label"><strong>Total Package Amount : </strong>{{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->total_package_amount)?$booking->total_package_amount:'' }}</label>
                                 </div>

                                 <?php } ?>
                                  <div class="form-group">
                                    <label class="col-form-label"><strong>Datetime : </strong>
                                    {{date(config('constants.DATETIME_FORMAT'), strtotime($booking->datetime))}}
                                    </label>
                                 </div>


                                </div>
                    </div>  

                   <!--  <div class="card">
                              <div class="card-header">Booking Information</div>
                                <div class="card-body">

                                </div>
                    </div> -->          


                        

                    

                      </div>
                      <div class="col-md-6"> 
                         <div class="card">
                              <div class="card-header">Provider Information</div>
                                <div class="card-body">
                               <?php if($booking->is_package==1 || $booking->is_hourly==1) { ?>
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Provider name : </strong>
                                   
                                    </label>
                                 </div>
                                 <?php } ?>

                                </div>
                    </div>

                      </div>
                  </div>

                </div></div>
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
@endsection
