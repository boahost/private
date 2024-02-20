@extends('layouts.app')
@section('title', 'Criar Video YouTube')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Cadastrar video</h1>
</section>
<section class="content">
  {!! Form::open(['url' => action('\Modules\Superadmin\Http\Controllers\YoutubeVideoLessonController@store'), 'method' => 'post',
  'id' => 'youtube_video_lesson_add_form','class' => 'youtube_form']); !!}
  <div class="row">

      <div class="col-md-12">
          @component('components.widget', ['class' => 'box-primary'])

          <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('url_from_app', 'Url do Aplicação:*') !!}
                {!! Form::text('url_from_app', null, ['class' => 'form-control', 'required',
                'placeholder' => 'Url do Aplicação']); !!}
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('url_from_youtube', 'Url da YouTube:*') !!}
                {!! Form::text('url_from_youtube', null, ['class' => 'form-control', 'required',
                'placeholder' => 'Url da YouTube']); !!}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('icone', 'Icone:*') !!}
                <select class="form-control" required="" id="icone" name="icone">
                    <option value="fas fa-play"> Play</option>
                    <option value="fas fa-arrow-circle-down"> Seta para Baixo</option>
                    <option value="fas fa-arrow-circle-up"> Seta para Cima</option>
                    <option value="fas fa-shopping-cart"> Carrinho de Certificado</option>
                    <option value="fas fa-landmark"> Empresa</option>
                    <option value="fas fa-address-book"> Pessoas</option>
                    <option value="fas fa-hand-holding-usd"> Financeiro</option>
                    <option value="fa fas fa-cubes"> Produtos</option>
                    <option value="fa fas fa-users"> Usuário</option>
                    <option value="fa fas fa-fire"> Cozinha</option>
                    <
                </select>
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('page_name', 'Nome da Página:*') !!}
                {!! Form::text('page_name', null, ['class' => 'form-control', 'required',
                'placeholder' => 'Nome da Página']); !!}
            </div>
        </div>

        <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('label_button', 'Titulo do Botão:*') !!}
                {!! Form::text('label_button', null, ['class' => 'form-control', 'required',
                'placeholder' => 'Titulo do Botão']); !!}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('background_color', 'Cor do Botão:*') !!}
                <input class="form-control" value="" required="" type="color" placeholder="Cor do Botão" name="background_color" id="background_color">

            </div>
        </div>
        <div class="col-md-12">
            <button type="submit" value="submit" class="btn btn-primary submit_product_form pull-right">@lang('messages.save')</button>
        </div>
        @endcomponent
    </div>
</div>
{!! Form::close(); !!}

</section>

@endsection


