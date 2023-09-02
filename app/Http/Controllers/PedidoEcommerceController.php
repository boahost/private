<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PedidoEcommerce;
use App\Models\Transaction;
use App\Models\TransactionSellLine;
use App\Models\City;
use App\Models\Contact;
use App\Models\ConfigEcommerce;
use App\Models\NaturezaOperacao;
use App\Models\Transportadora;
use App\Models\TransactionPayment;
use App\Models\BusinessLocation;
use Yajra\DataTables\Facades\DataTables;

class PedidoEcommerceController extends Controller
{
	public function index(){

		if (!auth()->user()->can('user.view') && !auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		if (request()->ajax()) {
			$business_id = request()->session()->get('user.business_id');
			$user_id = request()->session()->get('user.id');
			$pedidos = PedidoEcommerce::where('cliente_ecommerces.business_id', $business_id)
			->orderBy('pedido_ecommerces.id', 'desc')
			->where('pedido_ecommerces.status', '>', '0')
			->join('cliente_ecommerces', 'cliente_ecommerces.id', '=', 
				'pedido_ecommerces.cliente_id')

			->select(['cliente_ecommerces.nome', 'pedido_ecommerces.valor_total', 
				'pedido_ecommerces.created_at', 'pedido_ecommerces.forma_pagamento', 
				'pedido_ecommerces.status_preparacao', 'pedido_ecommerces.id']);

			if (!empty(request()->start_date) && !empty(request()->end_date)) {
				$start = request()->start_date;
				$end =  request()->end_date;
				$pedidos->whereDate('pedido_ecommerces.created_at', '>=', $start)
				->whereDate('pedido_ecommerces.created_at', '<=', $end);
			}

			return Datatables::of($pedidos)

			// ->addColumn(
			// 	'cliente',
			// 	function ($row) {
			// 		$teste = "teste";

			// 		return $row->cliente->nome . " " . $row->cliente->sobre_nome ;
			// 	}
			// )

			->addColumn(
				'action',
				function ($row){
					$html = '<a href="/pedidosEcommerce/ver/'. $row->id .'" class="btn btn-xs btn-primary"><i class="fa fa-th-list" aria-hidden="true"></i></a>
					&nbsp;';
					return $html;
				}
			)

			->editColumn(
				'created_at',
				function ($row){
					return \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
				}
			)

			->editColumn(
				'valor_total',
				function ($row){
					return "R$ " . number_format($row->valor_total, 2, ',', '.');
				}
			)

			->editColumn(
				'status_preparacao',
				function ($row){
					if($row->status_preparacao == 0){
						return "<span class='text-primary'>Novo</span>";
					}else if($row->status_preparacao == 1){
						return "<span class='text-info'>Aprovado</span>";
					}else if($row->status_preparacao == 2){
						return "<span class='text-danger'>Cancelado</span>";
					}else if($row->status_preparacao == 3){
						return "<span class='text-warning'>Aguardando Envio</span>";
					}else if($row->status_preparacao == 4){
						return "<span class='text-dark'>Enviado</span>";
					}else if($row->status_preparacao == 5){
						return "<span class='text-success'>Entregue</span>";
					}
				}
			)

			->removeColumn('cliente_ecommerces.id')
			->rawColumns(['action', 'status_preparacao'])
			->make(true);

		}
		return view('pedidos.list');

	}

	public function ver($id){
		$business_id = request()->session()->get('user.business_id');

		$pedido = PedidoEcommerce::
		where('business_id', $business_id)
		->where('id', $id)
		->firstOrFail();

		if($pedido == null){
			abort(403, 'Unauthorized action.');
		}

		return view('pedidos.ver')
		->with('pedido', $pedido);

	}

	public function salvarCodigo(Request $request){
		try{
			$pedido = PedidoEcommerce::find($request->id);
			$pedido->codigo_rastreio = $request->codigo;
			$pedido->save();
			return response()->json("ok", 200);

		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function gerarNFe($id){

		$business_id = request()->session()->get('user.business_id');

		$pedido = PedidoEcommerce::
		where('business_id', $business_id)
		->where('id', $id)
		->firstOrFail();

		if($pedido == null){
			abort(403, 'Unauthorized action.');
		}

		$erros = [];
		$doc = $pedido->cliente->cpf;

		if(strlen($doc) == 14){
			if(!$this->validaCPF($doc)){
				array_push($erros, "CPF cliente inválido");
			}
		}

		if(strlen($doc) == 18){
			if(!$this->validaCNPJ($doc)){
				array_push($erros, "CNPJ cliente inválido");
			}
		}

		$cidade = City::
		where('nome', $pedido->endereco->cidade)
		->first();

		if($cidade == null){
			array_push($erros, "Cidade cliente inválida");
		}

		$tiposFrete = [
			'0' => 'Emitente',
			'1' => 'Destinatário',
			'2' => 'Terceiros',
			'9' => 'SemFrete'
		];

		$business_locations = BusinessLocation::forDropdown($business_id);

		return view('pedidos/emitir_nfe')
		->with('pedido', $pedido)
		->with('erros', $erros)
		->with('cidade', $cidade)
		->with('tiposFrete', $tiposFrete)
		->with('business_locations', $business_locations)
		->with('ufs', $this->prepareUFs())
		->with('cidades', $this->prepareCities())
		->with('naturezas', $this->prepareNaturezas())
		->with('transportadoras', $this->prepareTransportadoras());

	}

	private function prepareCities(){
		$cities = City::all();
		$temp = [];
		foreach($cities as $c){
            // array_push($temp, $c->id => $c->nome);
			$temp[$c->id] = $c->nome . " ($c->uf)";
		}
		return $temp;
	}

	private function preparePaises(){
		$paises = Pais::all();
		$temp = [];
		foreach($paises as $p){
            // array_push($temp, $c->id => $c->nome);
			$temp[$p->codigo] = "$p->codigo - $p->nome";
		}
		return $temp;
	}

	private function prepareNaturezas(){
		$business_id = request()->session()->get('user.business_id');

		$naturezas = NaturezaOperacao::
		where('business_id', $business_id)
		->where('finNFe', '!=', 4)
		->get();

		$temp = [];
		foreach($naturezas as $c){
			$temp[$c->id] = $c->natureza;
		}
		return $temp;
	}

	private function prepareTransportadoras(){
		$business_id = request()->session()->get('user.business_id');

		$transportadoras = Transportadora::
		where('business_id', $business_id)
		->get();
		$temp = [];
		foreach($transportadoras as $t){

			$temp[$t->id] = $t->razao_social;
		}
		return $temp;
	}

	private function prepareUFs(){
		return [
			"AC"=> "AC",
			"AL"=> "AL",
			"AM"=> "AM",
			"AP"=> "AP",
			"BA"=> "BA",
			"CE"=> "CE",
			"DF"=> "DF",
			"ES"=> "ES",
			"GO"=> "GO",
			"MA"=> "MA",
			"MG"=> "MG",
			"MS" => "MS",
			"MT" => "MT",
			"PA" => "PA",
			"PB" => "PB",
			"PE" => "PE",
			"PI" => "PI",
			"PR" => "PR",
			"RJ" => "RJ",
			"RN" => "RN",
			"RS" => "RS",
			"RO" => "RO",
			"RR" => "RR",
			"SC" => "SC",
			"SE" => "SE",
			"SP" => "SP",
			"TO" => "TO"

		];

	}

	private function validaCPF($cpf){

		$cpf = preg_replace( '/[^0-9]/is', '', $cpf );
    // Verifica se foi informado todos os digitos corretamente
		if (strlen($cpf) != 11) {
			return false;
		}

    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
		if (preg_match('/(\d)\1{10}/', $cpf)) {
			return false;
		}

    // Faz o calculo para validar o CPF
		for ($t = 9; $t < 11; $t++) {
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}
		return true;
	}

	private function validaCNPJ($cnpj){

		$cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

    // Valida tamanho
		if (strlen($cnpj) != 14)
			return false;

    // Verifica se todos os digitos são iguais
		if (preg_match('/(\d)\1{13}/', $cnpj))
			return false;   

    // Valida primeiro dígito verificador
		for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
			return false;

    // Valida segundo dígito verificador
		for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
		{
			$soma += $cnpj[$i] * $j;
			$j = ($j == 2) ? 9 : $j - 1;
		}

		$resto = $soma % 11;

		return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
	}

	public function salvarVenda(Request $request){
		$pedido = PedidoEcommerce::find($request->id);
		$transportadora = $request->transportadora ?? NULL;
		$natureza = $request->natureza;

		$cliente = $this->salvarCliente($pedido);

		$tipoPagamento = '01';
		if($pedido->forma_pagamento == 'Pix'){
			$tipoPagamento = '17';
		}elseif($pedido->forma_pagamento == 'Boleto'){
			$tipoPagamento = '15';
		}else{
			$tipoPagamento = '03';
		}

		$business_id = request()->session()->get('user.business_id');
		$user_id = request()->session()->get('user.id');
		try{
			$dataVenda = [
				'business_id' => $business_id,
				'type' => 'sell',
				'status' => 'final',
				'is_direct_sale' => 1,
				'payment_status' => $tipoPagamento == 'boleto' ? 'due' : 'paid',
				'contact_id' => $cliente->id,
				'transaction_date' => date('Y-m-d H:i:s'),
				'created_by' => $user_id,
				'estado' => 'NOVO',
				'location_id' => $request->location_id,
				'final_total' => $pedido->valor_total,
				'total_before_tax' => $pedido->valor_total,
				'discount_amount' => 0,
				'discount_type' => NULL,
				'natureza_id' => $natureza,
				'tipo' => $request->frete,
				'valor_frete' => $request->valor_frete,
				'placa' => $request->placa ?? '',
				'uf' => $request->uf_placa ?? '',
				'qtd_volumes' => $request->qtd_volumes ?? 0,
				'numeracao_volumes' => $request->numeracao_volumes ?? 0,
				'especie' => $request->especie ?? '',
				'peso_liquido' => $request->peso_liquido ?? 0,
				'peso_bruto' => $request->peso_bruto ?? 0,
				'pedido_ecommerce_id' => $pedido->id
			];

			$venda = Transaction::create($dataVenda);

			foreach($pedido->itens as $i){

				$dataItemSell = [
					'transaction_id' => $venda->id,
					'product_id'=> $i->produto->id,
					'variation_id' => $i->variacao_id > 0 ? $i->variacao_id : $i->produto->product_variations[0]->id,
					'quantity' => $i->quantidade,
					'unit_price' => $i->variacao_id > 0 ? $i->variacao->default_sell_price : $i->produto->valor_ecommerce
				];

				$res = TransactionSellLine::create($dataItemSell);
			}

			// $dataFatura = [
			// 	'business_id' => $business_id,
			// 	'type' => 'expense',
			// 	'status' => 'final',
			// 	'payment_status' => 'due',
			// 	'contact_id' => $cliente->id,
			// 	'transaction_date' => date('Y-m-d'),
			// 	'created_by' => $user_id,
			// 	'numero_nfe_entrada' => '',
			// 	'chave' => '',
			// 	'estado' => '',
			// 	'location_id' => $request->location_id,
			// 	'ref_no' => 'PEDIDO_' . $pedido->id,
			// 	'final_total' => $pedido->valor_total,
			// 	'total_before_tax' => $pedido->valor_total,
			// 	'discount_amount' => 0,
			// 	'discount_type' => NULL
			// ];

			// $fatura = Transaction::create($dataFatura);

			$formaPagamento = '';

			if(strtolower($pedido->forma_pagamento) == 'boleto'){
				$formaPagamento = 'boleto';
			}else if(strtolower($pedido->forma_pagamento) == 'cartão'){
				$formaPagamento = 'card';
			}else{
				$formaPagamento = 'other';
			}

			$payment_data = [
				'amount' => $pedido->valor_total,
				'method' => $formaPagamento,
				'business_id' => $business_id,
				'is_return' => 0,
				'transaction_id' => $venda->id,
				'vencimento' => date('Y-m-d'),

				'paid_on' => !empty($payment['paid_on']) ? $payment['paid_on'] : \Carbon::now()->toDateTimeString(),
				'created_by' => $user_id,
				'payment_for' => $cliente->id,
				'payment_ref_no' => 'PEDIDO_' . $pedido->id,
				'account_id' => null
			];
			TransactionPayment::create($payment_data);

			$output = [
				'success' => 1,
				'msg' => "Venda gerada"
				// 'msg' => __('messages.something_went_wrong')
			];

			return redirect('sells')->with('status', $output);

		}catch(\Exception $e){
			$output = [
				'success' => 0,
				'msg' => $e->getMessage()
				// 'msg' => __('messages.something_went_wrong')
			];

			// echo $e->getMessage();
			// die;

			return redirect()->back()->with('status', $output);


		}


	}

	private function salvarCliente($pedido){

		$cliente = $pedido->cliente;
		$endereco = $pedido->endereco;

		$clienteExist = Contact::
		where('cpf_cnpj', $cliente->cpf)
		->first();

		$cidade = City::
		where('nome', $endereco->cidade)
		->first();

		$business_id = request()->session()->get('user.business_id');
		$user_id = request()->session()->get('user.id');

		if($clienteExist == null){
            //criar novo
			
			$data = [
				'type' => 'customer',
				'supplier_business_name' => "$cliente->nome $cliente->sobre_nome",
				'cpf_cnpj' => $cliente->cpf,
				'ie_rg' => '',
				'contribuinte' => 1,
				'consumidor_final' => 1,
				'rua' => $endereco->rua,
				'numero' => $endereco->numero,
				'bairro' => $endereco->bairro,
				'cep' => $endereco->cep,
				'name' => "$cliente->nome $cliente->sobre_nome",
				'tax_number' => '',
				'pay_term_number' => '',
				'pay_term_type' => '',
				'mobile' => '',
				'landline' => '',
				'alternate_number' => '',
				'city' => '',
				'state' => '',
				'country' => '',
				'landmark' => '',
				'customer_group_id' => '',
				'contact_id' => '',
				'custom_field1' => '',
				'custom_field2' => '',
				'custom_field3' => '',
				'custom_field4' => '',
				'email' => $cliente->email,
				'shipping_address' => '',
				'position' => '',
				'city_id' => $cidade->id,  
				'business_id' => $business_id,  
				'cod_pais' => '1058', 
				'id_estrangeiro' => '',
				'created_by' => $user_id
			];

			// print_r($data);

			return Contact::create($data);

		}else{
            //atualiza endereço
			$clienteExist->rua = $endereco->rua;
			$clienteExist->numero = $endereco->numero;
			$clienteExist->bairro = $endereco->bairro;
			$clienteExist->cep = $endereco->cep;
			$clienteExist->city_id = $cidade->id;

			$clienteExist->save();
			return $clienteExist;
		}

	}

	public function consultarPagamentos(){
		$business_id = request()->session()->get('user.business_id');

		$config = ConfigEcommerce::
		where('business_id', $business_id)
		->first();

		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		$pedidos = PedidoEcommerce::
		where('business_id', $business_id)
		->where('status', '!=', '0')
		->where('status_pagamento', 'pending')
		->limit(100)
		->get();

		$pedidosAlterados = [];
		foreach($pedidos as $pedido){
			$payStatus = \MercadoPago\Payment::find_by_id($pedido->transacao_id);

			if($payStatus->status == "approved"){
				$pedido->status_pagamento = "approved";
				$pedido->status = 2; // confirmado o pagamento;
				$pedido->save();
				array_push($pedidosAlterados, $pedido);
			}
		}

		return view('pedidos.alterados')
		->with('pedidos', $pedidosAlterados);

	}
}
