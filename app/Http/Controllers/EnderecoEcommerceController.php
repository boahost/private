<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EnderecoEcommerce;
use App\Models\ClienteEcommerce;
use App\Models\City;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;

class EnderecoEcommerceController extends Controller
{
	public function __construct(ModuleUtil $moduleUtil)
	{
		$this->moduleUtil = $moduleUtil;
	}

	public function index($clienteId){
		if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');

		if (request()->ajax()) {
			$enderecos = EnderecoEcommerce::where('cliente_id', $clienteId)
			->select(['id', 'rua', 'numero', 'bairro', 'cidade', 
				'uf', 'cep', 'complemento']);


			return Datatables::of($enderecos)

			->addColumn(
				'action',
				'<a href="/enderecosEcommerce/edit/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
				&nbsp'
			)

			->removeColumn('id')
			->rawColumns(['action'])
			->make(true);

		}

		$cliente = ClienteEcommerce::where('business_id', $business_id)
		->findOrFail($clienteId);
		if($cliente == null){
			abort(403, 'Unauthorized action.');
		}
		return view('enderecos.list')
		->with('cliente', $cliente);

	}

	public function edit($id){
		$endereco = EnderecoEcommerce::find($id);

		$cidade = City::
		where('nome', $endereco->cidade)
		->first();

		return view('enderecos.edit')
		->with('cities', $this->prepareCities())
		->with('cidade', $cidade)
		->with('endereco', $endereco);
	}

	private function prepareCities(){
		$cities = City::all();
		$temp = [];
		foreach($cities as $c){
			$temp[$c->id] = $c->nome . " ($c->uf)";
		}
		return $temp;
	}

	public function update(Request $request){
		try{
			$endereco = EnderecoEcommerce::find($request->id);

			$cidade = City::find($request->city_id);

			$endereco->rua = $request->rua;
			$endereco->numero = $request->numero;
			$endereco->bairro = $request->bairro;
			$endereco->cep = $request->cep;
			$endereco->complemento = $request->complemento ?? '';
			$endereco->cidade = $cidade->nome;
			$endereco->uf = $cidade->uf;

			$endereco->save();

			$output = [
				'success' => 1,
				'msg' => 'Endereço atualizado!!'
			];
		}catch (\Exception $e) {
			\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

			$output = [
				'success' => 0,
				'msg' => __("messages.something_went_wrong")
			];

		}

		return redirect()->back()->with('status', $output);
	}

}
