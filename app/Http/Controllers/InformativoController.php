<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformativoEcommerce;
use Yajra\DataTables\Facades\DataTables;

class InformativoController extends Controller
{
	public function index(){
		if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		if (request()->ajax()) {
			$business_id = request()->session()->get('user.business_id');
			$user_id = request()->session()->get('user.id');
			$contatos = InformativoEcommerce::where('business_id', $business_id)
			->select(['id', 'email']);


			return Datatables::of($contatos)

			->removeColumn('id')
			->rawColumns(['action'])
			->make(true);

		}
		return view('informativo.list');

	}
}
