<div class="col-md-4">
  <div class="form-group">
    {!! Form::label('banco', 'Banco' . ':*') !!}
    {!! Form::select('banco', App\Models\Bank::bancos(), isset($item) ? $item->banco : '', 
    ['id' => 'banco', 'class' => 'form-control select2', 'required']); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('agencia', 'Agencia' . '*:') !!}
    {!! Form::text('agencia', isset($item) ? $item->agencia : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Agencia', 'data-mask="00000000"' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('conta', 'Conta' . '*:') !!}
    {!! Form::text('conta', isset($item) ? $item->conta : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Conta', 'data-mask="00000000"' ]); !!}
  </div>
</div>

<div class="col-md-4">
  <div class="form-group">
    {!! Form::label('titular', 'Titular' . '*:') !!}
    {!! Form::text('titular', isset($item) ? $item->titular : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Titular' ]); !!}
  </div>
</div>

<div class="col-md-3">
  <div class="form-group">
    {!! Form::label('cnpj', 'CPF/CNPJ' . '*:') !!}
    {!! Form::text('cnpj', isset($item) ? $item->cnpj : '', 
    ['class' => 'form-control cpf_cnpj', 'required', 'placeholder' => 'CPF/CNPJ' ]); !!}
  </div>
</div>

<div class="col-md-5">
  <div class="form-group">
    {!! Form::label('endereco', 'Endereço' . '*:') !!}
    {!! Form::text('endereco', isset($item) ? $item->endereco : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Endereço' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('cep', 'CEP' . '*:') !!}
    {!! Form::text('cep', isset($item) ? $item->cep : '', 
    ['class' => 'form-control cep', 'required', 'placeholder' => 'CEP' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('bairro', 'Bairro' . '*:') !!}
    {!! Form::text('bairro', isset($item) ? $item->bairro : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Bairro' ]); !!}
  </div>
</div>

<div class="col-md-5">
  <div class="form-group">
    {!! Form::label('cidade_id', 'Cidade:*') !!}
    {!! Form::select('cidade_id', ['' => 'Selecione a cidade'] + $cities, isset($item) ? $item->cidade_id : '', ['id' => 'cidade', 'class' => 'form-control select2 featured-field', 'required']); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">

    {!! Form::label('padrao', 'Padrão' . ':') !!}
    {!! Form::select('padrao', ['0' => 'Não', '1' => 'Sim'], isset($item) ? $item->padrao : '', ['id' => 'padrao', 'class' => 'form-control', 'required']); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('carteira', 'Carteira' . '*:') !!}
    {!! Form::text('carteira', isset($item) ? $item->carteira : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Carteira' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('convenio', 'Convênio' . '*:') !!}
    {!! Form::text('convenio', isset($item) ? $item->convenio : '', 
    ['class' => 'form-control', 'required', 'placeholder' => 'Convênio' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('juros', 'Juros' . '*:') !!}
    {!! Form::text('juros', isset($item) ? $item->juros : '', 
    ['class' => 'form-control money', 'required', 'placeholder' => 'Juros' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('multa', 'Multa' . '*:') !!}
    {!! Form::text('multa', isset($item) ? $item->multa : '', 
    ['class' => 'form-control money', 'required', 'placeholder' => 'Multa' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">
    {!! Form::label('juros_apos', 'Juros após (dias)' . '*:') !!}
    {!! Form::text('juros_apos', isset($item) ? $item->juros_apos : '', 
    ['class' => 'form-control money', 'required', 'placeholder' => 'Juros após (dias)' ]); !!}
  </div>
</div>

<div class="col-md-2">
  <div class="form-group">

    {!! Form::label('tipo', 'Tipo' . ':') !!}
    {!! Form::select('tipo', ['Cnab400' => 'Cnab400', 'Cnab240' => 'Cnab240'], isset($item) ? $item->tipo : '', ['id' => 'tipo', 'class' => 'form-control', 'required']); !!}
  </div>
</div>

