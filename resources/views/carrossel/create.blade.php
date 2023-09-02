@extends('layouts.app')

@section('title', 'Adicionar Carrossel')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Adicionar Carrossel</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('CarrosselController@store'), 'method' => 'post', 'id' => 'carrossel_add_form', 'files' => true ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget')
      
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('titulo', 'Título' . ':') !!}
          {!! Form::text('titulo', null, ['class' => 'form-control', 'placeholder' => 'Título' ]); !!}
          @if($errors->has('titulo'))
          <span class="text-danger">
            {{ $errors->first('titulo') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('nome_botao', 'Nome botão' . ':') !!}
          {!! Form::text('nome_botao', null, ['class' => 'form-control', 'placeholder' => 'Nome botão' ]); !!}
          @if($errors->has('nome_botao'))
          <span class="text-danger">
            {{ $errors->first('nome_botao') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('link_acao', 'Link ação' . ':') !!}
          {!! Form::text('link_acao', null, ['class' => 'form-control', 'placeholder' => 'Link ação' ]); !!}
          @if($errors->has('link_acao'))
          <span class="text-danger">
            {{ $errors->first('link_acao') }}
          </span>
          @endif
        </div>
      </div>

      
      <div class="clearfix"></div>


      <div class="col-md-10">
        <div class="form-group">
          {!! Form::label('descricao', 'Descrição' . ':') !!}
          {!! Form::text('descricao', null, ['class' => 'form-control', 'placeholder' => 'Descrição' ]); !!}
          @if($errors->has('descricao'))
          <span class="text-danger">
            {{ $errors->first('descricao') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-4">

        <div class="form-group">
          {!! Form::label('image', 'Imagem' . ':*') !!}
          {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*']); !!}
          <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
          @if($errors->has('image'))
          <span class="text-danger">
            {{ $errors->first('image') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-3" style="visibility: hidden">
        <div class="form-group">
          {!! Form::label('cor_fundo', 'Cor de fundo' . '*:') !!}
          <input class="form-control" type="color" name="cor_fundo">

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
      <button type="submit" class="btn btn-primary pull-right" id="submit_user_button">@lang( 'messages.save' )</button>
    </div>
  </div>
  {!! Form::close() !!}
  @stop
  @section('javascript')
  <script type="text/javascript">
    $(document).ready(function(){

      var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
          image: { width: '100%', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
        },
      };
      $('#upload_image').fileinput(img_fileinput_setting);

    });

    
  </script>
  @endsection
