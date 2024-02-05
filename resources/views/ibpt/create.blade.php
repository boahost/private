@extends('layouts.app')
@section('title', 'IBPT')

@section('content')
<style type="text/css">
    .loader {
        border: 12px solid #F4F5FB; /* Light grey */
        border-top: 12px solid #1572E8; /* Blue */
        border-radius: 50%;
        width: 30px;
        height: 30px;
        float: right;
        animation: spin 2s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
  }
</style>


<!-- Content Header (Page header) -->
<section class="content-header">
    
</section>
@component('components.widget', ['class' => 'box-primary'])

{!! Form::open(['url' => action('IbptController@store'), 'method' => 'post', 'id' => 'ibpt_form', 'files' => true ]) !!}
    <h4>IBPT
        <small>@if(isset($ibpt)) Atualizar @else Inserir @endif Tabela {{(isset($ibpt) ? $ibpt->uf : '')}}</small>
    </h4>
<div class="row">
    <div class="col-sm-3 col-sm-offset-2">
        <div class="form-group">
            <label for="file">Tabela:</label>
            <input required name="file" accept=".csv" type="file" id="file">
            <p>Arquivo .csv</p>
        </div>
    </div>

    <input type="hidden" name="ibpt_id" value="@if(isset($ibpt)) {{$ibpt->id}} @else 0 @endif">
    @if(isset($estados))
    <div class="col-md-2">
        <div class="form-group">

            {!! Form::label('uf', 'UF' . ':') !!}
            {!! Form::select('uf', $estados, '', ['class' => 'form-control select2', 'required']); !!}
        </div>
    </div>
    @endif

    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('versao', 'Versão' . ':*') !!}
            {!! Form::text('versao', (isset($ibpt) ? $ibpt->versao : ''), ['class' => 'form-control', 'required', 
            'placeholder' => 'Versão']); !!}
        </div>
    </div>


    <div class="col-md-12">

        <div style="display: none" class="loader"></div>

        <button type="submit" class="btn btn-primary pull-right" id="submit_user_button">@if(isset($ibpt)) Editar @else Salvar @endif</button>
    </div>
</div>



{!! Form::close() !!}


@endcomponent
<!-- Main content -->
<section class="content">


</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">

    $( "#ibpt_form" ).submit(function( event ) {
      $('.loader').css('display', 'block')
  });
</script>
@endsection
