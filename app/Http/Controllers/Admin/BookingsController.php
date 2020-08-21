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
        if($job_type=='is_rfq')
        {
            $bookings=$bookings->where('is_rfq', true);
        }else if($job_type=='is_hourly')
        {
             $bookings=$bookings->where('is_hourly', true);
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
            ->editColumn('description', function ($booking) {
                $description='';
                if (strlen($booking->description) > 80) {
                    $description=substr($booking->description,0,80).'...';
                }else
                {
                    $description=$booking->description;
                }
                return $description;
            })
            ->editColumn('title', function ($booking) {
                return isset($booking->title)?ucwords($booking->title):'';
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
                        '<a href="#" class="btn btn-info btn-circle btn-sm"><i class="fas fa-eye"></i></a>';
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
    public function view($id,Booking $booking)
    { 
        return view('admin/bookings/view',compact('booking'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
