<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cte;
use App\Models\Veiculo;
use App\Models\Contact;
use App\Models\City;
use App\Models\MedidaCte;
use App\Models\ComponenteCte;
use App\Models\NaturezaOperacao;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Models\System;
use App\Models\BusinessLocation;

use NFePHP\DA\CTe\Dacte;
use NFePHP\DA\CTe\Daevento;
use App\Services\CTeService;
use App\Models\Business;

class CteController extends Controller
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
			$ctes = Cte::where('business_id', $business_id)
			->select(['id', 'valor_transporte', 'valor_receber', 'valor_carga', 'produto_predominante', 'data_previsata_entrega', 'estado', 'remetente_id', 'destinatario_id', 'created_at']);


			return Datatables::of($ctes)

			// ->addColumn(
			// 	'action',
			// 	'<a href="/cte/edit/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
			// 	&nbsp;<a href="/cte/delete/{{$id}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a> '
			// )
			->editColumn('data_previsata_entrega', function ($row) {
				return \Carbon\Carbon::parse($row->data_previsata_entrega)->format('d/m/Y');
			})

			->editColumn('created_at', function ($row) {
				return \Carbon\Carbon::parse($row->created_at)->format('d/m/Y H:i');
			})

			->editColumn('valor_carga', function ($row) {
				return number_format($row->valor_carga, 2, ',', '');
			})

			->editColumn('valor_transporte', function ($row) {
				return number_format($row->valor_transporte, 2, ',', '');
			})

			->editColumn('valor_receber', function ($row) {
				return number_format($row->valor_receber, 2, ',', '');
			})

			->addColumn('remetente', function ($row) {
				$rem = Contact::find($row->remetente_id);
				return $rem->name;
			})

			->addColumn('destinatario', function ($row) {
				$dest = Contact::find($row->destinatario_id);
				return $dest->name;
			})

			->addColumn('action', function ($row) {
				$t = Cte::find($row->id);
				$html = '';
				$html = '<form id="cte'.$row['id'].'" method="POST" action="'. route('cte.destroy', $row['id']) .'">';

				if($t->cte_numero > 0){
					$html .= '<a class="btn btn-xs btn-info" href="'. route('cte.ver', $row['id']) .'">Ver CTe</a>';
				}else{
					$html .= '<a class="btn btn-xs btn-primary" href="'. route('cte.gerar', $row['id']) .'">Gerar CTe</a>';
					$html .= '&nbsp;<a class="btn btn-xs btn-warning" href="'. route('cte.edit', $row['id']) .'">Editar</a>';
				}

				$html .= '&nbsp;<button type="button" class="btn btn-xs btn-danger btn-delete"> Excluir</button>
				'. method_field('DELETE') .'
				'. csrf_field() .'
				</form>';
				return $html;
			})


			->removeColumn('id')
			->rawColumns(['action', 'remetente', 'destinatario'])
			->make(true);

		}
		return view('cte.index');
	}

	public function create(){

		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');

		$tipos = Veiculo::tipos();
		$tiposRodado = Veiculo::tiposRodado();
		$tiposCarroceria = Veiculo::tiposCarroceria();
		$tiposProprietario = Veiculo::tiposProprietario();
		$ufs = Veiculo::cUF();

        //Check if subscribed or not, then check for users quota
		if (!$this->moduleUtil->isSubscribed($business_id)) {
			return $this->moduleUtil->expiredResponse();
		} elseif (!$this->moduleUtil->isQuotaAvailable('naturezas', $business_id)) {
			return $this->moduleUtil->quotaExpiredResponse('naturezas', $business_id, action('NaturezaController@index'));
		}

		$roles  = $this->getRolesArray($business_id);
		$username_ext = $this->getUsernameExtension();
		
        //Get user form part from modules

		$lastCte = Cte::lastCTeAux($business_id);
		$unidadesMedida = Cte::unidadesMedida();
		$tiposMedida = Cte::tiposMedida();
		$tiposTomador = Cte::tiposTomador();
		$naturezas = $this->prepareNaturezas();
		$modals = Cte::modals();
		$veiculos = $this->prepareVeiculos();
		$clientesAux = Contact::where('business_id', $business_id)->get();

		foreach($clientesAux as $c){
			$c->cidade;
		}
		
		$clientes = $this->prepareClientes();
		$cidades = $this->prepareCities();

		$business_locations = BusinessLocation::forDropdown($business_id, false, true);
		$bl_attributes = $business_locations['attributes'];
		$business_locations = $business_locations['locations'];

		$default_location = null;
		if (count($business_locations) == 1) {
			foreach ($business_locations as $id => $name) {
				$default_location = BusinessLocation::findOrFail($id);
			}
		}

		return view('cte.create')
		->with(compact('roles', 'username_ext', 'cidades', 'clientesAux', 'clientes', 'lastCte', 'unidadesMedida', 'tiposMedida', 'tiposTomador', 'naturezas', 'modals', 'veiculos'))
		->with('bl_attributes' , $bl_attributes)
		->with('default_location' , $default_location)
		->with('business_locations' , $business_locations);
	}

	public function edit($id){

		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}
		$cte = Cte::find($id);

		$business_id = request()->session()->get('user.business_id');

		$tipos = Veiculo::tipos();
		$tiposRodado = Veiculo::tiposRodado();
		$tiposCarroceria = Veiculo::tiposCarroceria();
		$tiposProprietario = Veiculo::tiposProprietario();
		$ufs = Veiculo::cUF();

        //Check if subscribed or not, then check for users quota
		if (!$this->moduleUtil->isSubscribed($business_id)) {
			return $this->moduleUtil->expiredResponse();
		} elseif (!$this->moduleUtil->isQuotaAvailable('naturezas', $business_id)) {
			return $this->moduleUtil->quotaExpiredResponse('naturezas', $business_id, action('NaturezaController@index'));
		}

		$roles  = $this->getRolesArray($business_id);
		$username_ext = $this->getUsernameExtension();
		
        //Get user form part from modules

		$lastCte = Cte::lastCTeAux($business_id);
		$unidadesMedida = Cte::unidadesMedida();
		$tiposMedida = Cte::tiposMedida();
		$tiposTomador = Cte::tiposTomador();
		$naturezas = $this->prepareNaturezas();
		$modals = Cte::modals();
		$veiculos = $this->prepareVeiculos();
		$clientesAux = Contact::where('business_id', $business_id)->get();

		foreach($clientesAux as $c){
			$c->cidade;
		}
		
		$clientes =  $this->prepareClientes();
		$cidades =  $this->prepareCities();

		$business_locations = BusinessLocation::forDropdown($business_id, false, true);
		$bl_attributes = $business_locations['attributes'];
		$business_locations = $business_locations['locations'];

		$default_location = null;
		if (count($business_locations) == 1) {
			foreach ($business_locations as $id => $name) {
				$default_location = BusinessLocation::findOrFail($id);
			}
		}

		return view('cte.edit')
		->with(compact('roles', 'username_ext', 'cidades', 'clientesAux', 'clientes', 'lastCte', 'unidadesMedida', 'tiposMedida', 'tiposTomador', 'naturezas', 'modals', 'veiculos'))
		->with('bl_attributes' , $bl_attributes)
		->with('cte' , $cte)
		->with('default_location' , $default_location)
		->with('business_locations' , $business_locations);
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

	private function prepareNaturezas(){
		$business_id = request()->session()->get('user.business_id');

		$naturezas = NaturezaOperacao::
		where('business_id', $business_id)
		->get();
		$temp = [];
		foreach($naturezas as $c){
			$temp[$c->id] = $c->natureza;
		}
		return $temp;
	}

	private function prepareVeiculos(){
		$business_id = request()->session()->get('user.business_id');

		$veiculos = Veiculo::
		where('business_id', $business_id)
		->get();
		$temp = [];
		foreach($veiculos as $v){
			$temp[$v->id] = "$v->placa - $v->modelo";
		}
		return $temp;
	}

	private function prepareClientes(){
		$business_id = request()->session()->get('user.business_id');

		$clientes = Contact::
		where('business_id', $business_id)
		->orderBy('name')
		->get();

		$temp = [];
		foreach($clientes as $c){
			if($c->name != 'Cliente padrão')
				$temp[$c->id] = $c->name . " ($c->cpf_cnpj)";
		}
		return $temp;
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

	public function update(Request $request, $id){
		$business_id = request()->session()->get('user.business_id');
		$user_id = request()->session()->get('user.id');
		try{

			$chaves = $request->chaves_nfe ?? '';
			if($chaves != ''){
				$chaves = str_replace(",", ";", $chaves);
			} 

			$cte = Cte::find($id);
			$config = Business::find($business_id);

			$cte->chave_nfe = $chaves;
			$cte->remetente_id = $request->remetente_id;
			$cte->destinatario_id = $request->destinatario_id;
			$cte->recebedor_id = $request->recebedor_id ?? null;
			$cte->expedidor_id = $request->expedidor_id ?? null;
			$cte->usuario_id = $user_id;
			$cte->natureza_id = $request->natureza_id;
			$cte->tomador = $request->tomador;
			$cte->municipio_envio = $request->cidade_envio;
			$cte->municipio_inicio = $request->cidade_inicio;
			$cte->municipio_fim = $request->cidade_fim;
			$cte->logradouro_tomador = $request->rua_tomador;
			$cte->numero_tomador = $request->numero_tomador;
			$cte->bairro_tomador = $request->bairro_tomador;
			$cte->cep_tomador = $request->cep_tomador;
			$cte->municipio_tomador = $request->cidade_tomador;
			$cte->observacao = $request->observacao ?? '';
			$cte->data_previsata_entrega = $this->parseDate($request->data_prevista_entrega);
			$cte->produto_predominante = $request->prod_predominante;
			$cte->cte_numero = 0;
			$cte->sequencia_cce = 0;
			$cte->chave = '';
			$cte->path_xml = '';
			$cte->estado = 'DISPONIVEL';

			$cte->valor_transporte = str_replace(",", ".", $request->valor_transporte);
			$cte->valor_receber = str_replace(",", ".", $request->valor_receber);
			$cte->valor_carga = str_replace(",", ".", $request->valor_carga);

			$cte->retira = $request->retira;
			$cte->detalhes_retira = $request->detalhes_retira ?? '';
			$cte->modal = $request->modal_transp;
			$cte->veiculo_id = $request->veiculo_id;
			$cte->tpDoc = $request->tpDoc ?? '';
			$cte->descOutros = $request->descOutros ?? '';
			$cte->nDoc = $request->nDoc ?? 0;
			$cte->vDocFisc = $request->vDocFisc ? str_replace(",", ".", $request->vDocFisc) : 0;
			$cte->globalizado = $request->globalizado ?? 0;
			$cte->cst = $request->cst;
			$cte->perc_icms = str_replace(",", ".", $request->perc_icms);
			$cte->location_id = $request->select_location_id ?? $config->locations[0]->id;

			$cte->save();

			$medidas = json_decode($request->medidas);
			foreach($cte->medidas as $m){
				$m->delete();
			}
		// print_r($medidas);
			foreach($medidas as $m){
				$medida = MedidaCte::create([
					'cod_unidade' => $m->unidade_medida,
					'tipo_medida'=> $m->tipo_medida,
					'quantidade_carga' => str_replace(",", ".", $m->quantidade),
					'cte_id' => $cte->id
				]);

			}

			$componentes = json_decode($request->componentes);

			foreach($cte->componentes as $c){
				$c->delete();
			}
		// print_r($medidas);
			foreach($componentes as $c){
				$componente = ComponenteCte::create([
					'nome' => $c->nome,
					'valor' => str_replace(",", ".", $c->valor),
					'cte_id' => $cte->id
				]);

			}
			$output = [
				'success' => 1,
				'msg' => "Cte atualizada!!"
			];

		}catch(\Exception $e){
			$output = [
				'success' => false,
				'msg' => "Erro ao criar CTe"
			];

			echo $e->getMessage();
			die;
		}

		return redirect('cte')->with('status', $output);
	}

	public function store(Request $request){
		$business_id = request()->session()->get('user.business_id');
		$user_id = request()->session()->get('user.id');
		try{

			$chaves = $request->chaves_nfe ?? '';
			if($chaves != ''){
				$chaves = str_replace(",", ";", $chaves);
			} 

			$config = Business::find($business_id);

			$data = [
				'business_id' => $business_id,
				'chave_nfe' => $chaves,
				'remetente_id' => $request->remetente_id,
				'destinatario_id' => $request->destinatario_id,
				'recebedor_id' => $request->recebedor_id ?? null,
				'expedidor_id' => $request->expedidor_id ?? null,
				'usuario_id' => $user_id,
				'natureza_id' => $request->natureza_id,
				'tomador' => $request->tomador,
				'municipio_envio' => $request->cidade_envio,
				'municipio_inicio' => $request->cidade_inicio,
				'municipio_fim' => $request->cidade_fim,
				'logradouro_tomador' => $request->rua_tomador,
				'numero_tomador' => $request->numero_tomador,
				'bairro_tomador' => $request->bairro_tomador,
				'cep_tomador' => $request->cep_tomador,
				'municipio_tomador' => $request->cidade_tomador,
				'observacao' => $request->observacao ?? '',
				'data_previsata_entrega' => $this->parseDate($request->data_prevista_entrega),
				'produto_predominante' => $request->prod_predominante,
				'cte_numero' => 0,
				'sequencia_cce' => 0,
				'chave' => '',
				'path_xml' => '',
				'estado' => 'DISPONIVEL',

				'valor_transporte' => str_replace(",", ".", $request->valor_transporte),
				'valor_receber' => str_replace(",", ".", $request->valor_receber),
				'valor_carga' => str_replace(",", ".", $request->valor_carga),

				'retira' => $request->retira,
				'detalhes_retira' => $request->detalhes_retira ?? '',
				'modal' => $request->modal_transp,
				'veiculo_id' => $request->veiculo_id,
				'tpDoc' => $request->tpDoc ?? '',
				'descOutros' => $request->descOutros ?? '',
				'nDoc' => $request->nDoc ?? 0,
				'vDocFisc' => $request->vDocFisc ? str_replace(",", ".", $request->vDocFisc) : 0,
				'globalizado' => $request->globalizado ?? 0,
				'cst' => $request->cst,
				'perc_icms' => str_replace(",", ".", $request->perc_icms),
				'location_id' => $request->select_location_id ?? $config->locations[0]->id
			];

			$result = Cte::create($data);

			$medidas = json_decode($request->medidas);
		// print_r($medidas);
			foreach($medidas as $m){
				$medida = MedidaCte::create([
					'cod_unidade' => $m->unidade_medida,
					'tipo_medida'=> $m->tipo_medida,
					'quantidade_carga' => str_replace(",", ".", $m->quantidade),
					'cte_id' => $result->id
				]);

			}

			$componentes = json_decode($request->componentes);
		// print_r($medidas);
			foreach($componentes as $c){
				$componente = ComponenteCte::create([
					'nome' => $c->nome,
					'valor' => str_replace(",", ".", $c->valor),
					'cte_id' => $result->id
				]);

			}
			$output = [
				'success' => 1,
				'msg' => "Cte Gerada!!"
			];
		}catch(\Exception $e){
			$output = [
				'success' => false,
				'msg' => "Erro ao gerar CTe"
			];
		}

		return redirect('cte')->with('status', $output);
	}

	private function parseDate($date, $plusDay = false){
		if($plusDay == false)
			return date('Y-m-d', strtotime(str_replace("/", "-", $date)));
		else
			return date('Y-m-d', strtotime("+1 day",strtotime(str_replace("/", "-", $date))));
	}

	public function delete($id){
		if (!auth()->user()->can('user.delete')) {
			abort(403, 'Unauthorized action.');
		}

		try {
			$business_id = request()->session()->get('user.business_id');

			$cte = Cte::where('business_id', $business_id)
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

		return redirect('cte')->with('status', $output);
	}

	public function gerar($id){
		$business_id = request()->session()->get('user.business_id');

		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}


		if($cte->cte_numero > 0){
			return redirect('/cte/ver/'.$cte->id);
		}

		return view('cte.gerar')
		->with(compact('cte'));

	}

	public function renderizar($id){
		$business_id = request()->session()->get('user.business_id');

		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		// $config = Business::find($business_id);
		$config = Business::getConfigCte($business_id, $cte);

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		if($config->certificado == ""){
			return redirect('/cte')
			->with('status', [
				'success' => 0,
				'msg' => 'Cadastre o certificado do emitente.'
			]);
		}
		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $config);

		try{
			$doc = $cte_service->gerarCTe($cte);
			if(!isset($doc['erros_xml'])){
				$xml = $doc['xml'];
				$dacte = new Dacte($xml);
				// $dacte->monta();
				$pdf = $dacte->render();
				return response($pdf)
				->header('Content-Type', 'application/pdf');

			}else{
				foreach($doc['erros_xml'] as $e){
					echo $e . "<br>";
				}
			}

		}catch(\Excption $e){
			echo $e->getMessage();
		}
	}

	public function gerarXml($id){
		$business_id = request()->session()->get('user.business_id');

		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		// $config = Business::find($business_id);
		$config = Business::getConfigCte($business_id, $cte);

		$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

		if($config->certificado == ""){
			return redirect('/cte')
			->with('status', [
				'success' => 0,
				'msg' => 'Cadastre o certificado do emitente.'
			]);
		}

		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $config);

		try{
			$doc = $cte_service->gerarCTe($cte);
			if(!isset($doc['erros_xml'])){
				$xml = $doc['xml'];
				// $dacte = new Dacte($xml);
				// $dacte->monta();
				// $pdf = $dacte->render();
				return response($xml)
				->header('Content-Type', 'application/xml');

			}else{
				foreach($doc['erros_xml'] as $e){
					echo $e . "<br>";
				}
			}

		}catch(\Excption $e){
			echo $e->getMessage();
		}
	}

	public function transmitir(Request $request){
		$business_id = request()->session()->get('user.business_id');

		$cte = Cte::where('business_id', $business_id)
		->where('id', $request->id)
		->first();

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		// $config = Business::find($business_id);
		$config = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);


		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $config);


		if($cte->estado == 'REJEITADO' || $cte->estado == 'DISPONIVEL'){
			header('Content-type: text/html; charset=UTF-8');

			$doc = $cte_service->gerarCTe($cte);
			if(!isset($doc['erros_xml'])){
			// return response()->json($signed, 200);

				$signed = $cte_service->sign($doc['xml']);
			// return response()->json($signed, 200);
				$resultado = $cte_service->transmitir($signed, $doc['chave'], $cnpj);

				if(isset($resultado['successo'])){
					$cte->chave = $doc['chave'];
					$cte->cte_numero = $doc['nCte'];
					$cte->path_xml = $cte['chave'] . '.xml';
					$cte->estado = 'APROVADO';
					$cte->save();
					return response()->json($resultado, 200);

				}else{
					$cte->estado = 'REJEITADO';
					$cte->save();
					if(isset($resultado['protocolo'])){
						return response()->json($resultado['protocolo'], $resultado['status']);
					}else{
						return response()->json($resultado, 404);
					}
				}
			}else{
				return response()->json($doc['xml_erros'][0], 407);

			}

		}else{
			return response()->json("Este CTe já esta aprovada", 403);
		}
	}

	public function imprimir($id){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		// $business = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);
		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		$logo = '';
		if($business->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(
				public_path('uploads/business_logos/' . $business->logo)));
		}

		try {
			if(file_exists(public_path('xml_cte/'.$cnpj.'/'.$cte->chave.'.xml'))){
				$xml = file_get_contents(public_path('xml_cte/'.$cnpj.'/'.$cte->chave.'.xml'));

				$dacte = new Dacte($xml);

				// $dacte->creditsIntegratorFooter('WEBNFe Sistemas - http://www.webenf.com.br');
				// $dacte->monta();
				$pdf = $dacte->render($logo);

				return response($pdf)
				->header('Content-Type', 'application/pdf');
			}else{
				return redirect('/cte')
				->with('status', [
					'success' => 0,
					'msg' => 'Arquivo não encontrado!!'
				]);
			}
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  

	}

	public function imprimirCancelamento($id){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		// $business = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		$logo = '';
		if($business->logo){
			$logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(
				public_path('uploads/business_logos/' . $business->logo)));
		}

		try {
			if(file_exists(public_path('xml_cte_cancelada/'.$cnpj.'/'.$cte->chave.'.xml'))){
				$xml = file_get_contents(public_path('xml_cte_cancelada/'.$cnpj.'/'.$cte->chave.'.xml'));

				$dadosEmitente = $this->getEmitente($business);


				$dacte = new Daevento($xml, $dadosEmitente);

				$daevento = new Daevento($xml, $dadosEmitente);
				$daevento->debugMode(true);
				$pdf = $daevento->render($logo);

				return response($pdf)
				->header('Content-Type', 'application/pdf');
			}else{
				return redirect('/cte')
				->with('status', [
					'success' => 0,
					'msg' => 'Arquivo não encontrado!!'
				]);
			}
		} catch (InvalidArgumentException $e) {
			echo "Ocorreu um erro durante o processamento :" . $e->getMessage();
		}  

	}

	private function getEmitente($config){

		return [
			'razao' => $config->razao_social,
			'logradouro' => $config->rua,
			'numero' => $config->numero,
			'complemento' => '',
			'bairro' => $config->bairro,
			'CEP' => $config->cep,
			'municipio' => $config->cidade->nome,
			'UF' => $config->cidade->uf,
			'telefone' => '',
			'email' => ''
		];
	}


	public function ver($id){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}

		// $business = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);

		if($cte->cte_numero == 0){
			return redirect('/cte/gerar/'.$cte->id);
		}

		return view('cte.ver')
		->with(compact('cte', 'business'));
	}

	public function baixarXml($id){
		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		// $business = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}
		if(file_exists(public_path('xml_cte/'.$cnpj.'/'.$cte->chave.'.xml'))){
			return response()->download(public_path('xml_cte/'.$cnpj.'/'.$cte->chave.'.xml'));
		}else{
			return redirect()->back()
			->with('status', [
				'success' => 0,
				'msg' => 'Arquivo não encontrado!!'
			]);
		}
	}

	public function baixarXmlCancelado($id){
		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $id)
		->first();

		// $business = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		if(!$cte){
			abort(403, 'Unauthorized action.');
		}
		
		if(file_exists(public_path('xml_cte_cancelada/'.$cnpj.'/'.$cte->chave.'.xml'))){
			return response()->download(public_path('xml_cte_cancelada/'.$cnpj.'/'.$cte->chave.'.xml'));
		}else{
			return redirect()->back()
			->with('status', [
				'success' => 0,
				'msg' => 'Arquivo não encontrado!!'
			]);
		}
	}

	public function corrigir(Request $request){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $request->id)
		->first();

		// $config = Business::find($business_id);
		$business = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);


		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $business);


		$doc = $cte_service->cartaCorrecao($cte, $request->justificativa, $cnpj);
		if(!isset($doc['erro'])){
			return response()->json($nfe, 200);

		}else{
			return response()->json($nfe, $nfe['status']);
		}

	}

	public function cancelar(Request $request){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $request->id)
		->first();

		// $config = Business::find($business_id);
		$config = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);


		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $config);


		$doc = $cte_service->cancelar($cte, $request->justificativa, $cnpj);
		if(!isset($doc['erro'])){

			$cte->estado = 'CANCELADO';
			$cte->save();
			return response()->json($doc, 200);

		}else{
			return response()->json($doc, $doc['status']);
		}

	}


	//Arquivs XML

	public function xmls(){
		$business_id = request()->session()->get('user.business_id');
		$aprovadas = [];
		$canceladas = [];

		$business = Business::find($business_id);

		$business_locations = BusinessLocation::forDropdown($business_id, false, true);

		$bl_attributes = $business_locations['attributes'];

		$business_locations = $business_locations['locations'];
		// array_push($business_locations, "");


		$default_location = null;
		if (count($business_locations) == 1) {
			foreach ($business_locations as $id => $name) {
				$default_location = BusinessLocation::findOrFail($id);
			}
		}

		return view('cte.lista')
		->with(compact('canceladas', 'aprovadas', 'business'))
		->with('bl_attributes' , $bl_attributes)
		->with('default_location' , $default_location)
		->with('select_location_id' , null)
		->with('business_locations' , $business_locations);
	}

	public function filtroXml(Request $request){
		$data_inicio = str_replace("/", "-", $request->data_inicio);
		$data_final = str_replace("/", "-", $request->data_final);
		$select_location_id = $request->select_location_id;

		$data_inicio_convert =  \Carbon\Carbon::parse($data_inicio)->format('Y-m-d');
		$data_final_convert =  \Carbon\Carbon::parse($data_final)->format('Y-m-d');
		$data_final_convert = date('Y-m-d', strtotime($data_final_convert. ' + 1 days'));

		$business_id = request()->session()->get('user.business_id');

		$aprovadas = Cte::where('business_id', $business_id)
		->whereBetween('created_at', [
			$data_inicio_convert, 
			$data_final_convert])
		->where('cte_numero', '>', 0)
		->where('estado', 'APROVADO')
		->orderBy('id', 'desc');

		if($select_location_id){
			$aprovadas->where('location_id', $select_location_id);
		}
		$aprovadas = $aprovadas->get();

		$canceladas = Cte::where('business_id', $business_id)
		->whereBetween('created_at', [
			$data_inicio_convert, 
			$data_final_convert])
		->where('cte_numero', '>', 0)
		->where('estado', 'CANCELADO')
		->orderBy('id', 'desc');

		if($select_location_id){
			$canceladas->where('location_id', $select_location_id);
		}
		$canceladas = $canceladas->get();

		$business = Business::find($business_id);
		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);

		$msg = [];

		if(sizeof($aprovadas) > 0){
			try{
				$zip_file = public_path('xml_cte/'.$cnpj.'/'.'xml.zip');
				$zip = new \ZipArchive();
				$zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

				foreach($aprovadas as $n){

					if(file_exists(public_path('xml_cte/'.$cnpj.'/'.$n->chave.'.xml'))){
						$zip->addFile(public_path('xml_cte/'.$cnpj.'/'.$n->chave.'.xml'), $n->chave . '.xml');
					}

				}
				$zip->close();
			}catch(\Exception $e){
				array_push($msg, "Erro ao gerar arquivo de XML!!");
			}

		}

		if(sizeof($canceladas) > 0){

			try{
				$zip_file = public_path('xml_cte_cancelada/'.$cnpj.'/'.'xml_cancelado.zip');
				$zip = new \ZipArchive();
				$zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

				foreach($canceladas as $n){

					if(file_exists(public_path('xml_cte_cancelada/'.$cnpj.'/'.$n->chave.'.xml'))){
						$zip->addFile(public_path('xml_cte_cancelada/'.$cnpj.'/'.$n->chave.'.xml'), $n->chave . '.xml');
					}

				}
				$zip->close();
			}catch(\Exception $e){
				array_push($msg, "Erro ao gerar arquivo de XML de Cancelamento!!");
			}

		}

		$business = Business::find($business_id);

		$business_locations = BusinessLocation::forDropdown($business_id, false, true);

		$bl_attributes = $business_locations['attributes'];

		$business_locations = $business_locations['locations'];
		// array_push($business_locations, "");


		$default_location = null;
		if (count($business_locations) == 1) {
			foreach ($business_locations as $id => $name) {
				$default_location = BusinessLocation::findOrFail($id);
			}
		}

		return view('cte.lista')
		->with(compact('canceladas', 'aprovadas', 'business', 'data_inicio', 'data_final', 'msg'))
		->with('bl_attributes' , $bl_attributes)
		->with('default_location' , $default_location)
		->with('select_location_id' , $select_location_id)
		->with('business_locations' , $business_locations);
	}

	public function baixarZipXmlAprovado(){
		$business_id = request()->session()->get('user.business_id');
		$business = Business::find($business_id);
		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		if(file_exists(public_path('xml_cte/'.$cnpj.'/'.'xml.zip'))){
			return response()->download(public_path('xml_cte/'.$cnpj.'/'.'xml.zip'));
		}else{
			return redirect()->back()
			->with('status', [
				'success' => 0,
				'msg' => 'Arquivo não encontrado!!'
			]);
		}
	}

	public function baixarZipXmlReprovado(){
		$business_id = request()->session()->get('user.business_id');
		$business = Business::find($business_id);
		$cnpj = str_replace(".", "", $business->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);
		if(file_exists(public_path('xml_cte_cancelada/'.$cnpj.'/'.'xml_cancelado.zip'))){
			return response()->download(public_path('xml_cte_cancelada/'.$cnpj.'/'.'xml_cancelado.zip'));
		}else{
			return redirect()->back()
			->with('status', [
				'success' => 0,
				'msg' => 'Arquivo não encontrado!!'
			]);
		}
	}

	public function consultar(Request $request){

		$business_id = request()->session()->get('user.business_id');
		$cte = Cte::where('business_id', $business_id)
		->where('id', $request->id)
		->first();

		// $config = Business::find($business_id);
		$config = Business::getConfigCte($business_id, $cte);

		$cnpj = str_replace(".", "", $config->cnpj);
		$cnpj = str_replace("/", "", $cnpj);
		$cnpj = str_replace("-", "", $cnpj);
		$cnpj = str_replace(" ", "", $cnpj);


		$cte_service = new CTeService([
			"atualizacao" => date('Y-m-d h:i:s'),
			"tpAmb" => (int)$config->ambiente,
			"razaosocial" => $config->razao_social,
			"siglaUF" => $config->cidade->uf,
			"cnpj" => $cnpj,
			"schemes" => "PL_CTe_300",
			"versao" => '3.00',
			"proxyConf" => [
				"proxyIp" => "",
				"proxyPort" => "",
				"proxyUser" => "",
				"proxyPass" => ""
			]
		], $config);


		try{
			$res = $cte_service->consultar($cte);
			return response()->json($res, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);

		}
	}

	public function importarXml(Request $request){

		if ($request->hasFile('file')){
			$arquivo = $request->hasFile('file');
			$xml = simplexml_load_file($request->file);

			$business_id = request()->session()->get('user.business_id');

			$cidade = City::getCidadeCod($xml->NFe->infNFe->emit->enderEmit->cMun);
			$dadosEmitente = [
				'type' => 'customer',
				'supplier_business_name' => '',
				'cpf_cnpj' => $xml->NFe->infNFe->emit->CPF ?? $xml->NFe->infNFe->emit->CNPJ,
				'ie_rg' => $xml->NFe->infNFe->emit->IE,
				'contribuinte' => 1,
				'consumidor_final' => 1,
				'rua' => $xml->NFe->infNFe->emit->enderEmit->xLgr,
				'numero' => $xml->NFe->infNFe->emit->enderEmit->nro,
				'bairro' => $xml->NFe->infNFe->emit->enderEmit->xBairro,
				'cep' => $xml->NFe->infNFe->emit->enderEmit->fone,
				'name' => $xml->NFe->infNFe->emit->xNome,
				'tax_number' => '',
				'pay_term_number' => '',
				'pay_term_type' => '',
				'mobile' => '',
				'landline' => '',
				'alternate_number' => '',
				'city' => '',
				'state' => '',
				'country' =>  '',
				'landmark' => '',
				'customer_group_id' => '',
				'custom_field1' => '',
				'custom_field2' => '',
				'custom_field3' => '',
				'custom_field4' => '',
				'email' => '',
				'city_id' => $cidade->id,
				'cod_pais' => '1058',
				'id_estrangeiro' => '',
				'business_id' => $business_id,
				'created_by' => $request->session()->get('user.id')
			];

			$emitente = $this->verificaClienteCadastrado($dadosEmitente);

			$cidade = City::getCidadeCod($xml->NFe->infNFe->dest->enderDest->cMun);
			$dadosDestinatario = [
				'type' => 'customer',
				'supplier_business_name' => '',
				'cpf_cnpj' => $xml->NFe->infNFe->dest->CPF ?? $xml->NFe->infNFe->dest->CNPJ,
				'ie_rg' => $xml->NFe->infNFe->dest->IE,
				'contribuinte' => 1,
				'consumidor_final' => 1,
				'rua' => $xml->NFe->infNFe->dest->enderEmit->xLgr,
				'numero' => $xml->NFe->infNFe->dest->enderEmit->nro,
				'bairro' => $xml->NFe->infNFe->dest->enderEmit->xBairro,
				'cep' => $xml->NFe->infNFe->dest->enderEmit->fone,
				'name' => $xml->NFe->infNFe->dest->xNome,
				'tax_number' => '',
				'pay_term_number' => '',
				'pay_term_type' => '',
				'mobile' => '',
				'landline' => '',
				'alternate_number' => '',
				'city' => '',
				'state' => '',
				'country' =>  '',
				'landmark' => '',
				'customer_group_id' => '',
				'custom_field1' => '',
				'custom_field2' => '',
				'custom_field3' => '',
				'custom_field4' => '',
				'email' => '',
				'city_id' => $cidade->id,
				'cod_pais' => '1058',
				'id_estrangeiro' => '',
				'business_id' => $business_id,
				'created_by' => $request->session()->get('user.id')
			];

			$destinatario = $this->verificaClienteCadastrado($dadosDestinatario);

			$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);

			$somaQuantidade = 0;
			foreach($xml->NFe->infNFe->det as $item) {
				$somaQuantidade += $item->prod->qCom;
			}

			$unidade = $xml->NFe->infNFe->det[0]->prod->uCom;
			if($unidade == 'M2'){
				$unidade = '04';
			}else if($unidade == 'M3'){
				$unidade = '00';
			}else if($unidade == 'KG'){
				$unidade = '01';
			}else if($unidade == 'UNID'){
				$unidade = '03';
			}else if($unidade == 'TON'){
				$unidade = '02';
			}

			$dadosDaNFe = [
				'remetente' => $emitente->id,
				'destinatario' => $destinatario->id,
				'chave' => $chave,
				'produto_predominante' => $xml->NFe->infNFe->det[0]->prod->xProd,
				'unidade' => $unidade,
				'valor_carga' => $xml->NFe->infNFe->total->ICMSTot->vProd,
				'munipio_envio' => $emitente->cidade->id . " - " . $emitente->cidade->nome . "(" .$emitente->cidade->uf . ")",
				'munipio_final' => $destinatario->cidade->id . " - " . $destinatario->cidade->nome . "(" .$destinatario->cidade->uf . ")",
				'componente' => 'Transporte',
				'valor_frete' => $xml->NFe->infNFe->total->ICMSTot->vFrete,
				'quantidade' => number_format($somaQuantidade, 4),
				'data_entrega' => date('d/m/Y')
			];

			// echo "<pre>";
			// print_r($dadosDaNFe);
			// echo "</pre>";

			$roles  = $this->getRolesArray($business_id);
			$username_ext = $this->getUsernameExtension();

			$lastCte = Cte::lastCTeAux($business_id);
			$unidadesMedida = Cte::unidadesMedida();
			$tiposMedida = Cte::tiposMedida();
			$tiposTomador = Cte::tiposTomador();
			$naturezas = $this->prepareNaturezas();
			$modals = Cte::modals();
			$veiculos = $this->prepareVeiculos();
			$clientesAux = Contact::where('business_id', $business_id)->get();

			foreach($clientesAux as $c){
				$c->cidade;
			}

			$clientes =  $this->prepareClientes();
			$cidades =  $this->prepareCities();

			$business_locations = BusinessLocation::forDropdown($business_id, false, true);
			$bl_attributes = $business_locations['attributes'];
			$business_locations = $business_locations['locations'];

			$default_location = null;
			if (count($business_locations) == 1) {
				foreach ($business_locations as $id => $name) {
					$default_location = BusinessLocation::findOrFail($id);
				}
			}

			$chave = substr($xml->NFe->infNFe->attributes()->Id, 3, 44);

			$unidade = $xml->NFe->infNFe->det[0]->prod->uCom;
			if($unidade == 'M2'){
				$unidade = '04';
			}else if($unidade == 'M3'){
				$unidade = '00';
			}else if($unidade == 'KG'){
				$unidade = '01';
			}else if($unidade == 'UNID'){
				$unidade = '03';
			}else if($unidade == 'TON'){
				$unidade = '02';
			}
			
			$dadosDaNFe = [
				'remetente' => $emitente->id,
				'destinatario' => $destinatario->id,
				'chave' => $chave,
				'produto_predominante' => $xml->NFe->infNFe->det[0]->prod->xProd,
				'unidade' => $unidade,
				'valor_carga' => $xml->NFe->infNFe->total->ICMSTot->vProd,
				'munipio_envio' => $emitente->cidade->id,
				'munipio_final' => $destinatario->cidade->id,
				'componente' => 'Transporte',
				'valor_frete' => $xml->NFe->infNFe->total->ICMSTot->vFrete,
				'quantidade' => number_format($somaQuantidade, 4),
				'data_entrega' => date('d/m/Y')
			];

			return view('cte.register_xml')
			->with(compact('roles', 'username_ext', 'cidades', 'clientesAux', 'clientes', 'lastCte', 'unidadesMedida', 'tiposMedida', 'tiposTomador', 'naturezas', 'modals', 'veiculos', 'dadosDaNFe'))
			->with('bl_attributes' , $bl_attributes)
			->with('default_location' , $default_location)
			->with('business_locations' , $business_locations);

		}

	}

	private function verificaClienteCadastrado($cliente){

		if($cliente['cpf_cnpj'] != ''){
			$cli = Contact::
			where('business_id', $cliente['business_id'])
			->where('cpf_cnpj', $cliente['cpf_cnpj'])->first();
		}else{
			$cli = Contact::
			where('business_id', $cliente['business_id'])
			->where('cpf_cnpj', $cliente['cpf_cnpj'])->first();
		}
		if($cli == null){
			$cliente = Contact::create($cliente);
			return $cliente;
		}
		return $cli;
	}

}
