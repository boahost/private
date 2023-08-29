@extends('layouts.app')

@section('title', 'Editar Veiculo')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Editar </h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('VeiculoController@update'), 'method' => 'post', 'id' => 'veiculo_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget')

      <input type="hidden" name="id" value="{{$veiculo->id}}">

      
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('placa', 'Placa' . ':*') !!}
          {!! Form::text('placa', $veiculo->placa, ['class' => 'form-control', 'required', 'placeholder' => 'Placa', 'data-mask="AAA-AAAA"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('uf', 'UF' . ':*') !!}
          {!! Form::select('uf', $ufs, $veiculo->uf, ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('modelo', 'Modelo' . ':*') !!}
          {!! Form::text('modelo', $veiculo->modelo, ['class' => 'form-control', 'required', 'placeholder' => 'Modelo' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('marca', 'Marca' . ':*') !!}
          {!! Form::text('marca', $veiculo->marca, ['class' => 'form-control', 'required', 'placeholder' => 'Marca' ]); !!}
        </div>
      </div>
      
      <div class="clearfix"></div>


      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cor', 'Cor' . ':*') !!}
          {!! Form::text('cor', $veiculo->cor, ['class' => 'form-control', 'required', 'placeholder' => 'Cor' ]); !!}
        </div>
      </div>
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('tipo_carroceira', 'Tipo da carroceria' . ':*') !!}
          {!! Form::select('tipo_carroceira', $tiposCarroceria, $veiculo->tipo_carroceira, ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('tipo_rodado', 'Tipo de rodado' . ':*') !!}
          {!! Form::select('tipo_rodado', $tiposRodado, $veiculo->tipo_rodado, ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('tara', 'Tara' . ':*') !!}
          {!! Form::text('tara', $veiculo->tara, ['class' => 'form-control', 'required', 'placeholder' => 'Tara', 'data-mask="0000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('capacidade', 'Capacidade' . ':*') !!}
          {!! Form::text('capacidade', $veiculo->capacidade, ['class' => 'form-control', 'required', 'placeholder' => 'Capacidade', 'data-mask="0000000"' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('proprietario_nome', 'Nome Proprietário' . ':*') !!}
          {!! Form::text('proprietario_nome', $veiculo->proprietario_nome, ['class' => 'form-control', 'required', 'placeholder' => 'Nome Proprietário' ]); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('proprietario_documento', 'Documento Proprietário' . ':*') !!}
          {!! Form::text('proprietario_documento', $veiculo->proprietario_documento, ['class' => 'form-control cpf_cnpj', 'required', 'placeholder' => 'Documento Proprietário' ]); !!}
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('proprietario_ie', 'I.E Proprietário' . ':') !!}
          {!! Form::text('proprietario_ie', $veiculo->proprietario_ie, ['class' => 'form-control', 'required', 'placeholder' => 'I.E Proprietário' ]); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('proprietario_uf', 'Proprietário UF' . ':*') !!}
          {!! Form::select('proprietario_uf', $ufs, $veiculo->proprietario_uf, ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('proprietario_tp', 'Tipo de Proprietário' . ':*') !!}
          {!! Form::select('proprietario_tp', $tiposProprietario, $veiculo->proprietario_tp, ['class' => 'form-control select2', 'id' => 'contact_type', 'required']); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('rntrc', 'RNTRC' . ':') !!}
          {!! Form::text('rntrc', $veiculo->rntrc, ['class' => 'form-control', 'required, minlength:8', 'placeholder' => 'RNTRC',
          'required' ]); !!}
        </div>
      </div>

      
      @endcomponent
    </div>


  </div>

  @if(!empty($form_partials))
  @foreach($form_partials as $partial)
  {!! $partial !!}
  @endforeach
  @endif
  <div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary pull-right" id="submit_button">Atualizar</button>
    </div>
  </div>
  {!! Form::close() !!}
  @stop
  @section('javascript')
  <script type="text/javascript">
    $(document).ready(function(){
    });
    $(document).on('click', '#submit_button', function(e) {
      e.preventDefault();

      $('form#veiculo_form').validate()
      if ($('form#veiculo_form').valid()) {
        $('form#veiculo_form').submit();
      }
    })
  </script>
  @endsection
