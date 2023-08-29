@extends('layouts.app')

@section('title', 'Pedido')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Pedido {{$pedido->id}}</h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">



    <form method="post" action="/pedidosEcommerce/salvarVenda">

      @if(count($business_locations) == 1)
      @php 
      $default_location = current(array_keys($business_locations->toArray()));
      $search_disable = false; 
      @endphp
      @else
      @php $default_location = null;
      $search_disable = true;
      @endphp
      @endif
      <div class="col-sm-3">
        <div class="form-group">
          {!! Form::label('location_id', __('purchase.business_location').':*') !!}
          @show_tooltip(__('tooltip.purchase_location'))
          {!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
        </div>
      </div>
      <div class="clearfix"></div>
      
      @csrf
      <input type="hidden" value="{{$pedido->id}}" name="id">

      <div class="col-sm-6">
        @component('components.widget')

        <h3>Dados do cliente</h3>
        <div class="col-md-12">
          <h4>Cliente: <strong>{{$pedido->cliente->nome}} {{$pedido->cliente->sobre_nome}}</strong></h4>
          <h4>CPF: <strong>{{$pedido->cliente->cpf}}</strong></h4>
          <h4>Email: <strong>{{$pedido->cliente->email}}</strong></h4>
          <h4>Telefone: <strong>{{$pedido->cliente->telefone}}</strong></h4>
          <a href="/clienteEcommerce/edit/{{$pedido->cliente->id}}" class="btn btn-primary">
            <i class="fa fa-edit"></i>
          </a>
        </div>
        
        @endcomponent
      </div>

      <div class="col-sm-6">
        @component('components.widget')

        <h3>Endereço de entrega</h3>
        <div class="col-md-12">
          <h4>Rua: <strong>{{$pedido->endereco->rua}}, {{$pedido->endereco->numero}}</strong></h4>
          <h4>Bairro: <strong>{{$pedido->endereco->bairro}} - {{$pedido->endereco->complemento}}</strong></h4>
          <h4>Cep: <strong>{{$pedido->endereco->cep}}</strong></h4>
          <h4>Cidade: <strong>{{$pedido->endereco->cidade}} ({{$pedido->endereco->uf}})</strong></h4>
          <a href="/enderecosEcommerce/edit/{{$pedido->endereco->id}}" class="btn btn-primary">
            <i class="fa fa-edit"></i>
          </a>
        </div>
        @endcomponent

      </div>


      <div class="col-sm-12">
        @component('components.widget')

        
        <div class="col-md-3 customer_fields">
          <div class="form-group">

            {!! Form::label('natureza', 'Natureza de Operação' . ':') !!}
            {!! Form::select('natureza', $naturezas, '', ['id' => 'tipo', 'class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="col-md-3 customer_fields">
          <div class="form-group">

            {!! Form::label('transportadora', 'Transportadora' . ':') !!}
            {!! Form::select('transportadora', $transportadoras, '', ['id' => 'tipo', 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-md-3 customer_fields">
          <div class="form-group">

            {!! Form::label('frete', 'Tipo frete' . ':') !!}
            {!! Form::select('frete', $tiposFrete, '', ['id' => 'tipo', 'class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('valor_frete', 'Valor do frete' . ':*') !!}
            {!! Form::text('valor_frete', $pedido->valor_frete, ['class' => 'form-control', 'required', 'placeholder' => 'Valor do frete' ]); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('placa', 'Placa Veiculo' . ':*') !!}
            {!! Form::text('placa', '', ['class' => 'form-control', 'placeholder' => 'Placa Veiculo', 'data-mask="AAA-AAAA"', 'data-mask-reverse="true"']); !!}
          </div>
        </div>

        <div class="col-md-2 customer_fields">
          <div class="form-group">

            {!! Form::label('uf_placa', 'UF' . ':') !!}
            {!! Form::select('uf_placa', $ufs, '', ['id' => 'tipo', 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('qtd_volumes', 'Qtd Volumes' . ':*') !!}
            {!! Form::text('qtd_volumes', '1', ['class' => 'form-control', 'placeholder' => 'Qtd Volumes']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('numeracao_volumes', 'Num. Volumes' . ':*') !!}
            {!! Form::text('numeracao_volumes', '1', ['class' => 'form-control', 'placeholder' => 'Num. Volumes']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('especie', 'Espécie' . ':*') !!}
            {!! Form::text('especie', '', ['class' => 'form-control', 'placeholder' => 'Espécie']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('peso_liquido', 'Peso liquído' . ':*') !!}
            {!! Form::text('peso_liquido', number_format($pedido->somaPeso(), 3), ['class' => 'form-control', 'placeholder' => 'Peso liquído', 'data-mask="00000,000"', 'data-mask-reverse="true"']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('peso_bruto', 'Peso bruto' . ':*') !!}
            {!! Form::text('peso_bruto', number_format($pedido->somaPeso(), 3), ['class' => 'form-control', 'placeholder' => 'Peso bruto', 'data-mask="00000,000"', 'data-mask-reverse="true"']); !!}
          </div>
        </div>

        <div class="col-sm-12">
          <h3>Total do pedido: <strong>R$ {{ number_format($pedido->valor_total, 2, ',', '.')}}</strong></h3>
        </div>

        @endcomponent
      </div>



      <div class="col-sm-12">
        @if(sizeof($erros) == 0)
        <button class="btn btn-success btn-lg">
          <i class="fa fa-check"></i>
          Salvar
        </button>

        @else
        @foreach($erros as $e)
        <p>
          <span class="label label-xl label-inline label-light-danger">
            {{$e}}
          </span>
        </p>
        @endforeach
        @endif
      </div>

    </form>

  </div>
</section>


@stop



@section('javascript')

<script type="text/javascript">
  var path = window.location.protocol + '//' + window.location.host


</script>

@endsection
