<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClienteEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\System;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use Illuminate\Support\Str;
use App\Rules\ValidaDocumento;

class ClienteEcommerceController extends Controller
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
			$clientes = ClienteEcommerce::where('business_id', $business_id)
			->select(['id', 'nome', 'sobre_nome', 'telefone', 'email', 'cpf', 'status'])
			->orderBy('id', 'desc');

			return Datatables::of($clientes)

			->addColumn(
				'action',
				// '<a href="/clienteEcommerce/edit/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
				// &nbsp;<button data-href="/clienteEcommerce/delete/{{$id}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>&nbsp;<a href="/enderecosEcommerce/{{$id}}" class="btn btn-xs btn-info"><i class="glyphicon glyphicon-map-marker"></i> Endereços</a>'
				function($row){
					$html = '<div class="btn-group">
					<button type="button" class="btn btn-info dropdown-toggle btn-xs" 
					data-toggle="dropdown" aria-expanded="false">' .
					__("messages.actions") .
					'<span class="caret"></span><span class="sr-only">Toggle Dropdown
					</span>
					</button>
					<ul class="dropdown-menu dropdown-menu-left" role="menu">' ;


					$html .= '<li><a href="/clienteEcommerce/edit/'. $row->id .'"><i class="glyphicon glyphicon-edit"></i>editar</a></li>';

					$html .= '<li><a href="/clienteEcommerce/delete/'. $row->id .'"><i class="glyphicon glyphicon-trash"></i>remover</a></li>';

					$html .= '<li><a href="/enderecosEcommerce/'. $row->id .'"><i class="glyphicon glyphicon-map-marker"></i>endereços</a></li>';

					$html .= '<li><a href="/clienteEcommerce/pedidos/'. $row->id .'"><i class="glyphicon glyphicon-list"></i>pedidos</a></li>';

					$html .= '</ul></div>';

					return $html;
				}
			)

			->editColumn(
				'status', function($row){
					if($row->status){
						return "<i class='fa fa-check text-success'></i>";
					}else{
						return "<i class='fa fa-check text-danger'></i>";
					}
				}
			)

			->addColumn(
				'pedidos', function($row){
					return sizeof($row->pedidos());
				}
			)

			->removeColumn('id')
			->rawColumns(['action', 'status'])
			->make(true);

		}
		return view('clientes_ecommerce.list');

	}

	public function create(){
		// return view('naturezas.register');

		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');

		return view('clientes_ecommerce.register');
	}

	public function save(Request $request){

		$this->_validate($request);
		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		try {
			$cliente = $request->only(['nome', 'sobre_nome', 'email',
				'senha', 'telefone', 'status', 'cpf']);


			$business_id = $request->session()->get('user.business_id');
			$cliente['business_id'] = $business_id;
			$cliente['token'] = Str::random(20);
			$cliente['senha'] = md5($cliente['senha']);

			$cli = ClienteEcommerce::create($cliente);

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

		return redirect('clienteEcommerce')->with('status', $output);
	}

	public function edit($id){
		// return view('naturezas.register');

		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');


		$cliente = ClienteEcommerce::where('business_id', $business_id)->findOrFail($id);


		if($cliente == null){
			abort(403, 'Unauthorized action.');
		}

		$tipo = strlen($cliente->cpf) == 11 ? 'f' : 'j';

		return view('clientes_ecommerce.edit')
		->with('tipo', $tipo)
		->with('cliente', $cliente);
	}

	public function update(Request $request, $id)
	{
		$this->_validate($request, true);

		if (!auth()->user()->can('category.update')) {
			abort(403, 'Unauthorized action.');
		}

        // if (request()->ajax()) {
		try {
			$input = $request->only(['nome', 'sobre_nome', 'telefone', 'email', 'cpf', 'status',
				'senha']);
			$business_id = $request->session()->get('user.business_id');


			$cliente = ClienteEcommerce::where('business_id', $business_id)->findOrFail($id);


            // $input['image'] = $this->moduleUtil->uploadFile($request, 'image', 'img/categorias', 'image');
			$doc = $input['cpf'];
			$doc = str_replace(".", "", $doc);
			$doc = str_replace("/", "", $doc);
			$doc = str_replace("-", "", $doc);
			$doc = str_replace(" ", "", $doc);
			$cliente->nome = $input['nome'];
			$cliente->sobre_nome = $input['sobre_nome'];
			$cliente->telefone = $input['telefone'];
			$cliente->email = $input['email'];
			$cliente->ie = $input['ie'] ?? '';
			$cliente->cpf = $doc;
			$cliente->status = $input['status'];

			if($input['senha'] != ""){
				$cliente->senha = md5($input['senha']);
			}

			$cliente->save();

			$output = [
				'success' => true,
				'msg' => 'Sucesso!!'
			];
		} catch (\Exception $e) {
			\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

			$output = [
				'success' => false,
				'msg' => __("messages.something_went_wrong")
			];
		}

            // return $output;
		return redirect('/clienteEcommerce')->with('status', $output);

        // }
	}

	private function _validate(Request $request, $edit = false){
		$rules = [
			'nome' => 'required|max:30',
			'sobre_nome' => 'required|max:30',
			'email' => ['required', 'email', 'max:60', \Illuminate\Validation\Rule::unique('cliente_ecommerces')->ignore($request->id)],
			'cpf' => ['required', 'max:18', new ValidaDocumento, \Illuminate\Validation\Rule::unique('cliente_ecommerces')->ignore($request->id)],
			'telefone' => 'required|max:15',
			'senha' => $edit == false ? 'required|min:4' : '',
		];

		$messages = [

			'nome.required' => 'O campo nome é obrigatório.',
			'nome.max' => '30 caracteres maximos permitidos.',
			'sobre_nome.required' => 'O campo sobre nome é obrigatório.',
			'sobre_nome.max' => '30 caracteres maximos permitidos.',

			'email.required' => 'O campo email é obrigatório.',
			'email.max' => '120 caracteres maximos permitidos.',
			'email.email' => 'Email inválido.',
			'email.unique' => 'Email já registrado.',

			'cpf.required' => 'O campo CPF é obrigatório.',
			'cpf.max' => '18 caracteres maximos permitidos.',
			'cpf.unique' => 'Documento já registrado.',

			'telefone.required' => 'O campo Telefone é obrigatório.',
			'telefone.max' => '15 caracteres maximos permitidos.',
			'senha.required' => 'O campo Senha é obrigatório.',
			'senha.min' => 'Minimo de 4 caracteres'

		];
		$this->validate($request, $rules, $messages);
	}

	public function delete($id)
	{
		if (!auth()->user()->can('category.delete')) {
			abort(403, 'Unauthorized action.');
		}

		if (request()->ajax()) {
			try {
				$business_id = request()->session()->get('user.business_id');

				$cliente = ClienteEcommerce::where('business_id', $business_id)->findOrFail($id);


				$cliente->delete();

				$output = [
					'success' => true,
					'msg' => 'Registro removido!!'
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

	public function pedidos($id){
		try {
			$business_id = request()->session()->get('user.business_id');

			if (request()->ajax()) {
				$pedidos = PedidoEcommerce::
				select('pedido_ecommerces.id as id', 'pedido_ecommerces.valor_total', 
					'pedido_ecommerces.forma_pagamento', 'pedido_ecommerces.status',
					'pedido_ecommerces.created_at')
				->join('cliente_ecommerces', 'cliente_ecommerces.id', '=', 
					'pedido_ecommerces.cliente_id')
				->where('cliente_ecommerces.id', $id)
				->orderBy('pedido_ecommerces.id', 'desc');

				return Datatables::of($pedidos)

				->addColumn(
					'action',
					'<a href="/pedidosEcommerce/ver/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-list"></i> ver pedido</a>
					&nbsp;'
				)

				->addColumn(
					'created_at',
					function($row){
						return \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
					}
				)

				->removeColumn('id')
				->rawColumns(['action'])
				->make(true);
			}else{
				$cliente = ClienteEcommerce::where('business_id', $business_id)
				->findOrFail($id);

				if($cliente == null){
					abort(403, 'Unauthorized action.');
				}

				return view('clientes_ecommerce.pedidos')
				->with('cliente', $cliente);
			}
		} catch (\Exception $e) {
			\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

			
		}
	}
}
