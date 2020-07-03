<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DataTables;
use Form;
use App\Country;

class CountriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.countries.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountries(Request $request){        
        $countries = Country::query();

        return DataTables::of($countries)  
            ->editColumn('is_default', function ($country) {
                if($country->is_default == TRUE ){
                    return "<a href='".route('admin.countries.updateDefault',['id'=>$country->id])."'><span class='badge badge-success'>Yes</span></a>";
                }else{
                    return "<a href='".route('admin.countries.updateDefault',['id'=>$country->id])."'><span class='badge badge-danger'>No</span></a>";
                }
             })          
            ->editColumn('is_active', function ($country) {
                if($country->is_active == TRUE ){
                    return "<a href='".route('admin.countries.status',$country->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.countries.status',$country->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->addColumn('action', function ($country) {
                return
                    // edit
                    '<a href="'.route('admin.countries.edit',[$country->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                    // Delete
                    Form::open(array(
                        'style' => 'display: inline-block;',
                        'method' => 'DELETE',
                        'onsubmit'=>"return confirm('Do you really want to delete?')",
                        'route' => ['admin.countries.destroy', $country->id])).
                    ' <button type="submit" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>'.
                    Form::close();
            })
            ->rawColumns(['is_default','is_active','action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         return view('admin.countries.create');
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
            'title'=>'required|unique:'.with(new Country)->getTable()
        ];

        $request->validate($rules);

        $is_default = intval($request->input('is_default'));
        $data['is_default'] = $is_default;

        $data = $request->all();

        $country = Country::create($data);

        if($is_default==TRUE)
            Country::where(['is_default'=>TRUE])->where('id', '!=', $country->id)->update(['is_default'=>FALSE]); 

        $request->session()->flash('success',__('global.messages.add'));
        return redirect()->route('admin.countries.index');

    }
   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Country $country)
    {
        return view('admin.countries.edit',compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Country $country)
    {
        $rules = [            
            'title'=>'required|unique:'.with(new Country)->getTable().',title,'.$country->getKey()
        ];

        $request->validate($rules);

        $is_default = intval($request->input('is_default'));
        $data['is_default'] = $is_default;  

        $data = $request->all();

        $country->update($data);  

        if($is_default==TRUE)
            Country::where(['is_default'=>TRUE])->where('id', '!=', $country->id)->update(['is_default'=>FALSE]);  

        $request->session()->flash('success',__('global.messages.update'));
        return redirect()->route('admin.countries.index');
    }

    /**
     * Change status the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
    */
    public function status($id=null){
      $country = Country::findOrFail($id);
      if (isset($country->is_active) && $country->is_active==FALSE) {
          $country->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $country->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.countries.index');
    }

      /**
     * Change default country on the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateDefault(Request $request, Country $country, $country_id=null){ 
        
        $country = Country::where(['id'=>$country_id])->groupBy('id')->first();
               
        if (isset($country->is_default) && $country->is_default==FALSE) {   
            $country->update(['is_default'=>TRUE]);                
            Country::where(['is_default'=>TRUE])->where('id', '!=',$country_id)->update(['is_default'=>FALSE]); 
            $request->session()->flash('success',__('global.messages.activate'));
        }else{
            $country->update(['is_default'=>FALSE]);
            $request->session()->flash('danger',__('global.messages.deactivate'));
        } 
         return redirect()->route('admin.countries.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Country $country)
    {
        $country->delete();
        $request->session()->flash('danger',__('global.messages.delete'));
        return redirect()->route('admin.countries.index');
    }
}
