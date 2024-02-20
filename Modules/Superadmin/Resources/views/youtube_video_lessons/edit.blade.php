@extends('layouts.app')
@section('title', 'Editar Video YouTube')

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Editar video</h1>
</section>
<section class="content">
    {!! Form::open(['url' => action('\Modules\Superadmin\Http\Controllers\YoutubeVideoLessonController@update', $duplicate_youtube_video_lesson->id), 'method' => 'post',
    'id' => 'youtube_video_lesson_add_form','class' => 'product_form create', 'files' => true ]); !!}
    @method('PUT')
    <div class="row">

        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])

            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('url_from_app', 'Url do Aplicação:*') !!}
                    {!! Form::text('url_from_app', !empty($duplicate_youtube_video_lesson->url_from_app) ? $duplicate_youtube_video_lesson->url_from_app : null, ['class' => 'form-control', 'required',
                    'placeholder' => 'Url do Aplicação']); !!}
                </div>
            </div>
            <div class="col-sm-4">
                <div class="form-group">
                    {!! Form::label('url_youtube', 'Url da YouTube:*') !!}
                    {!! Form::text('url_from_youtube', !empty($duplicate_youtube_video_lesson->url_from_youtube) ? $duplicate_youtube_video_lesson->url_from_youtube : null, ['class' => 'form-control', 'required',
                    'placeholder' => 'Url da YouTube']); !!}
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    {!! Form::label('icone', 'Icone:*') !!}
                    <select class="form-control" required="" id="icone" name="icone">
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-play'? 'selected':''}} value="fas fa-play"> Play</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-arrow-circle-down'? 'selected':''}} value="fas fa-arrow-circle-down"> Seta para Baixo</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-arrow-circle-down'? 'selected':''}} value="fas fa-arrow-circle-up"> Seta para Cima</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-shopping-cart'? 'selected':''}} value="fas fa-shopping-cart"> Carrinho de Certificado</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-landmark'? 'selected':''}} value="fas fa-landmark"> Empresa</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-address-book'? 'selected':''}} value="fas fa-address-book"> Pessoas</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fas fa-hand-holding-usd'? 'selected':''}} value="fas fa-hand-holding-usd"> Financeiro</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fa fas fa-cubes'? 'selected':''}} value="fa fas fa-cubes"> Produtos</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fa fas fa-users'? 'selected':''}} value="fa fas fa-users"> Usuário</option>
                        <option {{$duplicate_youtube_video_lesson->icone=='fa fas fa-fire'? 'selected':''}} value="fa fas fa-fire"> Cozinha</option>
                        <
                    </select>

                </div>
            </div>

            <div class="col-sm-2">
                <div class="form-group">
                    {!! Form::label('page_name', 'Nome da Página:*') !!}
                    {!! Form::text('page_name', !empty($duplicate_youtube_video_lesson->page_name) ? $duplicate_youtube_video_lesson->page_name : null, ['class' => 'form-control', 'required',
                    'placeholder' => 'Nome da Página']); !!}
                </div>
            </div>

            <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('label_button', 'Titulo do Botão:*') !!}
                {!! Form::text('label_button', !empty($duplicate_youtube_video_lesson->label_button) ? $duplicate_youtube_video_lesson->label_button : null, ['class' => 'form-control', 'required',
                'placeholder' => 'Titulo do Botão']); !!}
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                {!! Form::label('background_color', 'Cor do Botão:*') !!}
                <input class="form-control" value="{{!empty($duplicate_youtube_video_lesson->background_color) ? $duplicate_youtube_video_lesson->background_color:''}}" required="" type="color" placeholder="Cor do Botão" name="background_color" id="background_color">

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

@section('javascript')
<script type="text/javascript">

</script>
@endsection
