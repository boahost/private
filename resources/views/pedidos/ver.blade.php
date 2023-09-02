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

    <input type="hidden" id="pedido_id" value="{{$pedido->id}}" name="">
    <input type="hidden" id="token" value="{{csrf_token()}}" name="">

    <div class="col-sm-12">
      @component('components.widget')

      <h3>Dados do Pedido</h3>
      <div class="col-sm-6">
        <h4>Valor Total: <strong>{{ number_format($pedido->valor_total, 2, ',', '.')}}</strong></h4>
        <h4>Valor Frete: <strong>{{ number_format($pedido->valor_frete, 2, ',', '.')}}</strong></h4> 
        <h4>Token: <strong>{{$pedido->token}}</strong></h4>
        <h4>NFe: <strong>{{$pedido->numero_nfe == "" ? '--' : $pedido->numero_nfe}}</strong></h4>
        <h4>Forma de pagamento: <strong>{{$pedido->forma_pagamento}}</strong></h4>
        <h4>Data: <strong>{{\Carbon\Carbon::parse($pedido->created_at)->format('d/m/y H:i:s')}}</strong></h4>

      </div>
      <div class="col-sm-6">
        <h4>Código transação Mercado Pago: <strong>{{$pedido->transacao_id}}</strong></h4>
        <h4>Status detalhe Mercado Pago: <strong>{{$pedido->status_detalhe}}</strong></h4>
        <h4>Status pagamento Mercado Pago: <strong>{{$pedido->status_pagamento}}</strong></h4>

        <h4>Código rastreio: <strong>{{$pedido->codigo_rastreio == "" ? '--' : $pedido->codigo_rastreio}}</strong> 
          <button class="btn btn-primary" id="codigo_rastreio"><i class="fa fa-edit"></i></button>
        </h4>

        @if($pedido->cupom_desconto != "")
        <h4>Cupom de desconto: <strong>{{$pedido->cupom_desconto}} - {{$pedido->cupom->tipo == 'valor' ? 'R$' : ''}} {{number_format($pedido->cupom->valor, 2, ',', '.')}}{{$pedido->cupom->tipo == 'valor' ? '' : '%'}}</strong></h4>
        <h4>Valor do desconto: <strong>R$ {{number_format($pedido->valor_desconto, 2, ',', '.')}}</strong></h4>

        @endif
      </div>
      <div class="col-sm-12">
        @if($pedido->forma_pagamento == 'PIX')
        <h4>Chave copia e cola gerada: <strong>{{$pedido->qr_code}}</strong></h4>
        @endif

        @if($pedido->forma_pagamento == 'Boleto')
        <a class="btn btn-primary" target="_blank" href="{{$pedido->link_boleto}}">
          <i class="fa fa-print"></i>
          Imprimir Boleto
        </a>
        @endif
      </div>
      @endcomponent
    </div>

    <div class="col-sm-6">
      @component('components.widget')

      <h3>Dados do cliente</h3>
      <div class="col-md-12">
        <h4>Cliente: <strong>{{$pedido->cliente->nome}} {{$pedido->cliente->sobre_nome}}</strong></h4>
        <h4>CPF: <strong>{{$pedido->cliente->cpf}}</strong></h4>
        <h4>Email: <strong>{{$pedido->cliente->email}}</strong></h4>
        <h4>Telefone: <strong>{{$pedido->cliente->telefone}}</strong></h4>
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
      </div>
      @endcomponent

    </div>


    <div class="col-sm-12">
      @component('components.widget')

      <h3>Produtos do pedido</h3>
      <hr>
      <div class="col-md-12">
        <table width="100%">
          <thead>
            <tr>
              <th></th>
              <th>Produto</th>
              <th>Quantidade</th>
              <th>Valor Unitário</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            @foreach($pedido->itens as $i)


            <tr>
              <th width="20%">
                <img width="100" src="{{$i->produto->image_url}}">
              </th>
              <th width="30%">
                {{$i->produto->name}}
                @if($i->produto->type == 'variable')
                - {{$i->variacao->name}}
                @endif
              </th>
              <th>{{$i->quantidade}}</th>
              @if($i->produto->type == 'single')
              <th>{{ number_format($i->produto->valor_ecommerce, 2, ',', '.') }}</th>
              <th>{{ number_format($i->produto->valor_ecommerce*$i->quantidade, 2, ',', '.') }}</th>
              @else
              <th>{{ number_format($i->variacao->default_sell_price, 2, ',', '.') }}</th>
              <th>{{ number_format($i->variacao->default_sell_price*$i->quantidade, 2, ',', '.') }}</th>
              @endif
            </tr>

            @endforeach
          </tbody>
        </table>
        
      </div>
      @endcomponent

      <a href="/pedidosEcommerce/gerarNFe/{{$pedido->id}}" class="btn btn-primary btn-lg">
        <i class="fa fa-file"></i>
        Gerar NFe
      </a>

    </div>


  </div>
</section>


@stop



@section('javascript')

<script type="text/javascript">
  var path = window.location.protocol + '//' + window.location.host

  $('#codigo_rastreio').click(() => {
    let numero_nfe = $('#numero_nfe').val();
    swal({
      title: 'Código de rastreio',
      text: '',
      content: "input",
      button: {
        text: "Salvar!",
        closeModal: false,
        type: 'error'
      },
      confirmButtonColor: "#000",
    })
    .then(v => {
      if (!v){ 
        swal("Erro!", "Informe um código válido", "error");

      }
      else{
        let token = $('#token').val();
        let id = $('#pedido_id').val();

        $.ajax
        ({
          type: 'POST',
          data: {
            id: id,
            _token: token,
            codigo: v
          },
          url: path + '/pedidosEcommerce/salvarCodigo',
          dataType: 'json',
          success: function(e){
            console.log(e)

            swal("sucesso", "Código salvo!", "success")
            .then(() => {
              location.reload()
            });

          }, error: function(e){
            console.log(e)
            swal("Erro", "Erro ao salvar código", "error");

          }

        })
      }         


    })

    .catch(err => {
      if (err) {
        swal("Erro", "Algo não ocorreu bem!", "error");
      } else {
        swal.stopLoading();
        swal.close();
      }
    });
  })
</script>

@endsection
