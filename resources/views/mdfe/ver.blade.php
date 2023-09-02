@extends('layouts.app')

@section('title', 'Manter MDF-e')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Manter MDF-e</h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
    <div class="col-md-12">
      @component('components.widget')
      
      <input type="hidden" id="id" value="{{$mdfe->id}}" name="">
      <div class="col-md-12">
        <h4>Número MDF-e: <strong>{{$mdfe->mdfe_numero}}</strong></h4>
        <h4>UF início/UF fim: <strong>{{$mdfe->uf_inicio}}/{{$mdfe->uf_fim}}</strong></h4>
        <h4>Inicio da viagem: <strong>{{ \Carbon\Carbon::parse($mdfe->data_inicio_viagem)->format('d/m/Y')}}</strong></h4>

        <h4>Estado: <strong>{{$mdfe->estado}}</strong></h4>
        <h4>Valor da carga: <strong>R$ {{number_format($mdfe->valor_carga, 2, ',', '.')}}</strong></h4>

        <h4>Condutor: <strong>{{$mdfe->condutor_nome}} - {{$mdfe->condutor_cpf}}</strong></h4>
      </div>

      <input type="hidden" id="mdfe_numero" value="{{$mdfe->mdfe_numero}}" name="">
      
      <div class="clearfix"></div>


      <div class="col-md-12">
        <a class="btn btn-lg btn-primary" target="_blank" href="/mdfe/imprimir/{{$mdfe->id}}" id="submit_user_button">Imprimir</a>
        <a class="btn btn-lg btn-info" target="_blank" href="/mdfe/baixarXml/{{$mdfe->id}}" id="submit_user_button">Baixar XML</a>

        <a class="btn btn-lg btn-question" style="background: #673ab7; color: #fff" id="consultar">
        Consultar</a>
        
        @if($mdfe->estado != 'CANCELADO')
        <a class="btn btn-lg btn-danger" id="cancelar">Cancelar MDF-e</a>
        <!-- <a class="btn btn-lg btn-warning" id="corrigir">Corrigir CT-e</a> -->
        @endif


        @if($mdfe->estado == 'CANCELADO')
        <a class="btn btn-lg btn-question" style="background: #d84315; color: #fff" target="_blank" href="/mdfe/imprimirCancelamento/{{$mdfe->id}}" id="submit_user_button">Imprimir Cancelamento</a>
        @endif
      </div>
      
      @endcomponent
    </div>

  </div>

  

  <input type="hidden" id="token" value="{{csrf_token()}}" name="">
  <input type="hidden" id="id" value="{{$mdfe->id}}" name="">

  <br>
  <div class="row" id="action" style="display: none">
    <div class="col-md-12">
      @component('components.widget')
      <div class="info-box-content">
        <div class="col-md-4 col-md-offset-4">

          <span class="info-box-number total_purchase">
            <strong id="acao"></strong>
            <i class="fas fa-spinner fa-pulse fa-spin fa-fw margin-bottom"></i></span>
          </div>
        </div>
        @endcomponent

      </div>
    </div>

    @stop



    @section('javascript')
    <script type="text/javascript">
      // swal("Good job!", "You clicked the button!", "success");
      var path = window.location.protocol + '//' + window.location.host

      $('#cancelar').click(() => {
        let mdfe_numero = $('#mdfe_numero').val();
        swal({
          text: 'Cancelamento de MDF-e '+ mdfe_numero +'.',
          content: "input",
          button: {
            text: "Cancelar!",
            closeModal: false,
            type: 'error'
          },
          confirmButtonColor: "#DD6B55",
        })
        .then(v => {
          if (!v) swal("Erro!", "Informe um motivo para Cancelamento!", "error");
          else{
            let token = $('#token').val();
            let id = $('#id').val();
            $.ajax
            ({
              type: 'POST',
              data: {
                id: id,
                _token: token,
                justificativa: v
              },
              url: path + '/mdfe/cancelar',
              dataType: 'json',
              success: function(e){
                console.log("retorno", e)

                swal("sucesso", e.infEvento.xMotivo, "success")
                .then(() => {
                  location.reload()
                });

              }, error: function(e){
                console.log("erro",e)
                try{
                  swal("Erro ao cancelar", e.responseJSON.infEvento.xMotivo, "error");
                }catch{
                  swal("Erro ao cancelar", e.responseJSON.message, "error");

                }

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


      $('#consultar').click(() => {

        let token = $('#token').val();
        let id = $('#id').val();

        $.ajax
        ({
          type: 'POST',
          data: {
            id: id,
            _token: token
          },
          url: path + '/mdfe/consultar',
          dataType: 'json',
          success: function(e){
            console.log(e)

            swal("sucesso", "Resultado: " + e.xMotivo + " - Chave: " + e.protMDFe.infProt.chMDFe, "success")
            .then(() => {
            });


          }, error: function(e){
            console.log(e)
            swal("Erro ao consultar", e.responseJSON, "error");

          }

        })
      })


    </script>
    @endsection
