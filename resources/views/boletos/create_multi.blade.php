@extends('layouts.app')

@section('title', 'Gerar boleto')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Gerar boletos</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('BoletoController@storeMulti'), 'method' => 'post', 'id' => 'boleto_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])
      
      <div class="clearfix"></div>
      <hr>

      <p style="margin-left: 10px;" class="text-danger col-12"><i class="glyphicon glyphicon-info-sign text-danger"></i> Após gerar o boleto não será possível editar os dados da conta a receber.</p>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('banco', 'Banco' . ':*') !!}
          {!! Form::select('banco', ['' => 'Selecione uma conta bancária'] + $banks->pluck('info', 'id')->all(), '', 
          ['id' => 'banco', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('carteira', 'Carteira' . '*:') !!}
          {!! Form::text('carteira', $padrao ? $padrao->carteira : '', 
          ['class' => 'form-control', 'required', 'placeholder' => 'Carteira' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('tipo', 'Tipo' . ':') !!}
          {!! Form::select('tipo', ['Cnab400' => 'Cnab400', 'Cnab240' => 'Cnab240'], $padrao ? $padrao->tipo : '', ['id' => 'tipo', 'class' => 'form-control', 'required']); !!}
        </div>
      </div>
      
      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('logo', 'Usar logo' . ':') !!}
          {!! Form::select('logo', ['0' => 'Não', '1' => 'Sim'], '', ['id' => 'logo', 'class' => 'form-control', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('convenio', 'Convênio' . '*:') !!}
          {!! Form::text('convenio', $padrao ? $padrao->convenio : '', 
          ['class' => 'form-control', 'required', 'placeholder' => 'Convênio', 'minlength' => '4']); !!}

          @if($errors->has('convenio'))
          <span class="text-danger">
            {{ $errors->first('convenio') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>
      <hr>

      @foreach($contas as $key => $c)
      @component('components.widget', ['class' => 'box-danger'])

      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('info', 'Cliente' . '*:') !!}
          {!! Form::text('info', $c->contact->name . " | CPF/CNPJ: " . $c->contact->cpf_cnpj, 
          ['class' => 'form-control', 'required', 'readonly']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('valor', 'Valor' . '*:') !!}
          {!! Form::text('valor', number_format($c->valor_total, 2, ',', '.'), 
          ['class' => 'form-control', 'required', 'readonly']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('vencimento', 'Vencimento' . '*:') !!}
          {!! Form::text('vencimento', \Carbon\Carbon::parse($c->vencimento)->format('d/m/Y'), 
          ['class' => 'form-control', 'required', 'readonly']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero', 'Nº do boleto' . '*:') !!}
          {!! Form::text("payment[$key][numero]", old('numero'), 
          ['class' => 'form-control', 'required', 'placeholder' => 'Nº do boleto', 'data-mask="00000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero_documento', 'Nº do documento' . '*:') !!}
          {!! Form::text("payment[$key][numero_documento]", old('numero_documento'), 
          ['class' => 'form-control', 'required', 'placeholder' => 'Nº do documento', 'data-mask="00000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('juros', 'Juros' . '*:') !!}
          {!! Form::text("payment[$key][juros]", $padrao ? $padrao->juros : '', 
          ['class' => 'form-control money juros', 'required', 'placeholder' => 'Juros' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('multa', 'Multa' . '*:') !!}
          {!! Form::text("payment[$key][multa]", $padrao ? $padrao->multa : '', 
          ['class' => 'form-control money multa', 'required', 'placeholder' => 'Multa' ]); !!}
        </div>
      </div>


      {!! Form::text("payment[$key][id]", $c->id, 
      ['class' => '', 'required', 'placeholder' => 'Multa', 'style' => 'display: none' ]); !!}


      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('juros_apos', 'Juros após (dias)' . '*:') !!}
          {!! Form::text("payment[$key][juros_apos]", $padrao ? $padrao->juros_apos : '', 
          ['class' => 'form-control money juros_apos', 'required', 'placeholder' => 'Juros após (dias)' ]); !!}
        </div>
      </div>

      @endcomponent
      @endforeach
      @endcomponent
    </div>

  </div>

  <div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary pull-right" id="submit_button">Gerar Boletos</button>
    </div>
  </div>
  {!! Form::close() !!}
  @stop 
</section>

@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
  });
  $(document).on('click', '#submit_button', function(e) {
    e.preventDefault();

    $('form#boleto_form').validate()
    if ($('form#boleto_form').valid()) {
      $('form#boleto_form').submit();
    }
  })

  $('#banco').change(() => {
    var path = window.location.protocol + '//' + window.location.host

    let banco = $('#banco').val()
    $.get(path + '/api/bank/'+banco)
    .done((res) => {
      console.log(res)
      $('#carteira').val(res.carteira)
      $('#convenio').val(res.convenio)
      $('.multa').val(res.multa)
      $('.juros_apos').val(res.juros_apos)
      $('.juros').val(res.juros)
      $('#tipo').val(res.tipo).change()
    })
    .fail((err) => {
      console.log(err)
    })
  })
</script>
@endsection

