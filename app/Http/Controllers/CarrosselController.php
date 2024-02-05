<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarrosselEcommerce;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;

class CarrosselController extends Controller
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
			$naturezas = CarrosselEcommerce::where('business_id', $business_id)
			->select(['id', 'titulo', 'link_acao', 'nome_botao', 'img', 'cor_fundo']);


			return Datatables::of($naturezas)

			->addColumn(
				'action',
				'<a href="/carrosselEcommerce/edit/{{$id}}" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
				&nbsp;<button data-href="/carrosselEcommerce/delete/{{$id}}" class="btn btn-xs btn-danger delete_user_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>'
			)

			->editColumn('img', function ($row) {

				return '<div style="display: flex;"><img src="/uploads/img/carrossel/' . $row->img . '" alt="imagem" class="product-thumbnail-small"></div>';

			})

			->editColumn('cor_fundo', function ($row) {

				return '<div style="display: flex; padding: 10px; background-color: '.$row->cor_fundo.'"></div>';

			})
			->removeColumn('id')
			->rawColumns(['action', 'img', 'cor_fundo'])
			->make(true);

		}
		return view('carrossel.list');

	}

	public function create(){
		return view('carrossel.create');

	}

	public function store(Request $request){

		if (!auth()->user()->can('category.create')) {
			abort(403, 'Unauthorized action.');
		}
		$this->_validate($request);


		try {
            $request->merge(['titulo' => $request->titulo ?? '']);
            $request->merge(['descricao' => $request->descricao ?? '']);
            $request->merge(['nome_botao' => $request->nome_botao ?? '']);
            $request->merge(['link_acao' => $request->link_acao ?? '']);
            $input = $request->only(['titulo', 'descricao', 'nome_botao', 'link_acao']);

            $input['img'] = $this->moduleUtil->uploadFile($request, 'image', 'img/carrossel', 'image');

            $input['business_id'] = $request->session()->get('user.business_id');

            $carrossel = CarrosselEcommerce::create($input);
            $output = [
                'success' => true,
                'msg' => 'Registro adicionado!!'
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => $e->getMessage()
            ];
        }

        // return $output;
        return redirect('/carrosselEcommerce')->with('status', $output);
	}

	public function edit($id){
		// return view('naturezas.register');

		if (!auth()->user()->can('user.create')) {
			abort(403, 'Unauthorized action.');
		}

		$business_id = request()->session()->get('user.business_id');


		$carrossel = CarrosselEcommerce::find($id);

       

		return view('carrossel.edit')
		->with('carrossel', $carrossel);
	}

	public function update(Request $request, $id)
    {
        if (!auth()->user()->can('category.update')) {
            abort(403, 'Unauthorized action.');
        }

        // if (request()->ajax()) {
        try {
            $input = $request->only(['titulo', 'descricao', 'nome_botao', 'link_acao', 'cor_fundo']);
            $business_id = $request->session()->get('user.business_id');

            $input['img'] = $this->moduleUtil->uploadFile($request, 'image', 'img/carrossel', 'image');

            $carrossel = CarrosselEcommerce::where('business_id', $business_id)->findOrFail($id);


            if($input['img']){
                if($carrossel->image != null){
                    if(file_exists(public_path('uploads/img/carrossel/').$carrossel->img)){
                        unlink(public_path('uploads/img/carrossel/').$carrossel->img);
                    }
                }
            }

            // $input['image'] = $this->moduleUtil->uploadFile($request, 'image', 'img/categorias', 'image');

            $carrossel->titulo = $input['titulo'];
            $carrossel->descricao = $input['descricao'];
            $carrossel->nome_botao = $input['nome_botao'];
            $carrossel->link_acao = $input['link_acao'];
            $carrossel->cor_fundo = $input['cor_fundo'];
            if($input['img']){
                $carrossel->img = $input['img'];
            }

            
            $carrossel->save();

            $output = [
                'success' => true,
                'msg' => "Sucesso!!"
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
        return redirect('/carrosselEcommerce')->with('status', $output);

        // }
    }

     public function delete($id)
    {
        if (!auth()->user()->can('category.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $carrossel = CarrosselEcommerce::where('business_id', $business_id)->findOrFail($id);

                if($carrossel->img){
                    if(file_exists(public_path('uploads/img/carrossel/').$carrossel->img)){
                        unlink(public_path('uploads/img/carrossel/').$carrossel->img);
                    }
                }

                $carrossel->delete();

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

	private function _validate(Request $request){
		$rules = [
			// 'titulo' => 'required|max:30',
			// 'descricao' => 'required|max:200',
			// 'link_acao' => 'required|max:200',
			// 'nome_botao' => 'required|max:20',
			'image' => 'required'
		];

		$messages = [
			'titulo.required' => 'O campo nome é obrigatório.',
			'titulo.max' => '30 caracteres maximos permitidos.',
			'descricao.required' => 'O campo descrição é obrigatório.',
			'descricao.max' => '200 caracteres maximos permitidos.',
			'link_acao.required' => 'O campo link é obrigatório.',
			'link_acao.max' => '200 caracteres maximos permitidos.',
			'nome_botao.required' => 'O campo nome botão é obrigatório.',
			'nome_botao.max' => '200 caracteres maximos permitidos.',
			'image.required' => 'O campo imagem é obrigatório.',
		];
		$this->validate($request, $rules, $messages);
	}
}
