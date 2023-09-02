{!! Form::open(['url' => action('PaymentController@paymentBoleto'), 'method' => 'post', 'id' => 'form_boleto' ]) !!}

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('plano_id', 'Plano') !!}
            {!! Form::select('plano_id', ['' => 'Selecione o plano'] + $planos->pluck('info', 'id')->all(), '', ['class' => 'form-control', 'id' => 'plano_id', 'required']); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('payerFirstName', 'Nome') !!}
            {!! Form::text('payerFirstName', null, ['class' => 'form-control', 'placeholder' => 'Nome', 'required' ]); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('payerLastName', 'Sobre Nome') !!}
            {!! Form::text('payerLastName', null, ['class' => 'form-control', 'placeholder' => 'Sobre Nome', 'required' ]); !!}
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('payerEmail', 'Email') !!}
            {!! Form::email('payerEmail', null, ['class' => 'form-control', 'placeholder' => 'Email', 'required' ]); !!}
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label('docType', 'Tipo do documento') !!}
            {!! Form::select('docType', [], '', ['class' => 'form-control', 'id' => 'docType2', 'required', 'data-checkout' => 'docType']); !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('docNumber', 'Número do documento') !!}
            {!! Form::tel('docNumber', null, ['class' => 'form-control cpf_cnpj', 'placeholder' => 'Número do documento', 'required' ]); !!}
        </div>
    </div>

</div>
<div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-success pull-right" id="submit_button_boleto">Pagar com Boleto</button>
  </div>
</div>  
{!! Form::close() !!}
