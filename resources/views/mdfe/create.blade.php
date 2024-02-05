@extends('layouts.app')
@if($mdfe != null)
@section('title', 'Editar MDFe')
@else
@section('title', 'Adicionar MDFe')
@endif

@section('content')
<style type="text/css">
  .fa-trash:hover{
    cursor: pointer;
  }
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
  @if($mdfe != null)
  <h1>Editar </h1>
  @else
  <h1>Adicionar </h1>

  @endif
</section>

<!-- Main content -->
<section class="content">
  @if($mdfe != null)
  {!! Form::open(['url' => action('MdfeController@update', [$mdfe->id]), 'method' => 'put', 'id' => 'mdfe_add_form' ]) !!}
  @else
  {!! Form::open(['url' => action('MdfeController@store'), 'method' => 'post', 'id' => 'mdfe_add_form' ]) !!}
  @endif
  <div class="row">
    <div class="col-md-12">
      @component('components.widget')


      <div class="col-md-2">
        <div class="form-group">
          <h4>Ultima MDFe: <strong>{{$lastMdfe}}</strong></h4>
        </div>
      </div>

      <input type="hidden" id="clientesAux" value="{{json_encode($clientesAux)}}" name="">

      <div class="clearfix"></div>

      @if(is_null($default_location))

      <div class="col-md-4">
        <br>
        <div class="form-group" style="margin-top: 8px;">
          <div class="input-group">
            <span class="input-group-addon">
              <i class="fa fa-map-marker"></i>
            </span>
            {!! Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm', 
            'placeholder' => __('lang_v1.select_location'),
            'id' => 'select_location_id', 
            'required', 'autofocus'], $bl_attributes); !!}
            <span class="input-group-addon">
              @show_tooltip('Local da MDFe')
            </span> 
          </div>
        </div>

      </div>
      @endif

      <div class="clearfix"></div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('uf_inicio', 'UF início' . ':*') !!}
          {!! Form::select('uf_inicio', $ufs, $mdfe != null ? $mdfe->uf_inicio : '', ['class' => 'form-control select2', 'id' => 'uf_inicio', 'required', 'placeholder' => 'Selecione a UF']); !!}
        </div>
      </div>
      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('uf_fim', 'UF fim' . ':*') !!}
          {!! Form::select('uf_fim', $ufs, $mdfe != null ? $mdfe->uf_fim : '', ['class' => 'form-control select2', 'id' => 'uf_fim', 'required', 'placeholder' => 'Selecione a UF']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('data_inicio_viagem', 'Data início da viagem' . ':*') !!}
          <div class="input-group">
            <span class="input-group-addon">
              <i class="fa fa-calendar"></i>
            </span>

            {!! Form::text('data_inicio_viagem', $mdfe != null ? \Carbon\Carbon::parse($mdfe->data_inicio_viagem)->format('d/m/Y') : '', ['class' => 'form-control', 'readonly', 'required', 'id' => 'data_inicio_viagem']); !!}
          </div>
        </div>
      </div>


      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('carga_posterior', 'Carga posterior' . ':*') !!}
          {!! Form::select('carga_posterior', [0 => 'Não', 1 => 'Sim'], $mdfe != null ? $mdfe->carga_posterior : '', ['class' => 'form-control select2', 'id' => 'carga_posterior', 'required']); !!}
        </div>
      </div>


      <div class="clearfix"></div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('tipo_emitente', 'Tipo do emitente' . ':*') !!}
          {!! Form::select('tipo_emitente', [1 => '1 - Prestador de serviço de transporte', 2 => '2 - Transportador de Carga Própria'], $mdfe != null ? $mdfe->tp_emit : '', ['class' => 'form-control select2', 'id' => 'tipo_emitente', 'required']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('tipo_transportador', 'Tipo do transportador' . ':*') !!}
          {!! Form::select('tipo_transportador', [1 => '1 - ETC', 2 => '2 - TAC', 3 => '3 - CTC'], $mdfe != null ? $mdfe->tp_transp : '', ['class' => 'form-control select2', 'id' => 'tipo_transportador', 'required']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('lac_rodo', 'Lacre rodoviário' . ':*') !!}
          {!! Form::text('lac_rodo', $mdfe != null ? $mdfe->lac_rodo : '', ['class' => 'form-control type-ref', 'required', 'placeholder' => 'Lacre rodoviário' ]); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cnpj_contratante', 'CPF/CNPJ contratante' . ':*') !!}
          {!! Form::text('cnpj_contratante', $mdfe != null ? $mdfe->cnpj_contratante : '', ['class' => 'form-control type-ref cpf_cnpj', 'required', 'placeholder' => 'CPF/CNPJ contratante' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('quantidade_carga', 'Quantidade da carga' . ':*') !!}
          {!! Form::text('quantidade_carga', $mdfe != null ? $mdfe->quantidade_carga : '', ['class' => 'form-control type-ref', 'required', 'placeholder' => 'Quantidade da carga', 'data-mask="00000000,0000", data-mask-reverse="true"' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('valor_carga', 'Valor da carga' . ':*') !!}
          {!! Form::text('valor_carga', $mdfe != null ? $mdfe->valor_carga : '', ['class' => 'form-control type-ref', 'required', 'placeholder' => 'Valor da carga', 'data-mask="0000000000,00", data-mask-reverse="true"' ]); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('veiculo_tracao_id', 'Veiculo de tração' . ':*') !!}
          {!! Form::select('veiculo_tracao_id', $veiculos, $mdfe != null ? $mdfe->veiculo_tracao_id : '', ['class' => 'form-control select2', 'id' => 'veiculo_tracao_id', 'required']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('veiculo_reboque1_id', 'Veiculo de reboque 1 (opcional)' . ':') !!}
          {!! Form::select('veiculo_reboque1_id', $veiculos, $mdfe != null ? $mdfe->veiculo_reboque1_id : '', ['class' => 'form-control select2', 'id' => 'veiculo_reboque1_id']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('veiculo_reboque2_id', 'Veiculo de reboque 2 (opcional)' . ':') !!}
          {!! Form::select('veiculo_reboque2_id', $veiculos, $mdfe != null ? $mdfe->veiculo_reboque2_id : '', ['class' => 'form-control select2', 'id' => 'veiculo_reboque2_id']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('veiculo_reboque3_id', 'Veiculo de reboque 3 (opcional)' . ':') !!}
          {!! Form::select('veiculo_reboque3_id', $veiculos, $mdfe != null ? $mdfe->veiculo_reboque3_id : '', ['class' => 'form-control select2', 'id' => 'veiculo_reboque3_id']); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('produto_pred_nome', 'Produto predominante' . ':') !!}
          {!! Form::text('produto_pred_nome', $mdfe != null ? $mdfe->produto_pred_nome : '', ['class' => 'form-control type-ref', 'placeholder' => 'Produto predominante' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('produto_pred_cod_barras', 'Código de barras' . ':') !!}
          {!! Form::text('produto_pred_cod_barras', $mdfe != null ? $mdfe->produto_pred_cod_barras : '', ['class' => 'form-control type-ref', 'placeholder' => 'Código de barras' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('produto_pred_ncm', 'NCM' . ':') !!}
          {!! Form::text('produto_pred_ncm', $mdfe != null ? $mdfe->produto_pred_ncm : '', ['class' => 'form-control type-ref', 'placeholder' => 'NCM', 'data-mask="0000.00.00", data-mask-reverse="true"' ]); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cep_carrega', 'CEP Carrega' . ':') !!}
          {!! Form::text('cep_carrega', $mdfe != null ? $mdfe->cep_carrega : '', ['class' => 'form-control type-ref', 'placeholder' => 'CEP Carrega', 'data-mask="00000-000", data-mask-reverse="true"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('latitude_carrega', 'Latitude Carrega' . ':') !!}
          {!! Form::text('latitude_carrega', $mdfe != null ? $mdfe->latitude_carregamento : '', ['class' => 'form-control type-ref', 'placeholder' => 'Latitude Carrega', 'data-mask="-00.000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('longitude_carrega', 'Longitude Carrega' . ':') !!}
          {!! Form::text('longitude_carrega', $mdfe != null ? $mdfe->longitude_carregamento : '', ['class' => 'form-control type-ref', 'placeholder' => 'Longitude Carrega', 'data-mask="-00.000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cep_descarrega', 'CEP Descarrega' . ':') !!}
          {!! Form::text('cep_descarrega', $mdfe != null ? $mdfe->cep_descarrega : '', ['class' => 'form-control type-ref', 'placeholder' => 'CEP Descarrega', 'data-mask="00000-000", data-mask-reverse="true"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('latitude_descarrega', 'Latitude Descarrega' . ':') !!}
          {!! Form::text('latitude_descarrega', $mdfe != null ? $mdfe->latitude_descarregamento : '', ['class' => 'form-control type-ref', 'placeholder' => 'Latitude Descarrega', 'data-mask="-00.000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('longitude_descarrega', 'Longitude Descarrega' . ':') !!}
          {!! Form::text('longitude_descarrega', $mdfe != null ? $mdfe->longitude_descarregamento : '', ['class' => 'form-control type-ref', 'placeholder' => 'Longitude Descarrega', 'data-mask="-00.000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('tp_carga', 'Tipo de carga' . ':') !!}
          {!! Form::select('tp_carga', App\Models\Mdfe::tiposCarga(), $mdfe != null ? $mdfe->tp_carga : '', ['class' => 'form-control select2', 'id' => 'tp_carga', 'required', 'style' => 'width: 100%']); !!}
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs nav-justified">
              <li class="active">
                <a href="#geral" data-toggle="tab" aria-expanded="true">INFORMAÇÕES GERAIS</a>
              </li>
              <li class="''">
                <a href="#transp" data-toggle="tab" aria-expanded="false">INFORMAÇÕES DE TRANSPORTE</a>
              </li>
              <li class="''">
                <a href="#desc" data-toggle="tab" aria-expanded="false">INFORMAÇÕES DE DESCARREGAMENTO</a>
              </li>

            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="geral">
                <div class="row">
                  <div class="col-md-12" style="border: 1px solid #e0e0e0; border-radius: 5px;">
                    <div class="col-md-12">
                      <h3>Seguradora (opcional)</h3>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('seguradora_nome', 'Nome da seguradora' . ':*') !!}
                        {!! Form::text('seguradora_nome', $mdfe != null ? $mdfe->seguradora_nome : '', ['class' => 'form-control type-ref', 'placeholder' => 'Nome da seguradora' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('seguradora_cnpj', 'CNPJ da seguradora' . ':*') !!}
                        {!! Form::text('seguradora_cnpj', $mdfe != null ? $mdfe->seguradora_cnpj : '', ['class' => 'form-control type-ref', 'placeholder' => 'CNPJ da seguradora', 'data-mask="00.000.000/0000-00", data-mask-reverse="true"' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('numero_apolice', 'Número de apolice' . ':*') !!}
                        {!! Form::text('numero_apolice', $mdfe != null ? $mdfe->numero_apolice : '', ['class' => 'form-control type-ref', 'placeholder' => 'Número de apolice' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('numero_averbacao', 'Número da averbação' . ':*') !!}
                        {!! Form::text('numero_averbacao', $mdfe != null ? $mdfe->numero_averbacao : '', ['class' => 'form-control type-ref', 'placeholder' => 'Número da averbação' ]); !!}
                      </div>
                    </div>
                  </div>

                  <div class="clearfix"></div>
                  <div class="col-md-8" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">

                    <div class="col-md-12">
                      <h3>Municipios de carregamento</h3>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('municipio', 'Selecione o municipio' . ':') !!}
                        {!! Form::select('municipio', $cidades, '', ['class' => 'form-control select2', 'id' => 'municipio', 'required']); !!}
                      </div>
                    </div>
                    <div class="col-md-4">
                      <button type="button" id="add-cidade" class="btn btn-info" style="margin-top: 23px;">
                        Adicionar
                      </button>
                    </div>
                    <div class="col-md-12">

                      <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="cidades_table">
                          <thead>
                            <tr>
                              <th>Municipio</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div> 

                  <div class="col-md-4" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">

                    <div class="col-md-12">
                      <h3>Percurso</h3>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('uf', 'Selecione a UF' . ':') !!}
                        {!! Form::select('uf', $ufs, '', ['class' => 'form-control select2', 'id' => 'uf', 'required']); !!}
                      </div>
                    </div>
                    <div class="col-md-4">
                      <button type="button" id="add-uf" class="btn btn-info" style="margin-top: 23px;">
                        Adicionar
                      </button>
                    </div>
                    <div class="col-md-12">

                      <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="ufs_table">
                          <thead>
                            <tr>
                              <th>UF</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div> 

                </div>                    
              </div>

              <div class="tab-pane ''" id="transp">
                <div class="row">

                  <div class="col-md-12" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">
                    <div class="col-md-12">
                      <h3>CIOT (opcional)</h3>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('codigo_ciot', 'Código CIOT' . ':*') !!}
                        {!! Form::text('codigo_ciot', null, ['class' => 'form-control type-ref', 'placeholder' => 'Código CIOT' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('doc_ciot', 'CNPJ/CPF' . ':*') !!}
                        {!! Form::text('doc_ciot', null, ['class' => 'form-control type-ref cpf_cnpj', 'placeholder' => 'CNPJ/CPF', ]); !!}
                      </div>
                    </div>
                    <div class="col-md-4">
                      <button type="button" id="add-ciot" class="btn btn-info" style="margin-top: 23px;">
                        Adicionar
                      </button>
                    </div>
                    <div class="col-md-12">

                      <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="ciot_table">
                          <thead>
                            <tr>
                              <th>Código</th>
                              <th>CPF/CNPJ</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div>

                  <!-- Vale pedagio -->

                  <div class="col-md-12" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">
                    <div class="col-md-12">
                      <h3>Vale Pedagio (opcional)</h3>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('vale_cnpj_fornecedor', 'CNPJ Fornecedor' . ':*') !!}
                        {!! Form::text('vale_cnpj_fornecedor', null, ['class' => 'form-control type-ref', 'placeholder' => 'CNPJ Fornecedor' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('vale_doc_pagador', 'CPF/CNPJ Pagador' . ':*') !!}
                        {!! Form::text('vale_doc_pagador', null, ['class' => 'form-control type-ref', 'placeholder' => 'CPF/CNPJ Pagador' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        {!! Form::label('vale_numero_compra', 'Nº da compra' . ':*') !!}
                        {!! Form::text('vale_numero_compra', null, ['class' => 'form-control type-ref', 'placeholder' => 'Nº da compra', ]); !!}
                      </div>
                    </div>

                    <div class="col-md-2">
                      <div class="form-group">
                        {!! Form::label('vale_valor', 'Valor' . ':*') !!}
                        {!! Form::text('vale_valor', null, ['class' => 'form-control type-ref', 'placeholder' => 'Valor', 'data-mask="00000,00", data-mask-reverse="true"' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-2">
                      <button type="button" id="add-vale" class="btn btn-info" style="margin-top: 23px;">
                        Adicionar
                      </button>
                    </div>
                    <div class="col-md-12">

                      <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="vale_table">
                          <thead>
                            <tr>
                              <th>CNPJ Fornecedor</th>
                              <th>CPF/CNPJ do Pagador</th>
                              <th>Número da compra</th>
                              <th>Valor</th>
                              <th>Ação</th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div>

                  <div class="col-md-12" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">
                    <div class="col-md-12">
                      <h3>Condutor</h3>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('condutor_nome', 'Condutor' . ':*') !!}
                        {!! Form::text('condutor_nome', $mdfe != null ? $mdfe->condutor_nome : '', ['class' => 'form-control type-ref', 'placeholder' => 'Condutor' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('condutor_cpf', 'CPF' . ':*') !!}
                        {!! Form::text('condutor_cpf', $mdfe != null ? $mdfe->condutor_cpf : '', ['class' => 'form-control type-ref', 'placeholder' => 'CPF', 'data-mask="000.000.000-00", data-mask-reverse="true"' ]); !!}
                      </div>
                    </div>
                  </div>
                </div>
              </div>


              <div class="tab-pane ''" id="desc">
                <div class="row">

                  <div class="col-md-12" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 10px;">
                    <div class="col-md-12">
                      <h3>Informações da Unidade de Transporte / Documentos Fiscais / Lacres</h3>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('tipo_unidade_transporte', 'Tipo Unidade de Transporte' . ':*') !!}
                        {!! Form::select('tipo_unidade_transporte', $tiposUnidadeTransporte, '', ['class' => 'form-control select2 full', 'id' => 'tipo_unidade_transporte', 'required', 'style' => 'width: 100%']); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('id_unidade_transporte', 'ID da Unidade de Transporte (Placa)' . ':*') !!}
                        {!! Form::text('id_unidade_transporte', null, ['class' => 'form-control type-ref', 'placeholder' => 'ID da Unidade de Transporte (Placa)', 'data-mask="AAA-AAAA", data-mask-reverse="true"' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('qtd_rateio_transporte', 'Quantidade de Rateio (Transporte)' . ':*') !!}
                        {!! Form::text('qtd_rateio_transporte', null, ['class' => 'form-control type-ref', 'placeholder' => 'Quantidade de Rateio (Transporte)' ]); !!}
                      </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="col-md-3">
                      <div class="form-group">
                        {!! Form::label('id_unidade_carga', 'ID Unidade da Carga' . ':*') !!}
                        {!! Form::text('id_unidade_carga', null, ['class' => 'form-control type-ref', 'placeholder' => 'ID Unidade da Carga' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-4">
                      <div class="form-group">
                        {!! Form::label('qtd_rateio_unidade', 'Quantidade de Rateio (Unidade Carga)' . ':*') !!}
                        {!! Form::text('qtd_rateio_unidade', null, ['class' => 'form-control type-ref', 'placeholder' => 'Quantidade de Rateio (Unidade Carga)' ]); !!}
                      </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('chave_nfe', 'Chave NFe' . ':*') !!}
                        {!! Form::text('chave_nfe', null, ['class' => 'form-control type-ref', 'placeholder' => 'Chave NFe' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('segunda_nfe', 'Segundo Código de Barra NFe (Contigencia)' . ':*') !!}
                        {!! Form::text('segunda_nfe', null, ['class' => 'form-control type-ref', 'placeholder' => 'Segundo Código de Barra NFe (Contigencia)' ]); !!}
                      </div>
                    </div>

                    <div class="clearfix"></div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('chave_cte', 'Chave CTe' . ':*') !!}
                        {!! Form::text('chave_cte', null, ['class' => 'form-control type-ref', 'placeholder' => 'Chave CTe' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('segunda_cte', 'Segundo Código de Barra CTe (Contigencia)' . ':*') !!}
                        {!! Form::text('segunda_cte', null, ['class' => 'form-control type-ref', 'placeholder' => 'Segundo Código de Barra CTe (Contigencia)' ]); !!}
                      </div>
                    </div>

                    <div class="col-md-5" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 5px; margin-left: 10px; margin-bottom: 10px;">

                      <div class="col-md-12">
                        <h3>Lacre de transporte</h3>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          {!! Form::label('lacre_transp', 'Lacre' . ':') !!}
                          {!! Form::text('lacre_transp', null, ['class' => 'form-control type-ref', 'placeholder' => 'Lacre', 'id' => 'lacre_transp' ]); !!}
                        </div>
                      </div>
                      <div class="col-md-4">
                        <button type="button" id="add-lacre-transp" class="btn btn-info" style="margin-top: 23px;">
                          Adicionar
                        </button>
                      </div>
                      <div class="col-md-12">
                        <div class="table-responsive">
                          <table class="table table-bordered table-striped" id="lacres_transp_table">
                            <thead>
                              <tr>
                                <th>Lacre</th>
                                <th>Ação</th>
                              </tr>
                            </thead>
                            <tbody>

                            </tbody>
                          </table>
                        </div>
                      </div>

                    </div> 

                    <div class="col-md-5" style="border: 1px solid #e0e0e0; border-radius: 5px; margin-top: 5px; margin-left: 10px; margin-bottom: 10px;">

                      <div class="col-md-12">
                        <h3>Lacre da unidade da carga</h3>
                      </div>

                      <div class="col-md-6">
                        <div class="form-group">
                          {!! Form::label('lacre_unid_carga', 'Lacre' . ':') !!}
                          {!! Form::text('lacre_transp', null, ['class' => 'form-control type-ref', 'placeholder' => 'Lacre', 'id' => 'lacre_unid_carga' ]); !!}
                        </div>
                      </div>
                      <div class="col-md-4">
                        <button type="button" id="add-lacre-unid" class="btn btn-info" style="margin-top: 23px;">
                          Adicionar
                        </button>
                      </div>
                      <div class="col-md-12">

                        <div class="table-responsive">
                          <table class="table table-bordered table-striped" id="lacres_unid_carga_table">
                            <thead>
                              <tr>
                                <th>Lacre</th>
                                <th>Ação</th>
                              </tr>
                            </thead>
                            <tbody>

                            </tbody>
                          </table>
                        </div>
                      </div>

                    </div> 

                    <div class="clearfix"></div>

                    <div class="col-md-6">
                      <div class="form-group">
                        {!! Form::label('municipio_descarregamento', 'Selecione o municipio de descarregamento' . ':') !!}
                        {!! Form::select('municipio_descarregamento', $cidades, '', ['class' => 'form-control select2', 'style="width: 100%"', 'id' => 'municipio_descarregamento', 'required']); !!}
                      </div>
                    </div>

                    <div class="col-md-3">
                      <button style="margin-top: 24px; width: 100%" type="button" id="add-descarregamento" class="btn btn-info">
                        Adicionar
                      </button>
                    </div>

                    <div class="col-md-12">
                      <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="descarregamento_table">
                          <thead>
                            <tr>
                              <th>Tipo transporte</th>
                              <th>Quantidade Rateio</th>
                              <th>NFe Referência</th>
                              <th>CTe Referência</th>
                              <th>Municipio descarregamento</th>
                              <th>Lacres de transp</th>
                              <th>Ações</th>
                            </tr>
                          </thead>
                          <tbody>

                          </tbody>
                        </table>
                      </div>
                    </div>

                  </div>
                </div>

              </div>


            </div>
          </div>
        </div>
      </div>

      <input type="hidden" name="municipios_descarregamentos" id="municipios_descarregamentos"> 
      <input type="hidden" name="descargas" id="descargas"> 
      <input type="hidden" name="ciots" id="ciots"> 
      <input type="hidden" name="vales" id="vales"> 
      <input type="hidden" name="percurso" id="percurso"> 

      <input type="hidden" value="{{$mdfe != null ? $mdfe->municipiosCarregamento : ''}}" id="init_municipios"> 
      <input type="hidden" value="{{$mdfe != null ? $mdfe->percurso : ''}}" id="init_percurso"> 
      <input type="hidden" value="{{$mdfe != null ? $mdfe->ciots : ''}}" id="init_ciot"> 
      <input type="hidden" value="{{$mdfe != null ? $mdfe->valesPedagio : ''}}" id="init_vale"> 

      <input type="hidden" value="{{$mdfe != null ? $mdfe->infoDescarga : ''}}" id="init_descargas"> 

      <input type="hidden" value="{{$mdfe != null ? $mdfe->id : ''}}" name="mdfe_id">
      <div class="col-md-12">
        <div class="col-md-5">
          <div class="form-group">
            {!! Form::label('info_complementar', 'Informação complementar' . ':') !!}
            {!! Form::text('info_complementar', $mdfe != null ? $mdfe->info_complementar : '', ['class' => 'form-control type-ref', 'placeholder' => 'Info complementar' ]); !!}
          </div>
        </div>

        <div class="col-md-7">
          <div class="form-group">
            {!! Form::label('info_adicional_fisco', 'Informação Fiscal' . ':*') !!}
            {!! Form::text('info_adicional_fisco', $mdfe != null ? $mdfe->info_adicional_fisco : '', ['class' => 'form-control type-ref', 'placeholder' => 'Informação Adicional' ]); !!}
          </div>
        </div>
      </div>
      <input type="hidden" value="{{json_encode($cidades)}}" id="cidades">

      @endcomponent
    </div>


  </div>

  @if(!empty($form_partials))
  @foreach($form_partials as $partial)
  {!! $partial !!}
  @endforeach
  @endif
  <div class="row">
    <div class="col-md-12">
      <button id="finalizar" type="submit" class="btn btn-primary pull-right disabled" id="submit_user_button">@if($mdfe != null) Atualizar @else Salvar @endif MDFe</button>
    </div>
  </div>
  <br><br>
  {!! Form::close() !!}
  @stop
  @section('javascript')
  <script type="text/javascript">
    var CIDADES = JSON.parse($('#cidades').val())
    var CIDADESADICIONADAS = [];
    var PERCURSO = [];
    var CIOT = [];
    var VALES = [];
    var LACRESTRANSPORTE = [];
    var LACRESUNIDADECARGA = [];
    var DESCARGAS = [];
    
    $('.type-ref').keyup(() => {
      habilitaBtnSalarMdfe()
    })

    $('#add-cidade').click(() => {
      let municipio = $('#municipio').val()
      CIDADESADICIONADAS.push(municipio);
      montaHtmlCidades((html) => {
        $('#cidades_table tbody').html(html)
        __set('municipios_descarregamentos', JSON.stringify(CIDADESADICIONADAS))
      });
      habilitaBtnSalarMdfe()
    })

    function montaHtmlCidades(call){
      let html = '';
      CIDADESADICIONADAS.map((c) => {
        let nomeCidade = CIDADES[c];
        html += '<tr>'
        html += '<td>'+nomeCidade+'</td>'
        html += '<td>'
        html += '<i onclick="removeCidade('+c+')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeCidade(id){
      let temp = [];
      CIDADESADICIONADAS.map((c) => {
        if(c != id) temp.push(c)
      })
      CIDADESADICIONADAS = temp;
      setTimeout(() => {
        montaHtmlCidades((html) => {
          $('#cidades_table tbody').html(html)
          __set('municipios_descarregamentos', JSON.stringify(CIDADESADICIONADAS))
        });
      }, 300)
    }

    //estados

    $('#add-uf').click(() => {
      let uf = $('#uf').val()
      PERCURSO.push(uf);
      montaHtmlUF((html) => {
        $('#ufs_table tbody').html(html)
        __set('percurso', JSON.stringify(PERCURSO))
      });
      habilitaBtnSalarMdfe()

    })

    function montaHtmlUF(call){
      let html = '';
      PERCURSO.map((uf) => {
        html += '<tr>'
        html += '<td>'+uf+'</td>'
        html += '<td>'
        html += '<i onclick="removeUF(\''+uf+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeUF(uf){
      let temp = [];
      PERCURSO.map((c) => {
        if(c != uf) temp.push(c)
      })
      PERCURSO = temp;
      setTimeout(() => {
        montaHtmlUF((html) => {
          $('#ufs_table tbody').html(html)
          __set('percurso', JSON.stringify(PERCURSO))
        });
      }, 300)
    }

    //ciot

    $('#add-ciot').click(() => {
      if($('#codigo_ciot').val() && $('#doc_ciot').val()){
        let js = {
          'codigo': $('#codigo_ciot').val(),
          'doc_ciot': $('#doc_ciot').val()
        }
        CIOT.push(js);
        montaHtmlCiot((html) => {
          $('#ciot_table tbody').html(html)
          __set('ciots', JSON.stringify(CIOT))
        });
      }else{
        swal("Erro", "Informe código e documento", "error")
      }
    })

    function montaHtmlCiot(call){
      let html = '';
      CIOT.map((c) => {
        html += '<tr>'
        html += '<td>'+c.codigo+'</td>'
        html += '<td>'+c.doc_ciot+'</td>'
        html += '<td>'
        html += '<i onclick="removeCiot(\''+c.codigo+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeCiot(cod){
      let temp = [];
      CIOT.map((c) => {
        if(c.codigo != cod) temp.push(c)
      })
      CIOT = temp;
      setTimeout(() => {
        montaHtmlCiot((html) => {
          $('#ciot_table tbody').html(html)
          __set('ciots', JSON.stringify(CIOT))
        });
      }, 300)
    }

    $('#add-vale').click(() => {
      if($('#vale_cnpj_fornecedor').val() && $('#vale_doc_pagador').val() && 
        $('#vale_numero_compra').val() && $('#vale_valor').val()){
        let js = {
          'cnpj_fornecedor': $('#vale_cnpj_fornecedor').val(),
          'doc_pagador': $('#vale_doc_pagador').val(),
          'numero_compra': $('#vale_numero_compra').val(),
          'valor': $('#vale_valor').val(),
        }
        VALES.push(js);
        montaHtmlVale((html) => {
          $('#vale_table tbody').html(html)
          __set('vales', JSON.stringify(VALES))
        });
      }else{
        swal("Erro", "Informe os dados corretamente", "error")
      }
    })

    function montaHtmlVale(call){
      let html = '';
      VALES.map((c) => {
        html += '<tr>'
        html += '<td>'+c.cnpj_fornecedor+'</td>'
        html += '<td>'+c.doc_pagador+'</td>'
        html += '<td>'+c.numero_compra+'</td>'
        html += '<td>'+c.valor+'</td>'
        html += '<td>'
        html += '<i onclick="removeVale(\''+c.numero_compra+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeVale(numero_compra){
      let temp = [];
      VALES.map((c) => {
        if(c.numero_compra != numero_compra) temp.push(c)
      })
      VALES = temp;
      setTimeout(() => {
        montaHtmlCiot((html) => {
          $('#vale_table tbody').html(html)
          __set('vales', JSON.stringify(VALES))
        });
      }, 300)
    }

    //Lacre de transporte

    $('#add-lacre-transp').click(() => {
      if($('#lacre_transp').val()){

        LACRESTRANSPORTE.push($('#lacre_transp').val());
        montaHtmlLaresTransp((html) => {
          $('#lacres_transp_table tbody').html(html)
        });
      }else{
        swal("Erro", "Informe o lacre", "error")
      }
    })

    function montaHtmlLaresTransp(call){
      let html = '';
      LACRESTRANSPORTE.map((c) => {
        html += '<tr>'
        html += '<td>'+c+'</td>'
        html += '<td>'
        html += '<i onclick="removeLacreTransp(\''+c+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeLacreTransp(cod){
      let temp = [];
      LACRESTRANSPORTE.map((c) => {
        if(c != cod) temp.push(c)
      })
      LACRESTRANSPORTE = temp;
      setTimeout(() => {
        montaHtmlLaresTransp((html) => {
          $('#lacres_transp_table tbody').html(html)
        });
      }, 300)
    }

    //Lacre de unidade carga

    $('#add-lacre-unid').click(() => {
      if($('#lacre_transp').val()){
        LACRESUNIDADECARGA.push($('#lacre_unid_carga').val());
        montaHtmlLaresUnidade((html) => {
          $('#lacres_unid_carga_table tbody').html(html)
        });
      }else{
        swal("Erro", "Informe o lacre", "error")
      }
    })

    function montaHtmlLaresUnidade(call){
      let html = '';
      LACRESUNIDADECARGA.map((c) => {
        html += '<tr>'
        html += '<td>'+c+'</td>'
        html += '<td>'
        html += '<i onclick="removeLacreUnidade(\''+c+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeLacreUnidade(cod){
      let temp = [];
      LACRESUNIDADECARGA.map((c) => {
        if(c != cod) temp.push(c)
      })
      LACRESUNIDADECARGA = temp;
      setTimeout(() => {
        montaHtmlLaresUnidade((html) => {
          $('#lacres_unid_carga_table tbody').html(html)
        });
      }, 300)
    }

    // add descarregamento
    $('#add-descarregamento').click(() => {
      let tipo_unidade_transporte = $('#tipo_unidade_transporte').val();
      let id_unidade_transporte = $('#id_unidade_transporte').val();
      let qtd_rateio_transporte = $('#qtd_rateio_transporte').val();
      let id_unidade_carga = $('#id_unidade_carga').val();
      let qtd_rateio_unidade = $('#qtd_rateio_unidade').val();
      let chave_nfe = $('#chave_nfe').val();
      let segunda_nfe = $('#segunda_nfe').val();
      let chave_cte = $('#chave_cte').val();
      let segunda_cte = $('#segunda_cte').val();
      let municipio_descarregamento = $('#municipio_descarregamento').val();

      validaDescarregamento((valid) => {
        if(valid == ""){
          let js = {
            rand: Math.floor(Math.random() * 1000),
            tipo_unidade_transporte: tipo_unidade_transporte,
            id_unidade_transporte: id_unidade_transporte,
            qtd_rateio_transporte: qtd_rateio_transporte,
            id_unidade_carga: id_unidade_carga,
            qtd_rateio_unidade: qtd_rateio_unidade,
            chave_nfe: chave_nfe,
            segunda_nfe: segunda_nfe,
            chave_cte: chave_cte,
            segunda_cte: segunda_cte,
            municipio_descarregamento: municipio_descarregamento,
            lacres_transporte: LACRESTRANSPORTE,
            lacres_unidade_carga: LACRESUNIDADECARGA,
          }

          DESCARGAS.push(js)
          montaHtmlDescarregamento((html) => {
            $('#descarregamento_table tbody').html(html)
            __set('descargas', JSON.stringify(DESCARGAS))
          });
        }else{
          swal("Atenção", valid, "warning");
        }
      })
    })


    function montaHtmlDescarregamento(call){
      let html = '';
      DESCARGAS.map((d) => {
        html += '<tr>'
        html += '<td>'+d.tipo_unidade_transporte+'</td>'
        html += '<td>'+d.qtd_rateio_transporte+'</td>'
        html += '<td>'+d.chave_nfe+'</td>'
        html += '<td>'+d.chave_cte+'</td>'
        html += '<td>'+CIDADES[d.municipio_descarregamento]+'</td>'
        html += '<td>'+LACRESTRANSPORTE+'</td>'
        html += '<td>'
        html += '<i onclick="removeDescarregamento(\''+d.rand+'\')" class="fa fa-trash text-danger"></i>'
        html += '</td>'
        html += '</tr>'
      })
      call(html)
    }

    function removeDescarregamento(rand){
      let temp = [];
      DESCARGAS.map((c) => {
        if(c.rand != rand) temp.push(c)
      })
      DESCARGAS = temp;
      setTimeout(() => {
        montaHtmlDescarregamento((html) => {
          $('#descarregamento_table tbody').html(html)
          __set('descargas', JSON.stringify(DESCARGAS))
        });
      }, 300)
    }

    function validaDescarregamento(call){
      let msg = "";
      if(!$('#tipo_unidade_transporte').val()){
        msg = "Informe o tipo da unidade de transporte\n";
      }
      if(!$('#id_unidade_transporte').val()){
        msg += "Informe o ID unidade de transporte\n";
      }
      if(!$('#qtd_rateio_transporte').val()){
        msg += "Informe a quantidade de rateio\n";
      }
      if(!$('#id_unidade_carga').val()){
        msg += "Informe ID da unidade da carga\n";
      }
      if(!$('#qtd_rateio_unidade').val()){
        msg += "Informe a quantidade de rateio\n";
      }
      if(!$('#qtd_rateio_unidade').val()){
        msg += "Informe a quantidade de rateio da unidade\n";
      }

      let chave_nfe = $('#chave_nfe').val();
      let segunda_nfe = $('#segunda_nfe').val();
      let chave_cte = $('#chave_cte').val();
      let segunda_cte = $('#segunda_cte').val();

      if(!chave_nfe && !segunda_nfe && !chave_cte && !segunda_cte){
        msg += "Referêncie um documento\n"
      }
      call(msg);
    }

    function habilitaBtnSalarMdfe(){
      validaFormulario((res) => {
        if(res){
          $('#finalizar').removeClass('disabled')
        }
      })
    }

    function validaFormulario(call){
      let inputs = ['uf_inicio', 'uf_fim', 'data_inicio_viagem', 'lac_rodo', 
      'cnpj_contratante', 'quantidade_carga', 'valor_carga', 'veiculo_tracao_id', 'condutor_nome', 
      'condutor_cpf'];
      validaInputs(inputs, (res) => {
        call(res)
      })

    }

    function validaInputs(arr, call){
      let retorno = true
      arr.map((v) => {
        if(!$('#'+v).val()){
          retorno = false
          console.log("aqui", v)

          $('#'+v).addClass('is-invalid')
        }else{
          $('#'+v).removeClass('is-invalid')
        }
      })
      if(CIDADESADICIONADAS.length == 0) retorno = false;
      if(DESCARGAS.length == 0) retorno = false;

      call(retorno)
    }

    function __set(input, valor){
      habilitaBtnSalarMdfe()
      $('#'+input).val(valor)
    }

    //para edit
    $(function() {
      //iniciando munucipios
      if($('#init_municipios').val()){
        let municipios = JSON.parse($('#init_municipios').val())
        municipios.map((m) => {
          console.log(m)
          CIDADESADICIONADAS.push(m.cidade_id)
        })

        montaHtmlCidades((html) => {
          $('#cidades_table tbody').html(html)
          __set('municipios_descarregamentos', JSON.stringify(CIDADESADICIONADAS))
        });
      }

      //iniciando percurso
      if($('#init_percurso').val()){
        let percurso = JSON.parse($('#init_percurso').val())
        percurso.map((m) => {
          console.log(m)
          PERCURSO.push(m.uf)
        })

        montaHtmlUF((html) => {
          $('#ufs_table tbody').html(html)
          __set('percurso', JSON.stringify(PERCURSO))
        });
      }

      //iniciando ciot
      if($('#init_ciot').val()){
        let ciots = JSON.parse($('#init_ciot').val())
        ciots.map((c) => {
          console.log(c)
          let js = {
            'codigo': c.codigo,
            'doc_ciot': c.cpf_cnpj
          }
          CIOT.push(js);
        })

        montaHtmlCiot((html) => {
          $('#ciot_table tbody').html(html)
          __set('ciots', JSON.stringify(CIOT))
        });
      }

      if($('#init_vale').val()){
        let vales = JSON.parse($('#init_vale').val())
        vales.map((v) => {

          let js = {
            'cnpj_fornecedor': v.cnpj_fornecedor,
            'doc_pagador': v.cnpj_fornecedor_pagador,
            'numero_compra': v.numero_compra,
            'valor': v.valor,
          }
          VALES.push(js);
        })

        montaHtmlVale((html) => {
          $('#vale_table tbody').html(html)
          __set('vales', JSON.stringify(VALES))
        });
      }


      // iniciando descargas

      if($('#init_descargas').val()){
        let descargas = JSON.parse($('#init_descargas').val())
        descargas.map((v) => {
          console.log(v)
          preparaLacres(v.lacres_transp, (lacrestTransp) => {
            preparaLacres(v.lacres_unid_carga, (lacrestUnidCarga) => {
              let js = {
                rand: Math.floor(Math.random() * 1000),
                tipo_unidade_transporte: v.tp_unid_transp,
                id_unidade_transporte: v.id_unid_transp,
                qtd_rateio_transporte: v.quantidade_rateio,
                id_unidade_carga: v.unidade_carga.id_unidade_carga,
                qtd_rateio_unidade: v.unidade_carga.quantidade_rateio,
                chave_nfe: v.nfe.chave,
                segunda_nfe: v.nfe.seg_cod_barras,
                chave_cte: v.cte ? v.cte.chave : '',
                segunda_cte: v.cte ? v.cte.seg_cod_barras : '',
                municipio_descarregamento: v.cidade_id,
                lacres_transporte: lacrestTransp,
                lacres_unidade_carga: lacrestUnidCarga,
              }

              console.log(js)
              DESCARGAS.push(js)
            });
          });
        })

        montaHtmlDescarregamento((html) => {
          $('#descarregamento_table tbody').html(html)
          __set('descargas', JSON.stringify(DESCARGAS))
        });


      }
      setTimeout(() => {
        habilitaBtnSalarMdfe()
      },300)
    });

    function preparaLacres(objeto, call){
      let t = [];
      objeto.map((l) => {
        t.push(l.numero)
      })
      call(t)
    }

    $(document).on('click', '#finalizar', function(e) {
      e.preventDefault();

      $('form#mdfe_add_form').validate()
      if ($('form#mdfe_add_form').valid()) {
        $('form#mdfe_add_form').submit();
      }
    })

  </script>
  @endsection
