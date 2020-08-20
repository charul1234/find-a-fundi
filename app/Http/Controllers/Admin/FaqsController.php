<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Faq;
use Validator;
use DataTables;
use Config;
use Form;
use DB;
use Auth;

class FaqsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin/faqs/index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFaqs(Request $request){
        $faqs = Faq::query();
        $faqs = $faqs->select(DB::raw('faqs.*'));

        return DataTables::of($faqs)            
            ->editColumn('title', function ($faq) {
                return isset($faq->title)?ucwords($faq->title):'';
            })     
            ->editColumn('is_active', function ($faq) {
                if($faq->is_active == TRUE )
                {
                    return "<a href='".route('admin.faqs.status',$faq->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.faqs.status',$faq->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->addColumn('action', function ($faq) {
                return
                    // edit
                    '<a href="'.route('admin.faqs.edit',[$faq->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                    // Delete
                      Form::open(array(
                                  'style' => 'display: inline-block;',
                                  'method' => 'DELETE',
                                   'onsubmit'=>"return confirm('Do you really want to delete?')",
                                  'route' => ['admin.faqs.destroy', $faq->id])).
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
        return view('admin.faqs.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user(); 
        $rules = [ 
            'title'             => 'required', 
            'description'       => 'required'
        ];

        $validator = Validator::make($request->all(), $rules);
        if($validator->passes()) {
            $data = $request->all();
            $faq_data=array('title'=>$data['title'],
                            'description'=>$data['description'],
                            'created_by'=>$user->id,
                            'is_active'=>1);
            $faq = Faq::create($faq_data);            
            $request->session()->flash('success',__('global.messages.add'));
            return redirect()->route('admin.faqs.index');
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
      $faq = Faq::findOrFail($id);
      if (isset($faq->is_active) && $faq->is_active==FALSE) {
          $faq->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $faq->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.faqs.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Faq $faq)
    {
        return view('admin.faqs.edit',compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Faq $faq)
    {
        $user = Auth::user(); 
        $rules = [
            'title'             => 'required', 
            'description'          => 'required'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->passes()) {
            $data = $request->all();
            $faq_data=array('title'=>$data['title'],
                            'description'=>$data['description'],
                            'created_by'=>$user->id);
            $faq->update($faq_data);
            $request->session()->flash('success',__('global.messages.update'));
            return redirect()->route('admin.faqs.index');
        }else {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faq $faq){
      $faq->delete();
      session()->flash('danger',__('global.messages.delete'));
      return redirect()->route('admin.faqs.index');
    }
}
