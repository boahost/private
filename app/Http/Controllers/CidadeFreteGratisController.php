<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CidadeFreteGratis;
use App\Utils\ModuleUtil;
use Yajra\DataTables\Facades\DataTables;
use App\Models\System;
use App\Models\BusinessLocation;
use Spatie\Permission\Models\Role;
use App\Models\Contact;

class CidadeFreteGratisController extends Controller
{
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }

    public function index(){
        if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $cities = CidadeFreteGratis::
            where('business_id', $business_id)
            ->select(['id', 'nome', 'uf']);


            return Datatables::of($cities)
            ->addColumn(
                'action',
                '<a href="/freteGratis/edit/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>&nbsp;<a href="/freteGratis/delete/{{$id}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>'
            )

            ->removeColumn('id')
            ->rawColumns(['action'])
            ->make(true);

        }
        return view('frete_gratis.list');

    }

    public function new(){
        // return view('naturezas.register');

        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = request()->session()->get('user.business_id');

        $roles  = $this->getRolesArray($business_id);
        $username_ext = $this->getUsernameExtension();
        $contacts = Contact::contactDropdown($business_id, true, false);
        $locations = BusinessLocation::where('business_id', $business_id)
        ->Active()
        ->get();

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'naturezas.register']);

        return view('frete_gratis.register')
        ->with(compact('roles', 'username_ext', 'contacts', 'locations', 'form_partials'));
    }

    public function edit($id){
        // return view('naturezas.register');

        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        

        $roles  = $this->getRolesArray($business_id);
        $username_ext = $this->getUsernameExtension();
        $contacts = Contact::contactDropdown($business_id, true, false);
        $locations = BusinessLocation::where('business_id', $business_id)
        ->Active()
        ->get();

        $city = CidadeFreteGratis::find($id);

        //Get user form part from modules
        $form_partials = $this->moduleUtil->getModuleData('moduleViewPartials', ['view' => 'naturezas.register']);

        return view('frete_gratis.edit')
        ->with(compact('roles', 'city', 'username_ext', 'contacts', 'locations', 'form_partials'));
    }

    private function getRolesArray($business_id)
    {
        $roles_array = Role::where('business_id', $business_id)->get()->pluck('name', 'id');
        $roles = [];

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        foreach ($roles_array as $key => $value) {
            if (!$is_admin && $value == 'Admin#' . $business_id) {
                continue;
            }
            $roles[$key] = str_replace('#' . $business_id, '', $value);
        }
        return $roles;
    }

    private function getUsernameExtension()
    {
        $extension = !empty(System::getProperty('enable_business_based_username')) ? '-' .str_pad(session()->get('business.id'), 2, 0, STR_PAD_LEFT) : null;
        return $extension;
    }

    public function save(Request $request){
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $cidade = $request->only(['nome', 'uf']);


            $business_id = $request->session()->get('user.business_id');
            $cidade['business_id'] = $business_id;

            $c = CidadeFreteGratis::create($cidade);

            $output = [
                'success' => 1,
                'msg' => 'Sucesso'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];

        }

        return redirect('freteGratis')->with('status', $output);
    }

    public function delete($id){

        if (!auth()->user()->can('user.delete')) {
            abort(403, 'Unauthorized action.');
        }


        try {
            $business_id = request()->session()->get('user.business_id');

            CidadeFreteGratis::where('business_id', $business_id)
            ->where('id', $id)->delete();

            $output = [
                'success' => true,
                'msg' => 'Registro removido'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return redirect('freteGratis')->with('status', $output);

    }

    public function update(Request $request){
        if (!auth()->user()->can('user.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $cidade = $request->only(['nome', 'uf']);

            $city = CidadeFreteGratis::find($request->id);
            
            $city->update($cidade);

            $output = [
                'success' => 1,
                'msg' => 'Editado com sucesso!'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => __("messages.something_went_wrong")
            ];

            print_r($e->getMessage());
            die();
        }

        return redirect('freteGratis')->with('status', $output);
    }

}
