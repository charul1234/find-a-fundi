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
                    <div class="card mb-3">
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
                                    <label class="col-form-label"><strong>Request Budget : </strong>{{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->request_for_quote_budget)?$booking->request_for_quote_budget:'' }}</label>
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
                                  <div class="form-group">
                                    <label class="col-form-label"><strong>Category name: </strong>
                                     {{ isset($categoryname)?$categoryname:'' }}
                                    </label>
                                 </div>
                                  <div class="form-group">
                                    <label class="col-form-label"><strong>SubCategory name: </strong>
                                  <?php foreach ($subcategoryname as $key => $categoryname) {
                                    if($categoryname!='')
                                    {
                                     echo $categoryname.',';
                                    }
                                    
                                   } ?>
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
                        <?php if($booking->is_package==1 || $booking->is_hourly==1) { ?>
                         <div class="card mb-3">
                              <div class="card-header">Provider Information</div>
                                <div class="card-body">                             
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Name : </strong>
                                   {{ isset($providerdata->name)?$providerdata->name:'' }}
                                    </label>
                                   </div>
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Email : </strong>
                                   {{ isset($providerdata->email)?$providerdata->email:'' }}
                                    </label>
                                   </div>
                                </div>
                         </div>
                     <?php } ?>
                    <?php
                   if($booking->is_hourly==true || $booking->is_package==true )
                   {  
                     if($booking->is_quoted==true) 
                     { ?>
                    <div class="card mb-3">
                              <div class="card-header">Quoted</div>
                                <div class="card-body">
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Requirement : </strong>
                                   {{ isset($booking->requirement)?$booking->requirement:'' }}
                                    </label>
                                 </div>
                                 <div class="form-group">
                                    <label class="col-form-label"><strong>Budget : </strong>
                                   {{config('constants.DEFAULT_CURRENCY_SYMBOL')}}{{ isset($booking->budget)?$booking->budget:'' }}
                                    </label>
                                 </div>
                                  <div class="form-group">
                                    <label class="col-form-label"><strong>Service datetime : </strong>
                                    {{isset($booking->service_datetime)?date(config('constants.DATETIME_FORMAT'), strtotime($booking->service_datetime)):''}}
                                    </label>
                                 </div>

                                  </div>
                      </div> 
                    <?php }
                      } ?>
                    <?php if($booking->is_rfq==true)
                     {
                     
                      ?>
                        <div class="card mb-3">
                              <div class="card-header">Quoted</div>
                                <div class="card-body">
                       <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover" id="rfq">
                      <thead>
                        <tr>
                          <th scope="col">Provider</th>
                          <th scope="col">Datetime</th>
                          <th scope="col">Budget</th>
                          <th scope="col">Requirement</th>
                          <th scope="col">Status</th>
                        </tr>
                      </thead>
                      <tbody>

              <?php  
              if(count($booking_user)>0)
                { 
                 foreach ($booking_user as $key => $value) 
                 {  
                    ?>
                        <tr>
                          <td>{{isset($value->user->name)?$value->user->name:''}}</td>
                          <td>  {{isset($value->service_datetime)?date(config('constants.DATETIME_FORMAT'), strtotime($value->service_datetime)):''}}</td>
                          <td>{{isset($value->budget)?config('constants.DEFAULT_CURRENCY_SYMBOL').$value->budget:''}}</td>
                          <td>{{isset($value->requirement)?$value->requirement:''}}</td>
                          <td>{{isset($value->status)?ucwords($value->status):''}}</td>
                        </tr>     
                    <?php                
                 }   ?>                  
                 <?php 
                }   ?>
        

        </tbody></table>
              </div>  </div>  </div> 


                     <?php } ?>
                     <div class="card mb-3">
                              <div class="card-header">Seeker Information</div>
                                <div class="card-body">
                            
                                   <div class="form-group">
                                    <label class="col-form-label"><strong>Name : </strong>
                                   {{ isset($seekerdata->name)?$seekerdata->name:'' }}
                                    </label>
                                 </div>
                                  <div class="form-group">
                                    <label class="col-form-label"><strong>Email : </strong>
                                   {{ isset($seekerdata->email)?$seekerdata->email:'' }}
                                    </label>
                                 </div>
                                

                                </div>
                    </div>

                      </div>
                      <div class="col-md-12">
                        <?php if($booking->is_rfq==true || $booking->is_hourly==true) { ?>
                        <div class="card mb-3">
                            <div class="card-header">Job Schedules</div>
                                <div class="card-body">

                                      <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover" id="schedules">
                      <thead>
                        <tr>
                          <th scope="col">Date</th>
                          <th scope="col">Time</th>
                          <th scope="col">Service title</th>
                          <th scope="col">Requirement</th>
                          <th scope="col">Price</th>
                        </tr>
                      </thead>
                      <tbody>

              <?php  
              if(count($schedules)>0)
                { 
                 foreach ($schedules as $key => $schedule) 
                 {  
                    ?>
                        <tr>
                          <td>  {{isset($schedule->date)?date(config('constants.DATE_FORMAT'), strtotime($schedule->date)):''}}</td>
                          <td>  {{isset($schedule->start_time)?date(config('constants.TIME_FORMAT'), strtotime($schedule->start_time)):''}} - {{isset($schedule->end_time)?date(config('constants.TIME_FORMAT'), strtotime($schedule->end_time)):''}}</td>                          
                          <td>{{isset($schedule->service_title)?$schedule->service_title:''}}</td>
                          <td>{{isset($schedule->requirements)?$schedule->requirements:''}}</td>
                          <td>{{isset($schedule->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').($schedule->price):''}}</td>
                        </tr>     
                    <?php                
                 }   ?>                  
                 <?php 
                }   ?>
        

        </tbody></table>
              </div> 
            </div>
           </div>  
          <?php } ?>
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
<script type="text/javascript">
jQuery(document).ready(function(){        
     jQuery('#rfq').DataTable({
         responsive: true,
         pageLength: 5,
         lengthChange: false
     });
     jQuery('#schedules').DataTable({
         responsive: true,
         pageLength: 10,
         lengthChange: false
     });
   });
</script>

@endsection
