@extends('layouts.app')

@section('title', 'Emitir NFC-e')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Emitir NFC-e</h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
    <div class="col-md-12">
      @component('components.widget')
      <input type="hidden" id="id" value="{{$transaction->id}}" name="">
      <div class="col-md-5">
        <h4>Local: <strong>{{$transaction->location->name}} - {{$transaction->location->location_id}}</strong></h4>
        <h4>Ultimo numero NFC-e: <strong>{{$transaction->lastNFCe($transaction)}}</strong></h4>
        <h4>Valor: <strong>{{number_format($transaction->final_total, 2, ',', '.')}}</strong></h4>

      </div>

      <div class="clearfix"></div>

      <div class="col-md-12">

        <a class="btn btn-lg btn-danger" target="_blank" href="/nfce/gerarXml/{{$transaction->id}}" id="submit_user_button">Gerar XML</a>
        <a class="btn btn-lg btn-success" id="send-sefaz">Transmitir para Sefaz</a>
      </div>


      @endcomponent
    </div>

  </div>



  <input type="hidden" id="token" value="{{csrf_token()}}" name="">

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
      var notClick = false
      $('#send-sefaz').click(() => {
        if(!notClick){
          notClick = true;
          $('#send-sefaz').attr('disabled', true)
          $('#action').css('display', 'block')
          let token = $('#token').val();
          let id = $('#id').val();

          setTimeout(() => {
            $('#acao').html('Gerando XML');
          }, 50);

          setTimeout(() => {
            $('#acao').html('Assinando o arquivo');
          }, 800);

          setTimeout(() => {
            $('#acao').html('Transmitindo para sefaz');
          }, 1500);

          $.ajax
          ({
            type: 'POST',
            data: {
              id: id,
              _token: token
            },
            url: path + '/nfce/transmitir',
            dataType: 'json',
            success: function(e){
              console.log(e)

              swal("Sucesso", "NFC-e emitida, recibo: " + e, "success")
              .then(() => {
                window.open(path + '/nfce/imprimir/'+id)
                location.reload()
              });
              $('#action').css('display', 'none')


            }, error: function(e){
              $('#acao').html('')
              $('#action').css('display', 'none')
              console.log(e)
              try{
                if(e.status == 401){
                  swal("Erro ao transmitir", "[" + e.responseJSON.message.cStat + "]" + e.responseJSON.message.xMotivo, "error");
                  $('#action').css('display', 'none')

                }
                else if(e.status == 402){
                  swal("Erro ao transmitir", e.responseJSON, "error");
                  $('#action').css('display', 'none')

                }
                else if(e.status == 407){
                  $('#action').css('display', 'none')
                  swal("Erro ao criar XML", e.responseJSON, "error");
                }
                else{

                  $('#action').css('display', 'none')
                  try{

                    let jsError = JSON.parse(e.responseJSON)
                    console.log(jsError)
                    swal("Erro ao transmitir", "[" + jsError.protNFe.infProt.cStat +  "]" + jsError.protNFe.infProt.xMotivo, "error");
                  }catch{
                    swal("Erro ao transmitir", e.responseJSON, "error");

                  }

                }
              }catch{
                try{
                  swal("Erro", e.responseJSON, "error");
                }catch{
                  let js = e.responseJSON
                  swal("Erro", js.message, "error");


                }
              }
            }

          })
        }
      })


    </script>
    @endsection
