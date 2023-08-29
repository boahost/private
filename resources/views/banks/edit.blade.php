@extends('layouts.app')

@section('title', 'Editar Conta Bancária')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Editar Conta Bancária</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('BankController@update', [$item->id]), 'method' => 'put', 'id' => 'bank_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])
      @include('banks._forms')
      @endcomponent
    </div>

  </div>

  <div class="row">
    <div class="col-md-12">
      <button type="submit" class="btn btn-primary pull-right" id="submit_button">Atualizar</button>
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

    $('form#bank_form').validate()
    if ($('form#bank_form').valid()) {
      $('form#bank_form').submit();
    }
  })
</script>
@endsection

