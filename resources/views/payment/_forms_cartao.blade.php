{!! Form::open(['url' => action('PaymentController@paymentCartao'), 'method' => 'post', 'id' => 'form_cartao' ]) !!}

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('plano_id', 'Plano') !!}
            {!! Form::select('plano_id', ['' => 'Selecione o plano'] + $planos->pluck('info', 'id')->all(), '', ['class' => 'form-control', 'id' => 'plano_cartao_id', 'required']); !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('cardholderName', 'Titular do cartão') !!}
            {!! Form::text('cardholderName', null, ['class' => 'form-control', 'placeholder' => 'Nome', 'required', 'data-checkout' => 'cardholderName' ]); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('docType', 'Tipo do documento') !!}
            {!! Form::select('docType', [], '', ['class' => 'form-control', 'id' => 'docType3', 'required', 'data-checkout' => 'docType']); !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('docNumber', 'Número do documento') !!}
            {!! Form::tel('docNumber', null, ['class' => 'form-control cpf_cnpj', 'placeholder' => 'Número do documento', 'required', 'data-checkout' => 'docNumber', 'id' => 'docNumberCartao']); !!}
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('payerEmail', 'Email') !!}
            {!! Form::email('payerEmail', null, ['class' => 'form-control', 'placeholder' => 'Email', 'required', 'data-checkout' => 'payerEmail']); !!}
        </div>
    </div>

    <div class="col-md-4">
        <div class="col-md-10">
            <div class="form-group">
                {!! Form::label('cardNumber', 'Número do cartão') !!}
                {!! Form::tel('cardNumber', null, ['class' => 'form-control', 'placeholder' => 'Número do cartão', 'required', 'data-checkout' => 'cardNumber', 'data-mask' => '0000 0000 0000 0000']); !!}
            </div>
        </div>
        <div class="col-md-2 card-band">
            <img id="band-img" style="width: 20px; margin-top: 30px;" src="">
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('installments', 'Parcelas') !!}
            {!! Form::select('installments', [], '', ['class' => 'form-control', 'required']); !!}
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('cardExpirationMonth', 'Venc. Mês') !!}
            {!! Form::tel('cardExpirationMonth', null, ['class' => 'form-control', 'placeholder' => 'Venc. Mês', 'required', 'data-checkout' => 'cardExpirationMonth', 'data-mask' => '00']); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('cardExpirationYear', 'Venc. Ano') !!}
            {!! Form::tel('cardExpirationYear', null, ['class' => 'form-control', 'placeholder' => 'Venc. Ano', 'required', 'data-checkout' => 'cardExpirationYear', 'data-mask' => '00']); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('securityCode', 'Código de segurança') !!}
            {!! Form::tel('securityCode', null, ['class' => 'form-control', 'placeholder' => 'Código de segurança', 'required', 'data-checkout' => 'securityCode', 'data-mask' => 'AAAA']); !!}
        </div>
    </div>

    <input style="visibility: hidden" name="paymentMethodId" id="paymentMethodId" />
    <input style="visibility: hidden;" type="" name="transactionAmount" id="transactionAmount" value="" />

    <select style="visibility: hidden"  class="custom-select" id="issuer" name="issuer" data-checkout="issuer">
    </select>

</div>
<div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-success pull-right" id="submit_button_cartao">
        <i style="display: none" class="fa fa-spinner fa-spin"></i> Pagar com Cartão de Crédito</button>
  </div>
</div>  
{!! Form::close() !!}
