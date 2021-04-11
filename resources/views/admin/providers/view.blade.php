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
 <div class="card card-information mb-3">
     <div class="alert alert-secondary col-md-12" role="alert"><div class="row"><div class="col-md-5">    
                   </div><div class="col-md-7"> <div class="text-left font-weight-bold">Personal Information</div></div></div>
</div>
  <div class="row ml-3">
      <div class="col-md-5">
                    <div class="form-group">
                        <label class="col-form-label"><strong>Full Name : </strong>{{ isset($user->name)?ucwords($user->name):'' }}</label>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label"><strong>Mobile Number :</strong> {{ isset($user->mobile_number)?$user->mobile_number:'' }}</label>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label"><strong>Email :</strong> {{ isset($user->email)?$user->email:'' }}</label>
                    </div> 
                     <div class="form-group">
                      <label class="col-form-label"><strong>Category :</strong></label>
                       <?php
                         $main_category_name='';
                         $main_subcategory_name[]='';
                            if(count($user->category_user)>0)
                              {
                                foreach ($user->category_user as $key => $providerdata) 
                                {
                                  if($providerdata->category->parent_id==0)
                                  {
                                     $main_category_name= isset($providerdata->category->title)?$providerdata->category->title:'';
                                  }
                                  if($providerdata->category->parent_id!=0)
                                  {
                                     if(isset($providerdata->category->title) && $providerdata->category->title!='')
                                     {                              
                                        $main_subcategory_name[]= $subcategory_name= $providerdata->category->title; 
                                     }    
                                  }                           
                               }                           
                              }  
                              echo $main_category_name;
                        ?>    
                     </div>    
                      <div class="form-group">
                      <label class="col-form-label"><strong>Subcategory :</strong></label>
                      <?php 
                            $main_subcategory_name = implode(', ', $main_subcategory_name);
                            echo trim($main_subcategory_name,",");
                        ?>        
                     </div> 
                        <div class="form-group">
                 <label class="col-form-label"><strong>Service Type : </strong></label>
                  <label class="col-form-label">Request for Quote </label>
        <?php 
                       if($user->profile->is_rfq == TRUE ){
                         ?>
                       
                       <i class="fa badge-primary fa-check" aria-hidden="true"></i>
                         <?php
                       }else
                       {
                        ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                        <?php
                       }
                       ?> <label class="col-form-label">Package  </label> 
                      <?php if($user->profile->is_package == TRUE ){
                         ?>
                       
                       <i class="fa badge-primary fa-check" aria-hidden="true"></i>
                       <?php
                       }else
                       {
                        ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                        <?php
                       }?>
                         <label class="col-form-label">Hourly  </label>

                      <?php if($user->profile->is_hourly  == TRUE ){
                         ?>
                      
                       <i class="fa badge-primary fa-check" aria-hidden="true"></i>
                        <?php
                       }else
                       {
                        ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                        <?php
                       }
                        ?>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label"><strong>Address line 1 :</strong> <?php echo isset($user->profile->address_line_1)?ucwords($user->profile->address_line_1):'';?>   </label>
                    </div> 
                    <div class="form-group">
                        <label class="col-form-label"><strong>Address :</strong> <?php echo isset($user->profile->work_address)?ucwords($user->profile->work_address):'';?>   </label>
                    </div> 
                  <!--   <div class="form-group">
                        <label class="col-form-label"><strong>Zipcode :</strong> <?php echo isset($user->profile->zip_code)?ucwords($user->profile->zip_code):'';?>   </label>
                    </div>  -->
                    




      </div>
      <div class="col-md-7">

        <div class="row">
            <div class="col-md-7">   
              <div class="form-group">
                        <label class="col-form-label"><strong>Latitude : </strong> <?php echo isset($user->profile->latitude)?ucwords($user->profile->latitude):'';?>   </label>
                    </div>
                     <div class="form-group">
                        <label class="col-form-label"><strong>Longitude : </strong> <?php echo isset($user->profile->longitude)?ucwords($user->profile->longitude):'';?>   </label>
                    </div>
                    <div class="form-group">
                        <label class="col-form-label"><strong>Radius : </strong> <?php echo isset($user->profile->radius)?ucwords($user->profile->radius).config('constants.DISTANCE_KM'):'';?> </label>
                    </div> 
                    <div class="form-group">
                        <label class="col-form-label"><strong>DOB : </strong>{{ isset($user->profile->dob)?date(config('constants.DATE_FORMAT'),strtotime($user->profile->dob)):'' }} </label>
                    </div>
                       <div class="form-group">               
                       <label class="col-form-label"><strong>Year experience :  </strong>{{ isset($user->profile->experience_level->title)?ucwords($user->profile->experience_level->title):'' }} </label> 
                    </div>  
                    <div class="form-group">
                        <label class="col-form-label"><strong>Passport Number :  </strong><?php echo isset($user->profile->passport_number)?ucwords($user->profile->passport_number):'';?>   </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>Registered Date Time :  </strong>{{ date(config('constants.DATETIME_FORMAT'),strtotime($user->created_at)) }} </label>
                    </div>

            </div>
            <div class="col-md-5"> @if(isset($user) && $user->getMedia('profile_picture')->count() > 0 && file_exists($user->getFirstMedia('profile_picture')->getPath()))
                    @php $image_required = false; @endphp
                <div class="col-md-5 form-group">
                    <img width="100%" src="{{ $user->getFirstMedia('profile_picture')->getFullUrl() }}" />
                </div>
                @endif

            </div>
        </div>

      </div>
  </div>

 </div>

<div class="card card-information">
     <div class="alert alert-secondary col-md-12" role="alert"><div class="row"><div class="col-md-5">    
                   </div><div class="col-md-7"> <div class="text-left font-weight-bold">Technical Information</div></div></div>

</div>

<div class="card-body border mb-2 border-primary  mt-2 mb-2 ml-4 mr-4">

<div class="row">
<div class="col-md-5">

   <div class="form-group col-md-12 ">
    <h6 class="m-0 font-weight-bold text-primary">Evidence of Expertise</h6>
 </div>
  <div class="form-group col-md-12 ml-2 ">
    <strong class="m-0 font-weight-bold">Works photo</strong>
  </div>
<div class="row  ml-4  ">
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
 <div class="col-md-12  ml-2   form-group">
    <label class="col-form-label"><strong>Academy Trained :  </strong>
 <?php  if($user->profile->is_academy_trained == TRUE )
 {
  ?><i class="fa badge-primary fa-check" aria-hidden="true"></i>
  <?php 
 }else
 {
  ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
  <?php
 }
     ?>
                   
                      
            </div>


</div>
<div class="col-md-7">
<div class="row">
  <h6 class=" col-md-12 ml-3 font-weight-bold text-primary">Social Url</h6>
  <div class="col-md-12  ml-4">
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
</div>




</div>


<!-- </div> -->







                <div class="row  ml-1">
                      <div class="col-md-5">
                  

                                 
              <div class="row">
                    <div class="col-md-12">
                      <div class="">
    <h6 class="m-0 font-weight-bold text-primary">Declarations</h6>
 </div>
                      
        </div>
          <div class="col-md-12  ml-2">
         <div class="form-group">
                        <label class="col-form-label"><strong>I am not middlemen :  </strong>
                       <?php 
                       if($user->profile->fundi_is_middlemen == TRUE ){
                        ?><i class="fa badge-primary fa-check" aria-hidden="true"></i>
                        <?php 
                       }else
                       {
                        ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                        <?php
                       }
                        ?> </label>
                    </div> 
                     <div class="form-group">
                        <label class="col-form-label"><strong>I have all the required tools to do their job : </strong>
                       <?php 
                       if($user->profile->fundi_have_tools == TRUE ){
                        ?><i class="fa badge-primary fa-check" aria-hidden="true"></i>
                        <?php 
                       }else
                       {
                        ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                        <?php
                       }
                        ?> </label>
                    </div>
                       <div class="form-group">
                        <label class="col-form-label"><strong>I have a smartphone : </strong>
                       <?php 
                       if($user->profile->fundi_have_smartphone == TRUE ){
                      ?><i class="fa badge-primary fa-check" aria-hidden="true"></i>
                      <?php
                       }else
                         {
                          ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                          <?php
                         }
                        ?> </label>
                    </div>

                  
</div>
                   
                 </div>                          
                    
                      </div>
                      <div class="col-md-7">
                  <h6 class="ml-0 font-weight-bold text-primary">Certificate</h6>

                  <div class="row">
                   <div class="col-md-4">
                      <label class="col-md-12 control-label" for="degree_title">Degree </label>
                      <label class="col-md-12 control-label" for="degree_title"><strong><?php echo isset($providerdegree->title)?$providerdegree->title:'';?></strong></label>
                      @if(isset($providerdegree) && $providerdegree->getMedia('degree')->count() > 0 && file_exists($providerdegree->getFirstMedia('degree')->getPath()))  
                    <div class="row  col-md-6">                     
                    <div class="col-md-1  form-group">
                        <a download href="{{ $providerdegree->getFirstMedia('degree')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                   </div>
                   <div class="col-md-4">
                      <label class="col-md-12 control-label" for="diploma_title">Diploma</label>
                      <label class="col-md-12 control-label" for="diploma_title"><strong><?php echo isset($providerdiploma->title)?$providerdiploma->title:'';?></strong></label>
                       @if(isset($providerdiploma) && $providerdiploma->getMedia('diploma')->count() > 0 && file_exists($providerdiploma->getFirstMedia('diploma')->getPath()))              
                     
                        <div class="row  col-md-6">
                          
                    <div class="col-md-1  form-group">
                        <a download href="{{ $providerdiploma->getFirstMedia('diploma')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                   </div>
                   <div class="col-md-4">
                      <label class="col-md-12 control-label" for="certification_title">Certification</label>
                       <label class="col-md-12 control-label" for="certification_title"><strong><?php echo isset($providercertification->title)?$providercertification->title:'';?></strong></label>
                       @if(isset($providercertification) && $providercertification->getMedia('certification')->count() > 0 && file_exists($providercertification->getFirstMedia('certification')->getPath()))              
                     
                      <div class="row  col-md-6">  
                           
                    <div class="col-md-1 form-group">
                        <a download href="{{ $providercertification->getFirstMedia('certification')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                   </div>

                    <div class="col-md-12">
                        <label class="col-md-9 control-label" for="title">Upload Certificate of conduct </label>
                       @if(isset($user) && $user->getMedia('certificate_conduct')->count() > 0 && file_exists($user->getFirstMedia('certificate_conduct')->getPath()))
                        @php $image_required = false; @endphp
                    <div class="col-md-2 form-group">
                      
                         <a download href="{{ $user->getFirstMedia('certificate_conduct')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                    @endif

                    </div>
  
                  </div>
                    
                   </div>

                 </div>      
                
 </div>
<div class="card-body border mb-2 border-primary  mt-2 mb-2 ml-4 mr-4">
 <div class="form-group col-md-12 ">
    <h6 class="m-0 font-weight-bold text-primary">Company Information</h6>
 </div>
    <div class="row"  id="security_container">
          <div class="col-md-4">
           <div class="form-group">
                <?php  $company_name=isset($providerCompany->name)?$providerCompany->name:''; 
                       $remarks=isset($providerCompany->remarks)?$providerCompany->remarks:'';
                       $document_number=isset($providerCompany->document_number)?$providerCompany->document_number:'';
                       $is_payment_received=isset($providerCompany->is_payment_received)?$providerCompany->is_payment_received:'';?>
                
                <label class="col-md-12 control-label" for="company_name"> <strong>Company Name : </strong> <?php echo isset($company_name)?$company_name:'';?></label>
                           
            </div>
<div class="form-group">
                <label class="col-md-12 control-label" for="company_logo"><strong>Company logo : </strong></label>
                <div class="col-md-12">              
                    @php $company_logo_required = true; @endphp
                    @if(isset($providerCompany) && $providerCompany->getMedia('company_logo')->count() > 0 && file_exists($providerCompany->getFirstMedia('company_logo')->getPath()))
                        @php $company_logo_required = false; @endphp
                        <div class="row ">                        
                    <div class="col-md-1 form-group">
                        <a download href="{{ $providerCompany->getFirstMedia('company_logo')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                </div>               
            </div>
 <div class="form-group">
                <label class="col-md-12 control-label" for="document_image"><strong>Upload Document :</strong></label>
                <div class="col-md-12">
                    
                    @php $document_image_required = true; @endphp
                    @if(isset($providerCompany) && $providerCompany->getMedia('document_image')->count() > 0 && file_exists($providerCompany->getFirstMedia('document_image')->getPath()))
                        @php $document_image_required = false; @endphp
                        <div class="row ">                  
                    <div class="col-md-1 form-group">
                        <a download href="{{ $providerCompany->getFirstMedia('document_image')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif
                </div>               
            </div>
           </div>
           <div class="col-md-4">
            <div class="form-group">
                <label class="col-md-12 control-label" for="document_number"><strong>Document Number : </strong><?php echo isset($document_number)?$document_number:'';?></label> 
                <div class="col-md-12">                  
                    
                   
                </div>               
            </div>
            <div class="form-group">
                <label class="col-md-12 control-label" for="remarks"><strong>Remarks :</strong> <?php echo isset($remarks)?$remarks:'';?></label>                
            </div>
             <div class="col-md-6 form-group">
                     <?php  if($is_payment_received == TRUE ){
                      ?><i class="fa badge-primary fa-check" aria-hidden="true"></i>
                      <?php
                       }else
                         {
                          ?><i class="fa badge-primary fa-times" aria-hidden="true"></i>
                          <?php
                         } ?>
                        <label for="is_default">Payment Received </label>                        
                       
            </div> 



           </div>
           <div class="col-md-4">
            <div class="col-md-12 form-group">
              <label class="col-md-12 control-label" for="passport_number"><strong>Passport Number : </strong><?php echo isset($user->profile->passport_number)?$user->profile->passport_number:''; ?></label>      
            </div> 
            <div class="col-md-6 form-group">
              <label class="col-md-12 control-label" for="passport_image"><strong>Passport Image : </strong></label>   
                 <div class="col-md-12">     
                    @php $passport_image_required = true; @endphp
                    @if(isset($user) && $user->getMedia('passport_image')->count() > 0 && file_exists($user->getFirstMedia('passport_image')->getPath()))
                        @php $passport_image_required = false; @endphp
                        <div class="row ml-2">                      
                    <div class=" form-group">
                        <a download href="{{ $user->getFirstMedia('passport_image')->getFullUrl() }}" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>
                    </div>
                        </div>  
                    @endif


                  </div>     
                 </div> 
           </div>
       </div> 





                  <div class="row">
                      <div class="col-md-6">

                    <div class="form-group">
             <!--    <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Company</h6>
        </div> -->
                  <!-- <div class="table-responsive">
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
              </div>  --> 
              </div>
             
            <div class="form-group">
                <div class="ml-2">
        <h6 class="ml-2 font-weight-bold text-primary">Packages</h6>
        </div>
         <div class="ml-2 table-responsive">
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



              </div><div class="col-md-6">
               <div class="form-group">
                <div class="ml-0">
        <h6 class="ml-2 font-weight-bold text-primary">Hourly Charges</h6>
        </div>
                <div class="ml-2 table-responsive">
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
             <!--    <div class="ml-0">
        <h6 class="ml-0 font-weight-bold text-primary">Certifications</h6>
        </div> -->
                 <!--  <div class="table-responsive">
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
              </div>  --> 
              </div></div>
                     
                  </div> </div> 
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
      jQuery('#packages_table').DataTable({
         responsive: true,
         pageLength: 5,
         lengthChange: false
     });
     jQuery('#hourly_table').DataTable({
         responsive: true,
         pageLength: 5,
         lengthChange: false
     });
     jQuery('#company_table').DataTable({
         responsive: true,
         pageLength: 5,
         lengthChange: false
     });
     jQuery('#certification_table').DataTable({
         responsive: true,
         pageLength: 5,
         lengthChange: false
     });      
});
</script>
@endsection
