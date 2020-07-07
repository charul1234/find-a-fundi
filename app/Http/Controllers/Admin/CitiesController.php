<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DataTables;
use Form;
use App\City;
use App\Country;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.cities.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCities(Request $request){        
        $cities = City::query()->with('country');   

        return DataTables::of($cities)                       
            ->editColumn('is_active', function ($city) {
                if($city->is_active == TRUE ){
                    return "<a href='".route('admin.cities.status',$city->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.cities.status',$city->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->editColumn('country.title', function ($city) {
                return isset($city->country->title)?$city->country->title:'';
            })
            ->addColumn('action', function ($city) {
                return
                    // edit
                    '<a href="'.route('admin.cities.edit',[$city->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                    // Delete
                    Form::open(array(
                        'style' => 'display: inline-block;',
                        'method' => 'DELETE',
                        'onsubmit'=>"return confirm('Do you really want to delete?')",
                        'route' => ['admin.cities.destroy', $city->id])).
                    ' <button type="submit" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>'.
                    Form::close();
            })
            ->rawColumns(['is_active','action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::where(['is_active'=>TRUE])->pluck('title', 'id')->map(function($value, $key){
          return ucwords($value);
          }); 
        return view('admin/cities/create',compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'country_id'=>'required',
            'title'=>'required|unique:'.with(new City)->getTable()
        ];

        $request->validate($rules);

        $data = $request->all();

        $city = City::create($data); 

        $request->session()->flash('success',__('global.messages.add'));
        return redirect()->route('admin.cities.index');

    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(City $city)
    {
        $countries = Country::where(['is_active'=>TRUE])->pluck('title', 'id')->map(function($value, $key){
          return ucwords($value);
          }); 
        return view('admin.cities.edit',compact('city','countries'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, City $city)
    {
        $rules = [      
            'country_id'=>'required',      
            'title'=>'required|unique:'.with(new City)->getTable().',title,'.$city->getKey()
        ];

        $request->validate($rules);

        $data = $request->all();

        $city->update($data);  

        $request->session()->flash('success',__('global.messages.update'));
        return redirect()->route('admin.cities.index');
    }

    /**
     *  status.
     *
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request, $city_id=null)
    {
        $city = City::findOrFail($city_id);
        if (isset($city->is_active) && $city->is_active==FALSE) {
            $city->update(['is_active'=>TRUE]);
            $request->session()->flash('success',__('global.messages.activate'));
        }else{
            $city->update(['is_active'=>FALSE]);
            $request->session()->flash('danger',__('global.messages.deactivate'));
        }
        return redirect()->route('admin.cities.index');
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,City $city)
    {
        $city->delete();
        $request->session()->flash('danger',__('global.messages.delete'));
        return redirect()->route('admin.cities.index');
    }
}
