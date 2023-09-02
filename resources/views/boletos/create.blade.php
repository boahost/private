@extends('layouts.app')

@section('title', 'Gerar boleto')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Gerar boleto | Conta a receber <strong>{{$revenue->id}}</strong></h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('BoletoController@store'), 'method' => 'post', 'id' => 'boleto_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])
      <div class="col-md-6">
        <h4>Cliente: <strong>{{$revenue->contact->name}} - {{$revenue->contact->contact_id}}</strong></h4>
        <h4>Documento: <strong>{{$revenue->contact->cpf_cnpj}}</strong></h4>
        <h4>IE/RG: <strong>{{$revenue->contact->ie_rg}}</strong></h4>
        <h4>Email: <strong>{{$revenue->contact->email}}</strong></h4>
      </div>
      <div class="col-md-6">
        <h4>Rua: <strong>{{$revenue->contact->rua}}</strong></h4>
        <h4>Número: <strong>{{$revenue->contact->numero}}</strong></h4>
        <h4>Cidade: <strong>{{$revenue->contact->cidade->nome}} ({{$revenue->contact->cidade->uf}})</strong></h4>
        <h4>Bairro: <strong>{{$revenue->contact->bairro}}</strong></h4>
      </div>
      <div class="clearfix"></div>
      <hr>

      <p style="margin-left: 10px;" class="text-danger col-12"><i class="glyphicon glyphicon-info-sign text-danger"></i> Após gerar o boleto não será possível editar os dados da conta a receber.</p>

      <input type="hidden" value="{{$revenue->id}}" name="revenue_id">
      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('valor', 'Valor' . '*:') !!}
          {!! Form::text('valor', number_format($revenue->valor_total, 2, ',', '.'), 
          ['class' => 'form-control', 'required', 'readonly']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('vencimento', 'Vencimento' . '*:') !!}
          {!! Form::text('vencimento', \Carbon\Carbon::parse($revenue->vencimento)->format('d/m/Y'), 
          ['class' => 'form-control', 'required', 'readonly']); !!}
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('banco', 'Banco' . ':*') !!}
          {!! Form::select('banco', ['' => 'Selecione uma conta bancária'] + $banks->pluck('info', 'id')->all(), $padrao ? $padrao->id : '', 
          ['id' => 'banco', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero', 'Nº do boleto' . '*:') !!}
          {!! Form::text('numero', old('numero'), 
          ['class' => 'form-control', 'required', 'placeholder' => 'Nº do boleto', 'data-mask="00000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero_documento', 'Nº do documento' . '*:') !!}
          {!! Form::text('numero_documento', old('numero_documento'), 
          ['class' => 'form-control', 'required', 'placeholder' => 'Nº do documento', 'data-mask="00000000"' ]); !!}
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

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('juros', 'Juros' . '*:') !!}
          {!! Form::text('juros', $padrao ? $padrao->juros : '', 
          ['class' => 'form-control money', 'required', 'placeholder' => 'Juros' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('multa', 'Multa' . '*:') !!}
          {!! Form::text('multa', $padrao ? $padrao->multa : '', 
          ['class' => 'form-control money', 'required', 'placeholder' => 'Multa' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('juros_apos', 'Juros após (dias)' . '*:') !!}
          {!! Form::text('juros_apos', $padrao ? $padrao->juros_apos : '', 
          ['class' => 'form-control money', 'required', 'placeholder' => 'Juros após (dias)' ]); !!}
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

      <div class="col-md-2 div-aux" style="display: none">
        <div class="form-group">
          {!! Form::label('posto', 'Posto' . '*:') !!}
          {!! Form::text('posto', $padrao ? $padrao->posto : '', 
          ['class' => 'form-control', 'placeholder' => 'Posto' ]); !!}
        </div>
      </div>

      <div class="col-md-2 div-aux" style="display: none">
        <div class="form-group">
          {!! Form::label('codigo_cliente', 'Cód. Cliente' . '*:') !!}
          {!! Form::text('codigo_cliente', $padrao ? $padrao->codigo_cliente : '', 
          ['class' => 'form-control', 'placeholder' => 'Cód. Cliente' ]); !!}
        </div>
      </div>

      @endcomponent
    </div>

  </div>

  <div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary pull-right" id="submit_button">Gerar Boleto</button>
    </div>
  </div>
  {!! Form::close() !!}
  @stop 
</section>

@section('javascript')
<script type="text/javascript">
  $(document).ready(function(){
    verificaBanco(null)
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
    if(banco){
      $.get(path + '/api/bank/'+banco)
      .done((res) => {
        $('#carteira').val(res.carteira)
        $('#convenio').val(res.convenio)
        $('#multa').val(res.multa)
        $('#juros_apos').val(res.juros_apos)
        $('#juros').val(res.juros)
        $('#tipo').val(res.tipo).change()

        verificaBanco(res)
      })
      .fail((err) => {
        console.log(err)
      })
    }
  })

  function verificaBanco(res){
    $('#posto').val('')
    $('#codigo_cliente').val('')
    $('.div-aux').css('display', 'none')
    if(res){
      console.log(res)
      if(res.banco == "748" || res.banco == "104" || res.banco == "033")
        $('.div-aux').css('display', 'block')

    }
  }
</script>
@endsection

