<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TaxonomyController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!auth()->user()->can('category.view') && !auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        $category_type = request()->get('type');


        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $category = Category::where('business_id', $business_id)
            ->where('category_type', $category_type)
            ->select(['name', 'short_code', 'description', 'id', 'parent_id', 'image',
                'destaque', 'ecommerce']);

            return Datatables::of($category)
            ->addColumn(
                'action',
                '@can("category.update")
                <button onclick="md(\'{{$image}}\')" data-href="{{action(\'TaxonomyController@edit\', [$id])}}?type=' . $category_type . '" class="btn btn-xs btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i>  @lang("messages.edit")</button>
                &nbsp;
                @endcan
                @can("category.delete")
                <button data-href="{{action(\'TaxonomyController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_category_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                @endcan'
            )
            ->editColumn('name', function ($row) {
                if ($row->parent_id != 0) {
                    $temp = Category::findOrFail($row->parent_id);
                    return $row->name . ' | Sub: ' . $temp->name;
                } else {
                    return $row->name;
                }
            })
            ->editColumn('destaque', function ($row) {
                if ($row->destaque) {
                    return '<i class="fa fa-check text-success"></i>';
                } else {
                    return '<i class="fa fa-ban text-danger"></i>';
                }
            })
            ->editColumn('ecommerce', function ($row) {
                if ($row->ecommerce) {
                    return '<i class="fa fa-check text-success"></i>';
                } else {
                    return '<i class="fa fa-ban text-danger"></i>';
                }
            })
            ->editColumn('image', function ($row) {
                if($row->image != NULL){
                    return '<div style="display: flex;"><img src="/uploads/img/categorias/' . $row->image . '" alt="Categoria imagem" class="product-thumbnail-small"></div>';
                }
                else{
                    return '<div style="display: flex;"><img src="/img/default.png" class="product-thumbnail-small"></div>';
                }
            })
            ->removeColumn('id')
            // ->removeColumn('parent_id')
            ->rawColumns(['action', 'image', 'destaque', 'ecommerce'])
            ->make(true);
        }

        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

        return view('taxonomy.index')->with(compact('module_category_data', 'module_category_data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $category_type = request()->get('type');
        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

        $categories = Category::where('business_id', $business_id)
        ->where('parent_id', 0)
        ->where('category_type', $category_type)
        ->select(['name', 'short_code', 'id'])
        ->get();

        $parent_categories = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $parent_categories[$category->id] = $category->name;
            }
        }


        return view('taxonomy.create')
        ->with(compact('parent_categories', 'module_category_data', 'category_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('category.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'short_code', 'category_type', 'description',
                'destaque', 'ecommerce']);
            if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                $input['parent_id'] = $request->input('parent_id');
            } else {
                $input['parent_id'] = 0;
            }
            $input['image'] = $this->moduleUtil->uploadFile($request, 'image', 'img/categorias', 'image');

            if($input['image'] == null) $input['image'] = "";


            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');

            $category = Category::create($input);
            $output = [
                'success' => true,
                'data' => $category,
                'msg' => __("category.added_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        // return $output;
        return redirect()->back()->with('status', $output);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $category = Category::where('business_id', $business_id)->find($id);

            $category_type = request()->get('type');
            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

            $parent_categories = Category::where('business_id', $business_id)
            ->where('parent_id', 0)
            ->where('category_type', $category_type)
            ->where('id', '!=', $id)
            ->pluck('name', 'id');
            $is_parent = false;

            if ($category->parent_id == 0) {
                $is_parent = true;
                $selected_parent = null;
            } else {
                $selected_parent = $category->parent_id ;
            }

            return view('taxonomy.edit')
            ->with(compact('category', 'parent_categories', 'is_parent', 'selected_parent', 'module_category_data'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        // if (request()->ajax()) {
        try {
            $input = $request->only(['name', 'description', 'destaque', 'ecommerce']);
            $business_id = $request->session()->get('user.business_id');

            $input['image'] = $this->moduleUtil->uploadFile($request, 'image', 'img/categorias', 'image');

            $category = Category::where('business_id', $business_id)->findOrFail($id);

            if($input['image']){
                if($category->image != null){
                    if(file_exists(public_path('uploads/img/categorias/').$category->image)){
                        unlink(public_path('uploads/img/categorias/').$category->image);
                    }
                }
            }

            // $input['image'] = $this->moduleUtil->uploadFile($request, 'image', 'img/categorias', 'image');

            $category->name = $input['name'];
            $category->description = $input['description'];
            $category->destaque = isset($input['destaque']) ? 1 : 0;
            $category->ecommerce = isset($input['ecommerce']) ? 1 : 0;
            if($input['image']){
                $category->image = $input['image'];
            }

            if (!empty($request->input('short_code'))) {
                $category->short_code = $request->input('short_code');
            }

            if (!empty($request->input('add_as_sub_cat')) &&  $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                $category->parent_id = $request->input('parent_id');
            } else {
                $category->parent_id = 0;
            }
            $category->save();

            $output = [
                'success' => true,
                'msg' => __("category.updated_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            // echo $e->getMessage();
            // die;
            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

            // return $output;
        return redirect()->back()->with('status', $output);

        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('category.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);

                if($category->image){
                    if(file_exists(public_path('uploads/img/categorias/').$category->image)){
                        unlink(public_path('uploads/img/categorias/').$category->image);
                    }
                }

                $category->delete();

                $output = [
                    'success' => true,
                    'msg' => __("category.deleted_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }

    public function getCategoriesApi()
    {
        try {
            $api_token = request()->header('API-TOKEN');

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $categories = Category::catAndSubCategories($api_settings->business_id);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($categories);
    }

    /**
     * get taxonomy index page
     * through ajax
     * @return \Illuminate\Http\Response
     */
    public function getTaxonomyIndexPage(Request $request)
    {
        if (request()->ajax()) {
            $category_type = $request->get('category_type');
            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

            return view('taxonomy.ajax_index')
            ->with(compact('module_category_data', 'category_type'));
        }
    }
}
