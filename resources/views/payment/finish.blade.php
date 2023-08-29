@extends('layouts.app')
@section('title', 'Pagamento de Plano')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Pagamento
        <small>Plano</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <input type="hidden" value="{{$paymentPlan->transacao_id}}" id="transacao_id" name="">
    <input type="hidden" value="{{$paymentPlan->forma_pagamento}}" id="forma_pagamento" name="">

    @component('components.widget', ['class' => 'box-success', 'title' => 'Pagamento de plano'])
    
    <div class="row">
        <div class="col-lg-12">
            <h4>Valor: <strong class="">R$ {{ number_format($paymentPlan->valor, 2, ',', '.') }}</strong></h4>

            @if($paymentPlan->forma_pagamento == 'pix')
            <h3 style="display: none" class="text-success status">Pagamento aprovado <i class="fa fa-check"></i></h3>

            <div class="col-lg-4">
            </div>
            <div class="col-lg-4">
                <img style="width: 300px; height: 300px;" src="data:image/jpeg;base64,{{$paymentPlan->qr_code_base64}}"/>
            </div>  

            <div class="row">

                <div class="col-md-10 p-0 mt-1">
                    <input type="text" class="form-control w-100" value="{{$paymentPlan->qr_code}}" id="qrcode_input" />
                </div>
                <div class="col-md-2 p-0">
                    <button onclick="copy()" class="btn btn-info">
                        <i class="fa fa-copy">
                        </i>
                    </button>
                </div>

            </div>

            @endif

            @if($paymentPlan->forma_pagamento == 'boleto')
            <h3 class="text-success">Boleto gerado <i class="fa fa-check"></i></h3>
            <a target="_blank" class="btn btn-success" href="{{$paymentPlan->link_boleto}}"><i class="fa fa-print"></i> Imprimir</a>

            <h4>Forma de pagamento escolhida: <strong>Boleto bancário</strong></h4>

            @endif

            @if($paymentPlan->forma_pagamento == 'cartao')
            <h3 class="text-success">Pagamento concluído, obrigado por escolher nossa plataforma. <i class="fa fa-check"></i></h3>

            <h4>Forma de pagamento escolhida: <strong>Cartão de crédito</strong></h4>
            @endif
        </div>

    </div>
    @endcomponent

</div>

</section>


<!-- /.content -->
@stop
@section('javascript')

<script type="text/javascript">
    var status = 0

    function copy(){
        const inputTest = document.querySelector("#qrcode_input");

        inputTest.select();
        document.execCommand('copy');

        swal("", "Código pix copado!!", "success")
    }

    setInterval(() => {
        let forma_pagamento = $('#forma_pagamento').val();
        if(status == 0 && forma_pagamento == 'pix'){
            let transacao_id = $('#transacao_id').val();
            console.log(transacao_id)
            $.get('/api/consultaPix/'+transacao_id)
            .done((success) => {
                console.log("data", success)
                if(success == "approved"){
                    swal("Sucesso", "Pagamento aprovado!", "success")
                    .then(() => {
                        location.href = "/home";
                    })
                    status = 1
                    $('.status').css('display', 'block')

                }
            })
            .fail((err) => {
                console.log(err)
            })
        }
    }, 2000)
</script>
@endsection
