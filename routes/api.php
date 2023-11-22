<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware(['authEcommerce'])->group(function () {

    Route::group(['prefix' => '/produtos'], function () {
        Route::get('/categoria/{id}', 'Api\\ProdutoController@categoria');
        Route::get('/destaques', 'Api\\ProdutoController@destaques');
        Route::get('/maisVendidos', 'Api\\ProdutoController@maisVendidos');
        Route::get('/novosProdutos', 'Api\\ProdutoController@novosProdutos');
        Route::get('/categoriasEmDestaque', 'Api\\ProdutoController@categoriasEmDestaque');
        Route::get('/categorias', 'Api\\ProdutoController@categorias');
        Route::get('/carrossel', 'Api\\ProdutoController@carrossel');
        Route::get('/porCategoria/{id}', 'Api\\ProdutoController@porCategoria');
        Route::get('/porId', 'Api\\ProdutoController@porId');
        Route::get('/pesquisa', 'Api\\ProdutoController@pesquisa');
        Route::post('/favorito', 'Api\\ProdutoController@favorito');
    });

    Route::group(['prefix' => '/pix/efi'], function () {
        Route::post('/', 'Api\\PixEfiController@store');
        Route::post('/webhook', 'Api\\PixEfiController@atualizar');
    });

    Route::group(['prefix' => '/config'], function () {
        Route::get('/', 'Api\\ConfigController@index');
        Route::post('/salvarEmail', 'Api\\ConfigController@salvarEmail');
        Route::post('/salvarContato', 'Api\\ConfigController@salvarContato');
    });

    Route::group(['prefix' => '/carrinho'], function () {
        Route::get('/itens', 'Api\\CarrinhoController@itens');
        Route::post('/salvarPedido', 'Api\\CarrinhoController@salvarPedido');
        Route::get('/getPedido', 'Api\\CarrinhoController@getPedido');
        Route::post('/processarPagamentoCartao', 'Api\\CarrinhoController@processarPagamentoCartao');
        Route::post('/processarPagamentoBoleto', 'Api\\CarrinhoController@processarPagamentoBoleto');
        Route::post('/processarPagamentoPix', 'Api\\CarrinhoController@processarPagamentoPix');

        Route::get('/getStatusPix', 'Api\\CarrinhoController@getStatusPix');
        Route::get('/calcularFrete', 'Api\\CarrinhoController@calcularFrete');
        Route::post('/calculaDesconto', 'Api\\CarrinhoController@calculaDesconto');

    });

    Route::group(['prefix' => '/clientes'], function () {
        Route::post('/salvar', 'Api\\ClienteController@salvar');
        Route::post('/atualizar', 'Api\\ClienteController@atualizar');
        Route::post('/cadastroDuplicado', 'Api\\ClienteController@cadastroDuplicado');
        Route::get('/findWithCart', 'Api\\ClienteController@findWithCart');
        Route::get('/findWithData', 'Api\\ClienteController@findWithData');
        Route::post('/alterarSenha', 'Api\\ClienteController@alterarSenha');
        Route::post('/login', 'Api\\ClienteController@login');
        Route::post('/esqueciMinhaSenha', 'Api\\ClienteController@esqueciMinhaSenha');
    });

    Route::group(['prefix' => '/enderecos'], function () {
        Route::post('/salvar', 'Api\\EnderecoController@salvar');
        Route::post('/atualizar', 'Api\\EnderecoController@atualizar');
    });

});

Route::get('/bank/{id}', 'BankController@find');
Route::get('/consultaPix/{transacao_id}', 'PaymentController@consultaPix');
Route::get('/consultaValorPlano/{plano_id}', 'PaymentController@consultaValorPlano');

Route::get('/test', 'BankController@test');
