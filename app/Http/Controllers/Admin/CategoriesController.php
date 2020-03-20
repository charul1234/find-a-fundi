<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use DataTables;
use Form;
use App\Category;

class CategoriesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        return view('admin.categories.index');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories(Request $request){        
        $categories = Category::query()->with('parent');

        return DataTables::of($categories)
            ->editColumn('parent.title', function ($category) {
                return isset($category->parent->title)?$category->parent->title:'';
            })
            ->filterColumn('parent.title', function ($query, $keyword) {
                $keyword = strtolower($keyword);
                $query->whereHas('parent', function($query) use ($keyword){
                    $query->whereRaw("LOWER(title) like ?", ["%$keyword%"]);
                });
            })
            ->editColumn('is_active', function ($category) {
                if($category->is_active == TRUE ){
                    return "<a href='".route('admin.categories.status',$category->id)."'><span class='badge badge-success'>Active</span></a>";
                }else{
                    return "<a href='".route('admin.categories.status',$category->id)."'><span class='badge badge-danger'>Inactive</span></a>";
                }
            })
            ->addColumn('action', function ($category) {
                return
                    // edit
                    '<a href="'.route('admin.categories.edit',[$category->id]).'" class="btn btn-success btn-circle btn-sm"><i class="fas fa-edit"></i></a> '.
                    // Delete
                    Form::open(array(
                        'style' => 'display: inline-block;',
                        'method' => 'DELETE',
                        'onsubmit'=>"return confirm('Do you really want to delete?')",
                        'route' => ['admin.categories.destroy', $category->id])).
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
        $categories = Category::where(['parent_id'=>0, 'is_active'=>TRUE])->pluck('title', 'id');
        return view('admin.categories.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        $rules = [
            'title'=>['required', Rule::unique(with(new Category)->getTable(), 'title')],
        ];
        $request->validate($rules);

        $data = $request->all();
        $data['parent_id'] = isset($request->parent_id)?$request->parent_id:0;
        Category::create($data);

        $request->session()->flash('success',__('global.messages.add'));
        return redirect()->route('admin.categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category){
        return redirect()->route('admin.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category){
        $categories = Category::where(['parent_id'=>0])->pluck('title', 'id');
        return view('admin.categories.edit', compact('category_type','category_type_heading','categories','category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category){
        $rules = [
            'title'=>['required',Rule::unique(with(new Category)->getTable(), 'title')->ignore($category->getKey())],
        ];

        $request->validate($rules);

        $data = $request->all();
        $data['parent_id'] = isset($request->parent_id)?$request->parent_id:0;
        $category->update($data);

        $request->session()->flash('success',__('global.messages.update'));
        return redirect()->route('admin.categories.index');
    }

    /**
     * Change status the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function status($id=null){
      $category = Category::findOrFail($id);
      if (isset($category->is_active) && $category->is_active==FALSE) {
          $category->update(['is_active'=>TRUE]);
          session()->flash('success',__('global.messages.activate'));
      }else{
          $category->update(['is_active'=>FALSE]);
          session()->flash('danger',__('global.messages.deactivate'));
      }
      return redirect()->route('admin.categories.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Category $category){
        $category->delete();
        $request->session()->flash('danger',__('global.messages.delete'));
        return redirect()->route('admin.categories.index');
    }
}
