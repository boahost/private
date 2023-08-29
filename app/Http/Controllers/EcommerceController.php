<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigEcommerce;
use App\Utils\ModuleUtil;

class EcommerceController extends Controller
{

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

    public function config(){
    	$business_id = request()->session()->get('user.business_id');
    	$config = ConfigEcommerce::
    	where('business_id', $business_id)
    	->first();

    	return view('ecommerce/config')
    	->with('config', $config);
    }	

    public function save(Request $request){
    	$this->_validate($request);

    	try{
    		$business_id = $request->session()->get('user.business_id');

    		$config = ConfigEcommerce::
    		where('business_id', $business_id)
    		->first();

    		$request->merge(['link_facebook' => $request->link_facebook ?? '']);
    		$request->merge(['link_twiter' => $request->link_twiter ?? '']);
    		$request->merge(['link_instagram' => $request->link_instagram ?? '']);
    		$request->merge(['politica_privacidade' => $request->politica_privacidade ?? '']);
    		$request->merge(['frete_gratis_valor' => 
    			str_replace(",", ".", $request->frete_gratis_valor)]);
            $request->merge(['business_id' => $business_id]);
    		$request->merge(['business_id' => $business_id]);


    		$logo = $this->moduleUtil->uploadFile($request, 'logo', 'ecommerce_logos', 'image');

    		if($logo){
    			$request->merge(['logo' => $logo]);
    		}

    		$img_contato = $this->moduleUtil->uploadFile($request, 'img_contato', 'ecommerce_contatos', 'image');

    		if($img_contato){
    			$request->merge(['img_contato' => $img_contato]);
    		}

            $fav_icon = $this->moduleUtil->uploadFile($request, 'fav_icon', 'ecommerce_fav', 'image');

            if($fav_icon){
                $request->merge(['fav_icon' => $fav_icon]);
            }

    		if($config == null){

    			ConfigEcommerce::create($request->all());
    		}else{
    			$config->nome = $request->nome;
    			$config->email = $request->email;
    			$config->telefone = $request->telefone;
    			$config->rua = $request->rua;
    			$config->numero = $request->numero;
    			$config->bairro = $request->bairro;
    			$config->cidade = $request->cidade;
    			$config->cep = $request->cep;
    			$config->latitude = $request->latitude;
    			$config->longitude = $request->longitude;
    			$config->frete_gratis_valor = $request->frete_gratis_valor;
    			$config->link_facebook = $request->link_facebook;
    			$config->link_twiter = $request->link_twiter;
    			$config->link_instagram = $request->link_instagram;
    			$config->mercadopago_public_key = $request->mercadopago_public_key;
    			$config->mercadopago_access_token = $request->mercadopago_access_token;
    			$config->funcionamento = $request->funcionamento;
    			$config->politica_privacidade = $request->politica_privacidade;
    			$config->token = $request->token;
    			$config->cor_fundo = $request->cor_fundo;
                $config->cor_btn = $request->cor_btn;
    			$config->timer_carrossel = $request->timer_carrossel ?? 5;
                $config->mensagem_agradecimento = $request->mensagem_agradecimento;
                
    			if($logo){
    				if($config->logo != "" && file_exists(public_path('uploads/ecommerce_logos/').$config->logo)){
    					unlink(public_path('uploads/ecommerce_logos/').$config->logo);
    				}
    				$config->logo = $logo;
    			}

                if($fav_icon){
                    if($config->logo != "" && file_exists(public_path('uploads/ecommerce_fav/').$config->logo)){
                        unlink(public_path('uploads/ecommerce_fav/').$config->logo);
                    }
                    $config->fav_icon = $fav_icon;
                }

    			if($img_contato){

    				if($config->img_contato != "" && file_exists(public_path('uploads/ecommerce_contatos/').$config->img_contato)){

    					unlink(public_path('uploads/ecommerce_contatos/').$config->img_contato);
    				}
    				$config->img_contato = $img_contato;

    			}

    			$config->save();

    		}
    		$output = [
    			'success' => 1,
    			'msg' => 'Configurado com Sucesso!!'
    		];

    	}catch (\Exception $e) {

    		\Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

			echo $e->getMessage();
			die;

    		$output = [
    			'success' => 0,
    			'msg' => __("messages.something_went_wrong")
    		];

    	}
    	return redirect('/ecommerce/config')->with('status', $output);


    }

    private function _validate(Request $request){
    	$rules = [
    		'nome' => 'required|max:30',

    		'rua' => 'required|max:80',
    		'numero' => 'required|max:10',
    		'bairro' => 'required|max:30',
    		'cidade' => 'required|max:30',
    		'cep' => 'required|max:10',
    		'telefone' => 'required|max:15',
    		'email' => 'required|max:60',
    		'mercadopago_public_key' => 'required|max:120',
    		'mercadopago_access_token' => 'required|max:120',
    		'funcionamento' => 'required|max:120',
    		'link_facebook' => 'max:120',
    		'link_twiter' => 'max:120',
    		'link_instagram' => 'max:120',
    		'latitude' => 'required|max:10',
    		'longitude' => 'required|max:10',
    		'token' => 'required',
            'timer_carrossel' => 'required',
    		'politica_privacidade' => 'max:400',
    		'frete_gratis_valor' => 'required',
            'mensagem_agradecimento' => 'required'
    	];

    	$messages = [
    		'link_facebook.max' => '120 caracteres maximos permitidos.',
    		'link_twiter.max' => '120 caracteres maximos permitidos.',
    		'link_instagram.max' => '120 caracteres maximos permitidos.',

    		'nome.required' => 'O campo nome é obrigatório.',
            'nome.mensagem_agradecimento' => 'Campo obrigatório.',
    		'nome.max' => '80 caracteres maximos permitidos.',

    		'telefone.required' => 'O campo Telefone é obrigatório.',
    		'telefone.max' => '35 caracteres maximos permitidos.',
    		'rua.required' => 'O campo rua é obrigatório.',
    		'rua.max' => '80 caracteres maximos permitidos.',
    		'numero.required' => 'O campo número é obrigatório.',
    		'numero.max' => '10 caracteres maximos permitidos.',
    		'bairro.required' => 'O campo bairro é obrigatório.',
    		'bairro.max' => '30 caracteres maximos permitidos.',
    		'cidade.required' => 'O campo cidade é obrigatório.',
    		'cidade.max' => '30 caracteres maximos permitidos.',
    		'cep.required' => 'O campo cep é obrigatório.',
    		'cep.max' => '10 caracteres maximos permitidos.',
    		'email.required' => 'O campo email é obrigatório.',
    		'email.max' => '120 caracteres maximos permitidos.',

    		'mercadopago_public_key.max' => '120 caracteres maximos permitidos.',
    		'mercadopago_public_key.required' => 'Campo obrigatório.',

    		'mercadopago_access_token.max' => '120 caracteres maximos permitidos.',
    		'mercadopago_access_token.required' => 'Campo obrigatório.',

    		'politica_privacidade.max' => '400 caracteres maximos permitidos.',
    		'funcionamento.required' => 'O campo funcionamento é obrigatório.',
    		'funcionamento.max' => '60 caracteres maximos permitidos.',

    		'latitude.required' => 'O campo Latitude é obrigatório.',
    		'latitude.max' => '10 caracteres maximos permitidos.',
    		'longitude.required' => 'O campo Longitude é obrigatório.',
    		'longitude.max' => '10 caracteres maximos permitidos.',
    		'link.unique' => 'Já existe um cadastro com este link.',
    		'token.required' => 'O campo token é obrigatório.',
    		'frete_gratis_valor.required' => 'Campo obrigatório.',

            'timer_carrossel.required' => 'Campo obrigatório.',


    	];
    	$this->validate($request, $rules, $messages);
    }
}
