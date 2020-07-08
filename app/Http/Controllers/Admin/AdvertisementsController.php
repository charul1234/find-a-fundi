<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Advertisement;
use Validator;
use DataTables;
use Config;
use Form;
use DB;
use Auth;

class AdvertisementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         return view('admin/advertisements/index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function getAdvertisements(Request $request){  

        $advertisements = Advertisement::with(['media']);
        $advertisements = $advertisements->select(DB::raw('advertisements.*'));
         
        return DataTables::of($advertisements)  
            ->editColumn('section', function ($advertisement) {
                return ucwords($advertisement->section);
            })    

            ->editColumn('media.name', function ($advertisement) {
                if(isset($advertisement) && $advertisement->getMedia('image')->count() > 0 && file_exists($advertisement->getFirstMedia('image')->getPath()))
                    return '<img width="100px" src="'.$advertisement->getFirstMedia('image')->getFullUrl().'" />';

                return '';
            })
            ->editColumn('is_active', function ($advertisement) {
                if($advertisement->is_active == TRUE ){
                    return "<a href='".route('admin.advertisements.status',$advertisement->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.advertisements.status',$advertisement->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            }) 
            ->addColumn('action', function ($advertisement) {
                return
                    // edit
                    '<a href="'.route('admin.advertisements.edit',[$advertisement->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                    // Delete
                    Form::open(array(
                        'style' => 'display: inline-block;',
                        'method' => 'DELETE',
                        'onsubmit'=>"return confirm('Do you really want to delete?')",
                        'route' => ['admin.advertisements.destroy', $advertisement->id])).
                    ' <button type="submit" class="btn btn-danger btn-circle btn-sm"><i class="fas fa-trash"></i></button>'.
                    Form::close();
            })
            ->rawColumns(['media.name','is_active','action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sections=Advertisement::getSections();
        return view('admin.advertisements.create',compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $media_max_size = config('medialibrary.max_file_size') / 1024;  
        $rules = [
            'page_name'    =>'required',            
            'section'      =>'required',
            'title'        =>'required|unique:'.with(new Advertisement)->getTable(),
            'discription'  =>'nullable',
            'start_date'   =>'required',
            'end_date'     =>'required',           
            'image'=>[
                        'required',
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ]           
        ];  

        $request->validate($rules);
        
        $data = $request->all(); 
        $start_date = date('Y-m-d',strtotime($request->start_date)); 
        $end_date =  date('Y-m-d',strtotime($request->end_date));
        $data['start_date']=$start_date;
        $data['end_date']=$end_date;
        $data['created_by']=Auth::user()->id;
        $data['updated_by']=Auth::user()->id;         

        $advertisement = Advertisement::create($data); 

        if ($request->hasFile('image')){
             $file = $request->file('image');
             $customname = time() . '.' . $file->getClientOriginalExtension();
             $advertisement->addMedia($request->file('image'))
               ->usingFileName($customname)               
               ->toMediaCollection('image');
        }        
         
        $request->session()->flash('success',__('global.messages.add'));
        return redirect()->route('admin.advertisements.index');
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Advertisement $advertisement)
    {
        $sections=Advertisement::getSections();
        return view('admin.advertisements.edit',compact('sections','advertisement'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $media_max_size = config('medialibrary.max_file_size') / 1024;  
        $rules = [
            'page_name'    =>'required',            
            'section'      =>'required',
            'title'        =>'required|unique:'.with(new Advertisement)->getTable().',title,'.$advertisement->getKey(),
            'discription'  =>'nullable',
            'start_date'   =>'required',
            'end_date'     =>'required',           
            'image'=>[                       
                        'image',
                        'mimes:jpeg,jpg,png',
                        'max:'.$media_max_size,
                     ]           
        ]; 
        $request->validate($rules);
        
        $data = $request->all(); 
        $start_date = date('Y-m-d',strtotime($request->start_date)); 
        $end_date =  date('Y-m-d',strtotime($request->end_date));
        $data['start_date']=$start_date;
        $data['end_date']=$end_date;
        $data['created_by']=Auth::user()->id;
        $data['updated_by']=Auth::user()->id;   
        
        if (isset($advertisement) && ($advertisement->getMedia('image')->count()==0 || ($advertisement->getMedia('image')->count() >0 && !file_exists($advertisement->getFirstMedia('image')->getPath())))) {
            $rules['image'] = [                
                'file',
                'max:'.$media_max_size,
                'image'
            ];
        }
        
        $advertisement->update($data); 
        if ($request->hasFile('image')){
            $file = $request->file('image');
            $customname = time() . '.' . $file->getClientOriginalExtension();
            $advertisement
               ->addMedia($request->file('image'))
               ->usingFileName($customname)               
               ->toMediaCollection('image');
        }
        $request->session()->flash('success',__('global.messages.update'));
        return redirect()->route('admin.advertisements.index');
    }
    /**
     * Change status the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
    */
    public function status($id=null){
      $advertisement = Advertisement::findOrFail($id);
      if (isset($advertisement->is_active) && $advertisement->is_active==FALSE) {
          $advertisement->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $advertisement->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.advertisements.index');
    }

     /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Package $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Advertisement $advertisement){
      $advertisement->delete();
      session()->flash('danger',__('global.messages.delete'));
      return redirect()->route('admin.advertisements.index');
    }
}
