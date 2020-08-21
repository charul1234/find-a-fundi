@extends('admin.layouts.app')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Provider Information </h1>
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

              <div class="form-group">
                <label class="col-form-label"><strong>Category - Subcategory:</strong>
                  <?php
                      if(count($user->category_user)>0)
                        {
                          foreach ($user->category_user as $key => $providerdata) 
                          {
                            if($providerdata->category->parent_id==0)
                            {
                              echo isset($providerdata->category->title)?$providerdata->category->title:'';
                              echo " - "; 
                            }
                            if($providerdata->category->parent_id!=0)
                            {
                               if(isset($providerdata->category->title) && $providerdata->category->title!='')
                               {                              
                                  echo $subcategory_name= $providerdata->category->title; echo ",";
                               }    
                            }
                           
                         }  
                         
                        }  
                  ?>      
               </div>                        
              <div class="row">
                    <div class="col-md-12">
                       <div class="">
        <h6 class="ml-0 font-weight-bold text-primary">Declarations</h6>
        </div>
        </div>
          <div class="col-md-12">
         <div class="form-group">
                        <label class="col-form-label"><strong>I am not middlemen :  </strong>
                       <?php 
                       if($user->profile->fundi_is_middlemen == TRUE ){
                        ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                        <?php 
                       }
                        ?> </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>I have all the required tools to do their job : </strong>
                       <?php 
                       if($user->profile->fundi_have_tools == TRUE ){
                        ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                        <?php 
                       }
                        ?> </label>
                    </div>
                       <div class="form-group">
                        <label class="col-form-label"><strong>I have a smartphone : </strong>
                       <?php 
                       if($user->profile->fundi_have_smartphone == TRUE ){
                      ?><i class="fa badge-success fa-check" aria-hidden="true"></i>
                      <?php
                       }
                        ?> </label>
                    </div>

                     <div class="form-group">
                 <label class="col-form-label"><strong>Service Type : </strong></label>
        <?php 
                       if($user->profile->is_rfq == TRUE ){
                         ?>
                        <label class="col-form-label">Request for Quote 
                       <i class="fa badge-success fa-check" aria-hidden="true"></i>
                        </label> <?php
                       }
                       if($user->profile->is_package == TRUE ){
                         ?>
                        <label class="col-form-label">Package 
                       <i class="fa badge-success fa-check" aria-hidden="true"></i>
                        </label> <?php
                       }
                        ?>
                    </div>
</div>
                   
                 </div>                          
                    
                      </div>
                      <div class="col-md-6">
                         <div class="form-group">
                        <label class="col-form-label"><strong>Address :</strong> <?php echo isset($user->profile->work_address)?ucwords($user->profile->work_address):'';?>   </label>
                    </div>  
                    <div class="form-group">
                        <label class="col-form-label"><strong>Radius : </strong> <?php echo isset($user->profile->radius)?ucwords($user->profile->radius):'';?>   </label>
                    </div> 
                    <div class="form-group">
                        <label class="col-form-label"><strong>DOB : </strong>{{ isset($user->profile->dob)?date(config('constants.DATE_FORMAT'),strtotime($user->profile->dob)):'' }} </label>
                    </div>
                       <div class="form-group">
                      <!--   <label class="col-form-label"><strong>Experience Level :  </strong>{{ isset($user->profile->experience_level->title)?ucwords($user->profile->experience_level->title):'' }} </label> -->
                       <label class="col-form-label"><strong>Year experience :  </strong>{{ isset($user->profile->year_experience)?ucwords($user->profile->year_experience):'' }} </label> 
                    </div> 
                    <!--  <div class="form-group">
                        <label class="col-form-label"><strong>Payment Option : </strong> {{ isset($user->profile->payment_option->title)?ucwords($user->profile->payment_option->title):'' }} </label>
                    </div>  -->
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
                        <label class="col-form-label"><strong>Passport Number :  </strong><?php echo isset($user->profile->passport_number)?ucwords($user->profile->passport_number):'';?>   </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>Registered Date Time :  </strong>{{ date(config('constants.DATETIME_FORMAT'),strtotime($user->created_at)) }} </label>
                    </div>
                      <h6 class="ml-0 font-weight-bold text-primary">Social Url</h6>
                       <div class="form-group">
                          <label class="col-form-label"><strong>Facebook :  </strong><?php 
                          if(isset($user->profile->facebook_url))
                          {
                             echo "<a href=".$user->profile->facebook_url." target='_blank'>".$user->profile->facebook_url."</a>";
                          }
                          ?></label>
                       </div>
                       <div class="form-group">
                          <label class="col-form-label"><strong>Instagram :  </strong><?php 
                          if(isset($user->profile->instagram_url))
                          {
                             echo "<a href=".$user->profile->instagram_url." target='_blank'>".$user->profile->instagram_url."</a>";
                          }
                          ?></label>
                       </div>
                       <div class="form-group">
                          <label class="col-form-label"><strong>Twitter :  </strong><?php 
                          if(isset($user->profile->twitter_url))
                          {
                             echo "<a href=".$user->profile->twitter_url." target='_blank'>".$user->profile->twitter_url."</a>";
                          }


                          ?></label>
                       </div>
                   </div>

                 </div>      
                

                  <div class="row">
                      <div class="col-md-6">

                    <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Company</h6>
        </div>
                  <div class="table-responsive">
                     <table class="table table-striped table-bordered table-hover" id="company_table">
                      <thead>
                        <tr>
                          <th scope="col">Name</th>
                          <th scope="col">Logo</th>
                          <th scope="col">Document</th>
                          <th scope="col">Remarks</th>
                          <th scope="col">Document Number</th>
                          <th scope="col">Status</th>
                        </tr>
                      </thead>
                      <tbody>
          <?php 
     if(isset($providerCompanies) && count($providerCompanies)>0)
                {?>
              <?php  
                 foreach ($providerCompanies as $key => $value) {
                    ?>
                        <tr>
                          <td> {{isset($value->name)?$value->name:''}}</td>
                           <td>@if(isset($value) && $value->getMedia('company_logo')->count() > 0 && file_exists($value->getFirstMedia('company_logo')->getPath()))  
                       <img width="50" src="{{ $value->getFirstMedia('company_logo')->getFullUrl() }}" />
                      
                    </div>
                    @endif</td>

                          <td>@if(isset($value) && $value->getMedia('document_image')->count() > 0 && file_exists($value->getFirstMedia('document_image')->getPath()))  
                       <img width="50" src="{{ $value->getFirstMedia('document_image')->getFullUrl() }}" />
                       <a download href="{{ $value->getFirstMedia('document_image')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                    @endif</td>
                          <td>{{isset($value->remarks)?$value->remarks:''}}</td>
                          <td>{{isset($value->document_number)?$value->document_number:''}}</td>
                          <td>
                           <?php 
                       if($value->is_active == TRUE ){                        
                         echo "<a href='".route('admin.providers.company_status',[$value->is_active,$value->id,$value->user_id])."'><span class='badge badge-success'>Active</span></a>";
                       }else
                       {
                        echo "<a href='".route('admin.providers.company_status',[$value->is_active,$value->id,$value->user_id])."'><span class='badge badge-danger'>Inactive</span></a>";
                       }
                        ?></td>
                        </tr>     
                    <?php
               
                 }   ?><?php 
                }   ?></tbody>
                  </table>                 
              </div>  
              </div>
             
            <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Packages</h6>
        </div>
         <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover" id="packages_table">
                    <thead>
                        <tr>                
                           <th>Package name</th>
                           <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
     <?php  if($user->profile->is_package == TRUE)
            {   ?>
     <?php  
              if(isset($user->package_user))
                {                  
                 foreach ($user->package_user as $key => $value) {
                    ?>
                        <tr>
                          <td><?php  $package_name = App\Package::where(['id'=>$value->package_id])->first();
                          echo isset($package_name->title)?$package_name->title:'';
                         ?></td>
                          <td>{{isset($value->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').$value->price:''}}</td>
                        </tr>     
                    <?php                
                 }   ?>
                 
                 <?php 
                }   ?>    
      <?php } ?>        
            </tbody>
          </table>
 </div>   
              </div>

<h6 class="ml-0 font-weight-bold text-primary">Works photo</h6>
<div class="row">
  <?php if(isset($works_photo) && !empty($works_photo))
  {
    $i = 1;
    foreach ($works_photo as $key => $photo) {
      ?>
     <div class="col-md-2 mb-4">
      <div type="button"  data-toggle="modal" data-target="#myModal-<?php echo $i; // Displaying the increment ?>">
          <img width="70" height="70" src="{{ $photo->getFullUrl() }}" />
      </div>
     </div>

  

<!-- The Modal -->
<div class="modal" id="myModal-<?php echo $i; // Displaying the increment ?>">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <img width="100%" height="100%" src="{{ $photo->getFullUrl() }}" />
      </div>

      <!-- Modal footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>
    <?php $i++; }
  } ?>
  
</div>

               </div><div class="col-md-6">                     

         
            <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Hourly Charges</h6>
        </div>
                  <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover" id="hourly_table">
                      <thead>
                        <tr>
                          <th scope="col">Hours</th>
                          <th scope="col">Price</th>
                          <th scope="col">Type</th>
                        </tr>
                      </thead>
                      <tbody>
    <?php  if($user->profile->is_hourly == TRUE){      ?>
              <?php 
              if(isset($user->hourly_charge))
                { 
                 foreach ($user->hourly_charge as $key => $value) 
                 {
                    ?>
                        <tr>
                          <td>{{isset($value->hours)?$value->hours:''}}</td>
                          <td>{{isset($value->price)?config('constants.DEFAULT_CURRENCY_SYMBOL').$value->price:''}}</td>
                          <td>{{isset($value->type)?$value->type:''}}</td>
                        </tr>     
                    <?php                
                 }   ?>                  
                 <?php 
                }   ?>
          <?php } ?>  

        </tbody></table>
              </div> </div> 
                 
        

             <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Certifications</h6>
        </div>
                  <div class="table-responsive">
                     <table class="table table-striped table-bordered table-hover" id="certification_table">
                      <thead>
                        <tr>
                          <th scope="col">Title</th>
                          <th scope="col">Type</th>
                          <th scope="col">Document</th>
                        </tr>
                      </thead>
                      <tbody>
          <?php 
     if(isset($providerCertifications) && count($providerCertifications)>0)
                {?>
              <?php  
                 foreach ($providerCertifications as $key => $value) {
                    ?>
                        <tr>
                          <td> {{isset($value->title)?ucwords($value->title):''}}</td>
                          <td>{{isset($value->type)?ucwords($value->type):''}}</td>
                          <td>
                           <?php if($value->type=='certification'){ ?>
                          @if(isset($value) && $value->getMedia('certification')->count() > 0 && file_exists($value->getFirstMedia('certification')->getPath()))                      
                               <a download href="{{ $value->getFirstMedia('certification')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>                    
                          @endif

                          <?php }elseif ($value->type=='diploma') {?>
                              @if(isset($value) && $value->getMedia('diploma')->count() > 0 && file_exists($value->getFirstMedia('diploma')->getPath()))                      
                                   <a download href="{{ $value->getFirstMedia('diploma')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>                    
                          @endif
                           
                          <?php }elseif ($value->type=='degree') {?>
                           @if(isset($value) && $value->getMedia('degree')->count() > 0 && file_exists($value->getFirstMedia('degree')->getPath()))                      
                                   <a download href="{{ $value->getFirstMedia('degree')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>                    
                            @endif
                                 
                          <?php      } ?>
                         </td>
                        </tr>     
                    <?php
               
                 }   ?><?php 
                }   ?></tbody>
                  </table>                 
              </div>  
              </div></div>
                     
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
@endsection
