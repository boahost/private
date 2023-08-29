@extends('layouts.app')

@section('title', 'Endereço')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Editar Endereço Ecommerce</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('EnderecoEcommerceController@update'), 'method' => 'post', 'id' => 'natureza_add_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget')
      <input type="hidden" name="id" value="{{$endereco->id}}">
      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('rua', 'Rua' . ':*') !!}
          {!! Form::text('rua', $endereco->rua, ['class' => 'form-control', 'placeholder' => 'Rua' ]); !!}
          @if($errors->has('rua'))
          <span class="text-danger">
            {{ $errors->first('rua') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero', 'Número' . ':*') !!}
          {!! Form::text('numero', $endereco->numero, ['class' => 'form-control', 'placeholder' => 'Número' ]); !!}
          @if($errors->has('numero'))
          <span class="text-danger">
            {{ $errors->first('numero') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('bairro', 'Bairro' . ':*') !!}
          {!! Form::text('bairro', $endereco->bairro, ['class' => 'form-control', 'placeholder' => 'Bairro' ]); !!}
          @if($errors->has('bairro'))
          <span class="text-danger">
            {{ $errors->first('bairro') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-4 customer_fields">
        <div class="form-group">
          {!! Form::label('city_id', 'Cidade:*') !!}
          {!! Form::select('city_id', $cities, $cidade->id, ['id' => 'cidade', 'class' => 'form-control select2', 'required']); !!}
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cep', 'CEP' . ':*', ['id' => 'lbl_doc']) !!}
          {!! Form::text('cep', $endereco->cep, ['class' => 'form-control', 'placeholder' => 'CEP', 'data-mask="00000-000"', 'data-mask-reverse="true"', 'id' => 'doc' ]); !!}
          @if($errors->has('cep'))
          <span class="text-danger">
            {{ $errors->first('cep') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('complemento', 'Complemento' . ':*') !!}
          {!! Form::text('complemento', $endereco->complemento, ['class' => 'form-control', 'placeholder' => 'Complemento' ]); !!}
          @if($errors->has('complemento'))
          <span class="text-danger">
            {{ $errors->first('complemento') }}
          </span>
          @endif
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
      <button type="submit" class="btn btn-primary pull-right" id="submit_user_button">
        Atualizar
      </button>
    </div>
  </div>
  {!! Form::close() !!}
  @stop
  
