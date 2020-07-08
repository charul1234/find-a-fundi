<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Package;
use App\User;
use App\Category;
use Validator;
use DataTables;
use Config;
use Form;
use DB;

class PackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){        
        return view('admin/packages/index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPackages(Request $request){
        $packages = Package::with(['category']);
        $packages = $packages->select(DB::raw('packages.*'));

        return DataTables::of($packages)
            //->orderColumn('image', '-title $1')
            ->editColumn('created_at', function($package){
                return date(config('constants.DATETIME_FORMAT'), strtotime($package->created_at));
            })
            ->filterColumn('created_at', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereRaw("LOWER(DATE_FORMAT(created_at,'".config('constants.MYSQL_DATETIME_FORMAT')."')) like ?", ["%$keyword%"]);
            })
            ->editColumn('category.title', function ($package) {
                return isset($package->category->title)?$package->category->title:'';
            })
            ->filterColumn('category.title', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereHas('category', function($query) use ($keyword){
                    $query->whereRaw("LOWER(title) like ?", ["%$keyword%"]);
                });
            })
            /*->editColumn('image', function ($package) {
                if (isset($package->image) && $package->image!='' && \Storage::exists(config('constants.PACKAGES_UPLOADS_PATH').$package->image)) { 
                    $image = \Storage::url(config('constants.PACKAGES_UPLOADS_PATH').$package->image);
                }else{
                    $image = asset(config('constants.NO_IMAGE_URL'));
                }
                return '<img src="'.$image.'" width="100">';
            })*/
            ->editColumn('is_active', function ($package) {
                if($package->is_active == TRUE )
                {
                    return "<a href='".route('admin.packages.status',$package->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.packages.status',$package->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->addColumn('action', function ($package) {
                return
                        // edit
                        '<a href="'.route('admin.packages.edit',[$package->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                        // Delete
                          Form::open(array(
                                      'style' => 'display: inline-block;',
                                      'method' => 'DELETE',
                                       'onsubmit'=>"return confirm('Do you really want to delete?')",
                                      'route' => ['admin.packages.destroy', $package->id])).
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
    public function create(){
        $categories = Category::where('is_active',TRUE)->get()->pluck('title','id');
        return view('admin.packages.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $rules = [ 
            'category_id'       => 'required', 
            'title'             => 'required', 
            'duration'          => 'required',
            'image.*'=>[
                'file',
                'image'
            ],
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();
            $package = Package::create($data);
            if ($request->hasFile('image')){
                 $files = $request->file('image');
                  foreach ($files as $file) {
                     $customname = time() . '.' . $file->getClientOriginalExtension();
                     $package->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('image');
               }
            }
            
            $request->session()->flash('success',__('global.messages.add'));
            return redirect()->route('admin.packages.index');
        }else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Package $package
     * @return \Illuminate\Http\Response
     */
    public function show(Package $package){
        return redirect()->route('admin.packages.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Package $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package){
        $categories = Category::get()->pluck('title','id');
        return view('admin.packages.edit',compact('package','categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Package $package
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Package $package){
        $rules = [
            'category_id'       => 'required', 
            'title'             => 'required', 
            'duration'          => 'required',
            'image.*'=>[
                'file',
                'image'
            ], 
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();
            $package->update($data);

            if ($request->hasFile('image')){
                 $files = $request->file('image');
                  foreach ($files as $file) {
                     $customname = time() . '.' . $file->getClientOriginalExtension();
                     $package->addMedia($file)
                       ->usingFileName($customname)
                       ->toMediaCollection('image');
               }
            } 
              $all_media_id=isset($request->all_media_id)?$request->all_media_id:array(); 
              $media_id=isset($request->media_id)?$request->media_id:array();              
              if(!empty($all_media_id))
              {
                    foreach ($all_media_id as $key => $value) { 
                      if (!in_array($value,$media_id)) {  
                        if(isset($value))
                        {                          
                             foreach ($package->media as $media) 
                             {                                 
                                if($media->id==$value)
                                {
                                    $media->delete();
                                }
                             }                                                  
                        }                   
                      }
                    }
              }
            
            $request->session()->flash('success',__('global.messages.update'));
            return redirect()->route('admin.packages.index');
        }else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Change status the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function status($id=null){
      $package = Package::findOrFail($id);
      if (isset($package->is_active) && $package->is_active==FALSE) {
          $package->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $package->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.packages.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Package $package
     * @return \Illuminate\Http\Response
     */
    public function destroy(Package $package){
      $package->delete();
      session()->flash('danger',__('global.messages.delete'));
      return redirect()->route('admin.packages.index');
    }
}
