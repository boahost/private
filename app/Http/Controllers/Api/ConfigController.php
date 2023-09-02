<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ConfigEcommerce;
use App\Models\InformativoEcommerce;
use App\Models\ContatoEcommerce;

class ConfigController extends Controller
{
	public function index(Request $request){

		try{
			$config = ConfigEcommerce::
			where('business_id', $request->business_id)
			->first();

			return response()->json($config, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}

	public function salvarEmail(Request $request){
		try{
			$email = $request->email;
			$businessId = $request->business_id;


			$info = InformativoEcommerce::
			where('email', $email)
			->where('business_id', $businessId)
			->first();

			if($info != null){
				return response()->json("Email já registrado!!", 404);
			}

			$i = InformativoEcommerce::create([
				'business_id' => $businessId,
				'email' => $email
			]);

			return response()->json($i, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

	public function salvarContato(Request $request){
		try{
			$data = $request->data;

			$businessId = $request->business_id;

			$contato = [
				'nome' => $data['nome'],
				'email' => $data['email'],
				'texto' => $data['mensagem'],
				'business_id' => $businessId
			];

			$result = ContatoEcommerce::create($contato);
			return response()->json($result, 200);
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}
	}

}
