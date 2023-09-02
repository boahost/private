@extends('layouts.app')

@section('title', 'Editar Natureza de Operação')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Editar Natureza de Operação</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('NaturezaController@update'), 'method' => 'post', 'id' => 'natureza_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])

      <input type="hidden" name="id" value="{{$natureza->id}}">
      
      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('natureza', 'Natureza' . ':*') !!}
          {!! Form::text('natureza', $natureza->natureza, ['class' => 'form-control', 'required', 'placeholder' => 'Natureza' ]); !!}
        </div>
      </div>

      <div class="col-md-3 customer_fields">
        <div class="form-group">

          {!! Form::label('sobrescreve_cfop', 'Sobrescrever CFOP do produto' . ':') !!}
          {!! Form::select('sobrescreve_cfop', ['0' => 'Não', '1' => 'Sim'], $natureza->sobrescreve_cfop, ['id' => 'sobrescreve_cfop', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>
      
      <div class="col-md-2 customer_fields">
        <div class="form-group">
          {!! Form::label('finNFe', 'Finalidade' . ':') !!}
          {!! Form::select('finNFe', App\Models\NaturezaOperacao::finalidades(), $natureza->finNFe, ['id' => 'finNFe', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2 customer_fields">
        <div class="form-group">

          {!! Form::label('tipo', 'Tipo' . ':') !!}
          {!! Form::select('tipo', ['1' => 'Saída', '0' => 'Entrada'], $natureza->tipo, ['id' => 'tipo', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>

      <div class="clearfix"></div>


      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cfop_entrada_estadual', 'CFOP entrada estadual' . '*:') !!}
          {!! Form::text('cfop_entrada_estadual', $natureza->cfop_entrada_estadual, ['class' => 'form-control', 'required', 'placeholder' => 'CFOP entrada estadual' ]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cfop_saida_estadual', 'CFOP saida estadual' . '*:') !!}
          {!! Form::text('cfop_saida_estadual', $natureza->cfop_saida_estadual, ['class' => 'form-control', 'required', 'placeholder' => 'CFOP saida estadual' ]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cfop_entrada_inter_estadual', 'CFOP entrada outro estado' . '*:') !!}
          {!! Form::text('cfop_entrada_inter_estadual', $natureza->cfop_entrada_inter_estadual, ['class' => 'form-control', 'required', 'placeholder' => 'CFOP entrada outro estado' ]); !!}
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cfop_saida_inter_estadual', 'CFOP saida outro estado' . '*:') !!}
          {!! Form::text('cfop_saida_inter_estadual', $natureza->cfop_saida_inter_estadual, ['class' => 'form-control', 'required', 'placeholder' => 'CFOP saida outro estado' ]); !!}
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
      <button type="submit" class="btn btn-primary pull-right" id="submit_button">@lang( 'messages.save' )</button>
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

      $('form#natureza_form').validate()
      if ($('form#natureza_form').valid()) {
        $('form#natureza_form').submit();
      }
    })
  </script>
  @endsection
