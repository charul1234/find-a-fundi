<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Booking;
use App\User;
use App\Category;
use Validator;
use DataTables;
use Config;
use Form;
use DB;
use App\Transaction;
use App\Schedule;
use App\BookingUser;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin/bookings/index');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getBookings(Request $request){
        $bookings = Booking::with(['category','booking_user','user']);
        $bookings = $bookings->select(DB::raw('bookings.*'));

        $job_type = $request->input('job_type');
        $job_status = $request->input('job_status');
        if($job_type=='is_rfq')
        {
            $bookings=$bookings->where('is_rfq', true);
        }else if($job_type=='is_hourly')
        {
             $bookings=$bookings->where('is_hourly', true);
        }else if($job_type=='is_package')
        {
             $bookings=$bookings->where('is_package', true);
        }
        if($job_status!='')
        {
            $bookings=$bookings->where('status', $job_status);
        }     
            
        return DataTables::of($bookings)
            //->orderColumn('image', '-title $1')
            ->editColumn('datetime', function($booking){
                return date(config('constants.DATETIME_FORMAT'), strtotime($booking->datetime));
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereRaw("LOWER(DATE_FORMAT(created_at,'".config('constants.MYSQL_DATETIME_FORMAT')."')) like ?", ["%$keyword%"]);
            })            
            ->editColumn('title', function ($booking) {
                return isset($booking->title)?ucwords($booking->title):'';
            })
            ->editColumn('user_id', function ($booking) {
                $providerdata=User::where('id',$booking->user_id)->first();
                $provider_name=isset($providerdata->name)?$providerdata->name:'';
                return isset($provider_name)?($provider_name):'';
            })
            ->editColumn('requested_id', function ($booking) {
                $seekerdata=User::where('id',$booking->requested_id)->first();
                $seeker_name=isset($seekerdata->name)?$seekerdata->name:'';
                return isset($seeker_name)?($seeker_name):'';
            })
            ->editColumn('request_for_quote_budget', function ($booking) {                
                return isset($booking->request_for_quote_budget)?config('constants.DEFAULT_CURRENCY_SYMBOL').$booking->request_for_quote_budget:'';
            })
            
            ->addColumn('hourly_budget', function ($booking) {      
             $min_budget=isset($booking->min_budget)?config('constants.DEFAULT_CURRENCY_SYMBOL').$booking->min_budget:'';  
             $max_budget=isset($booking->max_budget)?config('constants.DEFAULT_CURRENCY_SYMBOL').$booking->max_budget:'';        
                return $min_budget." - ".$max_budget;
            })
            ->addColumn('type', function ($booking) {  
                $type='';
                if($booking->is_hourly==true)
                {
                    $type='Hourly';
                }else if($booking->is_rfq==true)
                {
                    $type='RFQ';
                }else if($booking->is_package==true)
                {
                    $type='Package';
                }
                return $type;
            })
            ->editColumn('estimated_hours', function ($booking) {               
                return isset($booking->estimated_hours)?$booking->estimated_hours:'';                
            })
            ->editColumn('is_quoted', function ($booking) {
                $is_quoted='No';
                if($booking->is_quoted==true)
                {
                    $is_quoted='Yes';
                }
                return $is_quoted;                
            })
            ->addColumn('action', function ($booking) {
                return
                        // Edit  '.route('admin.bookings.view',[$booking->id]).'
                        '<a href="'.route('admin.bookings.view',[$booking->id]).'" class="btn btn-info btn-circle btn-sm"><i class="fas fa-eye"></i></a>'.
                        // Delete
                          Form::open(array(
                                      'style' => 'display: inline-block;',
                                      'method' => 'DELETE',
                                       'onsubmit'=>"return confirm('Do you really want to delete?')",
                                      'route' => ['admin.bookings.destroy', $booking->id])).
                          ' <button type="submit" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>'.
                          Form::close();
            })
            ->rawColumns(['is_active','action'])
            ->make(true);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {   
        $booking=Booking::with(['category','booking_user','user','user.profile'])->where('id',$id)->first();       
        $job_type=$providerdata=$categoryname='';
        $subcategoryname=$booking_user=array();
        if($booking->is_rfq==1)
        {
          $job_type='RFQ';
          $booking_user= BookingUser::with(['user'])->where(array('booking_id'=>$id))->get();
        }else if($booking->is_hourly==1)
        {
          $job_type='Hourly';
          
        }else if($booking->is_package==1)
        {
          $job_type='Package';   
          
        }         
        if(isset($booking->user_id) || $booking->user_id!='0')
        {
          $providerdata=User::with('profile')->where('id',$booking->user_id)->first();    
        }
        $categoryname=isset($booking->category->title)?$booking->category->title:'';        
        if(count($booking->subcategory)>0)
        {
          foreach ($booking->subcategory as $key => $subcategory) 
          {
            $subcategoryname[]=$subcategory->category->title;
          }   
        }
        $seekerdata=User::where('id',$booking->requested_id)->first(); 
        $schedules=Schedule::where('booking_id',$id)->get();  
        return view('admin/bookings/view',compact('booking','job_type','providerdata','categoryname','subcategoryname','seekerdata','booking_user','schedules'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $booking = Booking::findOrFail($id);  
      Transaction::where('booking_id',$id)->delete();
      Schedule::where('booking_id',$id)->delete();
      $booking->delete();
      session()->flash('danger',__('global.messages.delete'));
      return redirect()->route('admin.bookings.index');
    }
}
