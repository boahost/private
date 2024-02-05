<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemUpdate;
class UpdateController extends Controller
{
    public function index(){
        if (!auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        $system = SystemUpdate::first();
        if($system == null){
            $system = SystemUpdate::create(['version' => '1.0']);
        }

        return view('update.index', compact('system'));
    }

    public function sql(Request $request){
        if($request->hasFile('file')){
            $file = $request->file('file');

            $text = file_get_contents($file);
            $lines = explode(";", $text);
            $logMessage = [];
            foreach($lines as $sql){
                if(trim($sql)){
                    try{
                        \DB::unprepared("$sql;");
                        array_push($logMessage, "Comando SQL executado <strong class='text-info'>$sql;</strong>");

                    }catch(\Exception $e){
                        array_push($logMessage, "Erro ao executar SQL: " . $e->getMessage() . " - <strong class='text-success'>ISSO NÃO AFETA A ATUALIZAÇÃO</strong>");
                    }
                }
            }

            return view('update/finish', compact('logMessage'));

        }else{
            session()->flash('mensagem_erro', "Arquivo não foi selecionado!!");
            return redirect()->back();
        }
    }
}
