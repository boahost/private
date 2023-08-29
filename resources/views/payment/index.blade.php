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


    @component('components.widget', ['class' => 'box-success', 'title' => 'Pagamento de plano'])
    
    <div class="row">
        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified">
                    <li class="active">
                        <a href="#tab_pix" data-toggle="tab" aria-expanded="true">PIX</a>
                    </li>
                    <li class="">
                        <a href="#tab_card" data-toggle="tab" aria-expanded="false">Cartão</a>
                    </li>
                    <li class="''">
                        <a href="#tab_boleto" data-toggle="tab" aria-expanded="false">Boleto</a>
                    </li>

                </ul>

                <div class="tab-content">
                    <div class="tab-pane active" id="tab_pix">
                        @include('payment._forms_pix')
                    </div>
                    <div class="tab-pane " id="tab_card">
                        @include('payment._forms_cartao')
                    </div>
                    <div class="tab-pane ''" id="tab_boleto">
                        @include('payment._forms_boleto')
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcomponent

</div>

</section>


<!-- /.content -->
@stop
@section('javascript')
<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

<script type="text/javascript">
    $(function () {
        window.Mercadopago.setPublishableKey('{{getenv('MERCADOPAGO_PUBLIC_KEY')}}');
        window.Mercadopago.getIdentificationTypes();

        setTimeout(() => {
            let s = $('#docType').html()

            $('#docType2').html(s)
            $('#docType3').html(s)
        }, 2000)
        getValorPlano()

    });

    $(document).on('click', '#submit_button_boleto', function(e) {
        e.preventDefault();

        $('form#form_boleto').validate()
        if ($('form#form_boleto').valid()) {
            $('form#form_boleto').submit();
        }
    })

    $(document).on('click', '#submit_button_pix', function(e) {
        e.preventDefault();

        $('form#form_pix').validate()
        if ($('form#form_pix').valid()) {
            $('form#form_pix').submit();
        }
    })
    $(document).on('click', '#submit_button_cartao', function(e) {
        $('.fa-spin').css('display', 'inline-block')
    })

    function getValorPlano(){
        let plano_cartao_id = $('#plano_cartao_id').val();
        if(plano_cartao_id){
            $.get('/api/consultaValorPlano/'+plano_cartao_id)
            .done((success) => {
                console.log(success)
                $('#transactionAmount').val(success)
            })
            .fail((err) => {
                console.log(err)
            })
        }
    }

    $('#plano_cartao_id').change(() => {
        getValorPlano()
    })

    $('#cardNumber').keyup(() => {
        let cardnumber = $('#cardNumber').val().replaceAll(" ", "");
        let plan = $('#plano_cartao_id').val()
        if(!plan){
            swal("Alerta", "Selecione o plano", "error")
            return
        }
        if (cardnumber.length >= 6) {


            let bin = cardnumber.substring(0,6);

            window.Mercadopago.getPaymentMethod({
                "bin": bin
            }, setPaymentMethod);
        }
    })

    function setPaymentMethod(status, response) {
        if (status == 200) {
            let paymentMethod = response[0];
            document.getElementById('paymentMethodId').value = paymentMethod.id;

            $('#band-img').attr("src", paymentMethod.thumbnail);

            $('.card-band').css('display', 'block')
            console.log("paymentMethod.id", paymentMethod.id)
            getIssuers(paymentMethod.id);
        } else {
            alert(`payment method info error: ${response}`);
        }
    }

    function getIssuers(paymentMethodId) {
        window.Mercadopago.getIssuers(
            paymentMethodId,
            setIssuers
            );
    }

    function setIssuers(status, response) {
        if (status == 200) {
            let issuerSelect = document.getElementById('issuer');
            $('#issuer').html('');
            response.forEach( issuer => {
                let opt = document.createElement('option');
                opt.text = issuer.name;
                opt.value = issuer.id;
                issuerSelect.appendChild(opt);
            });

            getInstallments(
                document.getElementById('paymentMethodId').value,
                document.getElementById('transactionAmount').value,
                issuerSelect.value
                );
        } else {
            alert(`issuers method info error: ${response}`);
        }
    }

    function getInstallments(paymentMethodId, transactionAmount, issuerId){
        window.Mercadopago.getInstallments({
            "payment_method_id": paymentMethodId,
            "amount": parseFloat(transactionAmount),
            "issuer_id": parseInt(issuerId)
        }, setInstallments);
    }

    function setInstallments(status, response){
        if (status == 200) {
            document.getElementById('installments').options.length = 0;
            response[0].payer_costs.forEach( payerCost => {
                let opt = document.createElement('option');
                opt.text = payerCost.recommended_message;
                opt.value = payerCost.installments;
                document.getElementById('installments').appendChild(opt);
            });
        } else {
            alert(`installments method info error: ${response}`);
        }
    }

    doSubmit = false;
    document.getElementById('form_cartao').addEventListener('submit', getCardToken);
    function getCardToken(event){

        let docNumberCartao = $('#docNumberCartao').val()
        docNumberCartao = docNumberCartao.replace(/[^0-9]/g, '')
        $('#docNumberCartao').val(docNumberCartao)

        event.preventDefault();
        if(!doSubmit){
            let $form = document.getElementById('form_cartao');
            window.Mercadopago.createToken($form, setCardTokenAndPay);
            return false;
        }
    };

    function setCardTokenAndPay(status, response) {
        if (status == 200 || status == 201) {
            let form = document.getElementById('form_cartao');
            let card = document.createElement('input');

            card.setAttribute('name', 'token');
            card.setAttribute('type', 'hidden');
            card.setAttribute('value', response.id);
            console.log(card)
            form.appendChild(card);
            doSubmit=true;

            form.submit();
        } else {
            alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
        }
    };
</script>
@endsection
