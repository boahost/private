<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\CarrosselEcommerce;
use App\Models\Category;
use App\Models\CurtidaProdutoEcommerce;
use App\Models\ClienteEcommerce;

class ProdutoController extends Controller
{
	public function categoria($id){
		$categoria = Category::
		where('business_id', request()->business_id)
		->where('id', $id)
		->first();

		return response()->json($categoria, 200);
	}

	public function destaques(Request $request){
		$produtos = Product::
		where('business_id', $request->business_id)
		->where('destaque', true)
		->where('ecommerce', true)
		->get();

		return response()->json($produtos, 200);
	}

	public function novosProdutos(Request $request){
		$produtos = Product::
		where('business_id', $request->business_id)
		->where('novo', true)
		->where('ecommerce', true)
		->get();

		return response()->json($produtos, 200);
	}

	public function maisVendidos(Request $request){
		
		$produtos = Product::
		selectRaw('products.*, sum(item_pedido_ecommerces.quantidade) as soma')
		->join('item_pedido_ecommerces', 'item_pedido_ecommerces.produto_id',
			'=', 'products.id')
		->join('pedido_ecommerces', 'item_pedido_ecommerces.pedido_id',
			'=', 'pedido_ecommerces.id')
		->where('products.business_id', $request->business_id)
		->where('products.ecommerce', true)
		->where('pedido_ecommerces.status', '!=', 0)
		->groupBy('products.id')
		->orderBy('soma')
		->limit(20)
		->get();

		return response()->json($produtos, 200);
	}

	public function pesquisa(Request $request){
		$produtos = Product::
		where('business_id', $request->business_id)
		->where('name', 'LIKE', "%$request->pesquisa%")
		->where('ecommerce', true)
		->get();

		return response()->json($produtos, 200);
	}

	public function categoriasEmDestaque(Request $request){
		$categorias = Category::
		where('business_id', $request->business_id)
		->where('destaque', 1)
		->where('ecommerce', 1)
		->get();

		return response()->json($categorias, 200);
	}

	public function categorias(Request $request){
		$categorias = Category::
		where('business_id', $request->business_id)
		->where('ecommerce', 1)
		->get();

		return response()->json($categorias, 200);
	}

	public function carrossel(Request $request){
		$carrossel = CarrosselEcommerce::
		where('business_id', $request->business_id)
		->get();

		return response()->json($carrossel, 200);
	}

	public function porCategoria(Request $request, $id){
		$produtos = Product::
		where('business_id', $request->business_id)
		->where('category_id', $id)
		->where('ecommerce', 1)
		->get();

		return response()->json($produtos, 200);
	}

	public function porId(Request $request){
		$produto = Product::
		where('business_id', $request->business_id)
		->where('id', $request->id)
		->where('ecommerce', 1)
		->first();

		$produto->category;

		foreach($produto->variations as $v){
			$v->media;
		}
		$temp = [];
		array_push($temp, $produto->image_url);
		foreach($produto->imagens as $i){
			array_push($temp, $i->image_url);
		}

		$cliente = ClienteEcommerce::
		where('token', $request->token)
		->first();

		$curtida = null;
		if($cliente){
			$curtida = CurtidaProdutoEcommerce::
			where('produto_id', $request->id)
			->where('cliente_id', $cliente->id)
			->first();
		}

		$produto->favorito = $curtida != null ? true : false;
		$produto->imagensAll = $temp;

		return response()->json($produto, 200);
	}

	public function favorito(Request $request){

		try{
			$cliente = ClienteEcommerce::
			where('token', $request->token)
			->first();

			$curtida = CurtidaProdutoEcommerce::
			where('produto_id', $request->produtoId)
			->where('cliente_id', $cliente->id)
			->first();

			if($curtida != null){
				$curtida->delete();
				return response()->json("delete", 200);
			}else{
				CurtidaProdutoEcommerce::create(
					[
						'produto_id' => $request->produtoId,
						'cliente_id' => $cliente->id 
					]
				);
				return response()->json("inserido", 201);

			}
		}catch(\Exception $e){
			return response()->json($e->getMessage(), 401);
		}

	}
}
