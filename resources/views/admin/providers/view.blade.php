@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Provider Information ( {{ isset($user->name)?ucwords($user->name):'' }} )</h1>
    <!-- Content Row -->
    <div class="card shadow mb-4">
<div class="card-header py-3">
                    <a href="{{route('admin.providers.index')}}" class="btn btn-danger btn-sm btn-icon-split float-right">
                        <span class="icon text-white-50">
                          <i class="fas fa-arrow-left"></i>
                        </span>
                        <span class="text">Back</span>
                    </a>
                    <h6 class="m-0 font-weight-bold text-primary">Provider Details</h6>
                </div>
                <div class="card-body">

                <div class="row">
                      <div class="col-md-6">
                         <div class="form-group">
                        <label class="col-form-label"><strong>Full Name : </strong>{{ isset($user->name)?$user->name:'' }}</label>
                    </div>
                     <div class="form-group">
                        <label class="col-form-label"><strong>Mobile Number :</strong> {{ isset($user->mobile_number)?$user->mobile_number:'' }}</label>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label"><strong>Email :</strong> {{ isset($user->email)?$user->email:'' }}</label>
                    </div>          
                      <div class="row">
                    <div class="col-md-12">
                       <div class="">
        <h6 class="ml-0 font-weight-bold text-primary">Declarations</h6>
        </div>
        </div>
          <div class="col-md-12">
         <div class="form-group">
                        <label class="col-form-label"><strong>They are not middlemen :  </strong>
                       <?php 
                       if($user->profile->fundi_is_middlemen == TRUE ){
                        ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                        <?php 
                       }
                        ?> </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>They have all the required tools to do their job : </strong>
                       <?php 
                       if($user->profile->fundi_have_tools == TRUE ){
                        ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                        <?php 
                       }
                        ?> </label>
                    </div>
                       <div class="form-group">
                        <label class="col-form-label"><strong>They have a smartphone : </strong>
                       <?php 
                       if($user->profile->fundi_have_smartphone == TRUE ){
                      ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                      <?php
                       }
                        ?> </label>
                    </div>
</div>
                   
                 </div>                          
                    
                      </div>
                      <div class="col-md-6">
                         <div class="form-group">
                        <label class="col-form-label"><strong>Address :</strong> <?php echo isset($user->profile->work_address)?ucwords($user->profile->work_address):'';?>   </label>
                    </div>   
                    <div class="form-group">
                        <label class="col-form-label"><strong>DOB : </strong>{{ isset($user->profile->dob)?date(config('constants.DATE_FORMAT'),strtotime($user->profile->dob)):'' }} </label>
                    </div>
                       <div class="form-group">
                        <label class="col-form-label"><strong>Experience Level :  </strong>{{ isset($user->profile->experience_level->title)?ucwords($user->profile->experience_level->title):'' }} </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>Payment Option : </strong> {{ isset($user->profile->payment_option->title)?ucwords($user->profile->payment_option->title):'' }} </label>
                    </div> 
                   <!--  <div class="form-group">
                        <label class="col-form-label">Additional Work : 
                       <?php 
                     /*  if($user->profile->additional_work == TRUE ){
                        echo 'Yes';
                       }else
                       {
                        echo 'No';
                       }*/
                        ?> </label>
                    </div> --> 

                    <!-- <div class="form-group">
                        <label class="col-form-label">Price : {{isset($user->profile->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').ucwords($user->profile->price):''}} </label>
                    </div>  -->
                    <div class="form-group">
                        <label class="col-form-label"><strong>Radius : </strong> <?php echo isset($user->profile->radius)?ucwords($user->profile->radius):'';?>   </label>
                    </div> 
                    <div class="form-group">
                        <label class="col-form-label"><strong>Passport Number :  </strong><?php echo isset($user->profile->passport_number)?ucwords($user->profile->passport_number):'';?>   </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>Created DateTime :  </strong>{{ date(config('constants.DATETIME_FORMAT'),strtotime($user->created_at)) }} </label>
                    </div>
                      </div>
                 </div>      
                

                  <div class="row">
                      <div class="col-md-6">

                  <?php if($user->profile->is_package == TRUE && $user->profile->is_rfq == FALSE ){ ?>      
                   <div class="table-responsive">
               <!-- <table class="table table-bordered" width="100%" cellspacing="0" id="users">
                    <thead>
                        <tr>                  
                          <th>price</th>
                           <th>email</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr> 
                          <th>price</th>
                          <th>email</th>
                        </tr>
                    </tfoot>
                </table>  -->
                <?php } ?>
                   <?php  if($user->profile->is_package == TRUE && $user->profile->is_rfq == FALSE ){ 
               
   ?>
            <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Packages</h6>
        </div>
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Package Name</th>
                          <th scope="col">Price</th>
                        </tr>
                      </thead>
                      <tbody>
              <?php  
              if(isset($user->package_user))
                {
                  $package_counter=1;
                 foreach ($user->package_user as $key => $value) {
                    ?>
                        <tr>
                          <th scope="row"><?php echo $package_counter;?></th>
                          <td><?php  $package_name = App\Package::where(['id'=>$value->package_id])->first();
                          echo isset($package_name->title)?$package_name->title:'';
                         ?></td>
                          <td>{{isset($value->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').$value->price:''}}</td>
                        </tr>     
                    <?php
                 $package_counter++;
                 }   ?>
                 
                 <?php 
                }   ?>             
           </table>
              </div>
            </div>    
                <?php } ?>
                                          
                   
                    
                      </div> 
         

                      <div class="col-md-6"> 
                     

  
                  <!--   <div class="form-group">
                        <label class="col-form-label">Package : 
                       <?php 
                      /* if($user->profile->is_package == TRUE ){
                        echo 'Yes';
                       }else
                       {
                        echo 'No';
                       }*/
                        ?> </label>
                    </div> -->
                    
                   <!--  <div class="form-group">
                        <label class="col-form-label">Hourly : 
                       <?php 
                      /* if($user->profile->is_hourly == TRUE ){
                        echo 'Yes';
                       }else
                       {
                        echo 'No';
                       }*/
                        ?> </label>
                    </div> --> 
                  <?php  if($user->profile->is_hourly == TRUE  && $user->profile->is_rfq == FALSE ){ 
                ?>
            <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Hourly Charges</h6>
        </div>
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Hours</th>
                          <th scope="col">Price</th>
                          <th scope="col">Type</th>
                        </tr>
                      </thead>
                      <tbody>
              <?php 
              if(isset($user->hourly_charge))
                { $hourly_counter=1;
                 foreach ($user->hourly_charge as $key => $value) {
                    ?>
                        <tr>
                          <th scope="row"><?php echo $hourly_counter;?></th>
                          <td>{{isset($value->hours)?$value->hours:''}}</td>
                          <td>{{isset($value->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').$value->price:''}}</td>
                          <td>{{isset($value->type)?$value->type:''}}</td>
                        </tr>     
                    <?php
                 $hourly_counter++;
                 }   ?>
                  
                 <?php 
                }   ?></table>
              </div> 
                 
        <?php } ?>  


                      </div>

                       <input type="hidden" name="provider_id" id="provider_id" value="<?php echo isset($id)?$id:'' ?>"> 
                  </div>

                  <div class="row">
                      <div class="col-md-6">
                     
            <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Company</h6>
        </div>
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">#</th>
                          <th scope="col">Name</th>
                          <th scope="col">Remarks</th>
                          <th scope="col">Document Number</th>
                          <th scope="col">Payment Received</th>
                        </tr>
                      </thead>
                      <tbody>
          <?php
     if(isset($user->company) && count($user->company)>0)
                {?>
              <?php  $company_counter=1;
                 foreach ($user->company as $key => $value) {
                    ?>
                        <tr>
                          <th scope="row"><?php echo $company_counter;?></th>
                          <td>{{isset($value->name)?$value->name:''}}</td>
                          <td>{{isset($value->remarks)?$value->remarks:''}}</td>
                          <td>{{isset($value->document_number)?$value->document_number:''}}</td>
                          <td>
                           <?php 
                       if($value->is_payment_received == TRUE ){                        
                         /*echo "<a href='".route('admin.providers.payment_received',$value->is_payment_received)."'><span class='badge badge-success'>Received</span></a>";*/
                         echo "Yes";
                       }else
                       {
                        /*echo "<a href='".route('admin.providers.payment_received',$value->is_payment_received)."'><span class='badge badge-danger'>Not Received</span></a>";*/
                        echo "No";
                       }
                        ?></td>
                        </tr>     
                    <?php
                 $company_counter++;
                 }   ?><?php 
                }   ?>
                  </table>
              </div> </div>
                      <div class="col-md-6">
                          <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Request For Quotation <?php 
                      if($user->profile->is_rfq == TRUE ){
                         ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                        <?php
                       }
                        ?></h6>
              </div>    
                      </div>
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
 
/*function getUsers(){
    jQuery('#users').dataTable().fnDestroy();
    var provider_id=jQuery('#provider_id').val();
    
    jQuery('#users tbody').empty();
    jQuery('#users').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: '{{ route('admin.providers.getUsersPackage') }}',
            method: 'POST',
            data: {    
                   provider_id:provider_id            
                }
        },
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50,100,"All"]
        ],
        columns: [             
            {data: 'package_user.price', name: 'package_user.price'},
            {data: 'package_user.package_id', name: 'package_user.package_id'}
        ]
    });
}*/
</script>
@endsection
