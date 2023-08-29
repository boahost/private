@extends('layouts.app')

@section('title', 'Configuração de Ecommerce')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Configuração de Ecommerce</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('EcommerceController@save'), 'method' => 'post', 'id' => 'config_form', 'files' => true ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget', ['class' => 'box-primary'])
      
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('nome', 'Nome' . ':*') !!}
          {!! Form::text('nome', $config != null ? $config->nome : old('nome'), ['class' => 'form-control', 'placeholder' => 'Nome' ]); !!}
          @if($errors->has('nome'))
          <span class="text-danger">
            {{ $errors->first('nome') }}
          </span>
          @endif
          <span></span>
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('email', 'Email' . ':*') !!}
          {!! Form::text('email', $config != null ? $config->email : old('email'), ['class' => 'form-control', 'placeholder' => 'Email' ]); !!}
          @if($errors->has('email'))
          <span class="text-danger">
            {{ $errors->first('email') }}
          </span>
          @endif
        </div>
      </div>
      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('telefone', 'Telefone' . ':*') !!}
          {!! Form::text('telefone', $config != null ? $config->telefone : old('telefone'), ['class' => 'form-control', 'placeholder' => 'Telefone', 'data-mask="00 00000-0000"', 'data-mask-reverse="true"' ]); !!}
          @if($errors->has('telefone'))
          <span class="text-danger">
            {{ $errors->first('telefone') }}
          </span>
          @endif
        </div>
      </div>

      
      <div class="clearfix"></div>

      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('rua', 'Rua' . ':*') !!}
          {!! Form::text('rua', $config != null ? $config->rua : old('rua'), ['class' => 'form-control', 'placeholder' => 'Rua' ]); !!}
          @if($errors->has('rua'))
          <span class="text-danger">
            {{ $errors->first('rua') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('numero', 'Nº' . ':*') !!}
          {!! Form::text('numero', $config != null ? $config->numero : old('numero'), ['class' => 'form-control', 'placeholder' => 'Nº' ]); !!}
          @if($errors->has('numero'))
          <span class="text-danger">
            {{ $errors->first('numero') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('bairro', 'Bairro' . ':*') !!}
          {!! Form::text('bairro', $config != null ? $config->bairro : old('bairro'), ['class' => 'form-control', 'placeholder' => 'Bairro' ]); !!}
          @if($errors->has('bairro'))
          <span class="text-danger">
            {{ $errors->first('bairro') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('cidade', 'Cidade' . ':*') !!}
          {!! Form::text('cidade', $config != null ? $config->cidade : old('cidade'), ['class' => 'form-control', 'placeholder' => 'Cidade' ]); !!}
          @if($errors->has('cidade'))
          <span class="text-danger">
            {{ $errors->first('cidade') }}
          </span>
          @endif
        </div>
      </div>


      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cep', 'CEP' . ':*') !!}
          {!! Form::text('cep', $config != null ? $config->cep : old('cep'), ['class' => 'form-control', 'placeholder' => 'CEP', 'data-mask="00000-000"', 'data-mask-reverse="true"' ]); !!}
          @if($errors->has('cep'))
          <span class="text-danger">
            {{ $errors->first('cep') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('latitude', 'Latitude' . ':*') !!}
          {!! Form::text('latitude', $config != null ? $config->latitude : old('latitude'), ['class' => 'form-control', 'placeholder' => 'Latitude' ]); !!}
          @if($errors->has('latitude'))
          <span class="text-danger">
            {{ $errors->first('latitude') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('longitude', 'Longitude' . ':*') !!}
          {!! Form::text('longitude', $config != null ? $config->longitude : old('longitude'), ['class' => 'form-control', 'placeholder' => 'Longitude' ]); !!}
          @if($errors->has('longitude'))
          <span class="text-danger">
            {{ $errors->first('longitude') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('frete_gratis_valor', 'Frete gratis a partir de' . ':*') !!}
          {!! Form::text('frete_gratis_valor', $config != null ? $config->frete_gratis_valor : old('frete_gratis_valor'), ['class' => 'form-control', 'placeholder' => 'Frete gratis a partir de', 'data-mask="0000000,00"', 'data-mask-reverse="true"' ]); !!}
          @if($errors->has('frete_gratis_valor'))
          <span class="text-danger">
            {{ $errors->first('frete_gratis_valor') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('link_facebook', 'Link facebook' . ':*') !!}
          {!! Form::text('link_facebook', $config != null ? $config->link_facebook : old('link_facebook'), ['class' => 'form-control', 'placeholder' => 'Link facebook' ]); !!}
          @if($errors->has('link_facebook'))
          <span class="text-danger">
            {{ $errors->first('link_facebook') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('link_twiter', 'Link twiter' . ':*') !!}
          {!! Form::text('link_twiter', $config != null ? $config->link_twiter : old('link_twiter'), ['class' => 'form-control', 'placeholder' => 'Link twiter' ]); !!}
          @if($errors->has('link_twiter'))
          <span class="text-danger">
            {{ $errors->first('link_twiter') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('link_instagram', 'Link instagram' . ':*') !!}
          {!! Form::text('link_instagram', $config != null ? $config->link_instagram : old('link_instagram'), ['class' => 'form-control', 'placeholder' => 'Link instagram' ]); !!}
          @if($errors->has('link_instagram'))
          <span class="text-danger">
            {{ $errors->first('link_instagram') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('mercadopago_public_key', 'Mercado pago public key' . ':*') !!}
          {!! Form::text('mercadopago_public_key', $config != null ? $config->mercadopago_public_key : old('mercadopago_public_key'), ['class' => 'form-control', 'placeholder' => 'Mercado pago public key' ]); !!}
          @if($errors->has('mercadopago_public_key'))
          <span class="text-danger">
            {{ $errors->first('mercadopago_public_key') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-6">
        <div class="form-group">
          {!! Form::label('mercadopago_access_token', 'Mercado pago access token' . ':*') !!}
          {!! Form::text('mercadopago_access_token', $config != null ? $config->mercadopago_access_token : old('mercadopago_access_token'), ['class' => 'form-control', 'placeholder' => 'Mercado pago access token' ]); !!}
          @if($errors->has('mercadopago_access_token'))
          <span class="text-danger">
            {{ $errors->first('mercadopago_access_token') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>

      <div class="col-md-10">
        <div class="form-group">
          {!! Form::label('funcionamento', 'Descreva o funcionamento' . ':*') !!}
          {!! Form::text('funcionamento', $config != null ? $config->funcionamento : old('funcionamento'), ['class' => 'form-control', 'placeholder' => 'Descreva o funcionamento' ]); !!}
          @if($errors->has('funcionamento'))
          <span class="text-danger">
            {{ $errors->first('funcionamento') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>
      <div class="col-md-12">
        <div class="form-group">
          {!! Form::label('politica_privacidade', 'Politica de privacidade') !!}
          {!! Form::textarea('politica_privacidade', $config != null ? $config->politica_privacidade : old('politica_privacidade'), ['class' => 'form-control', 'rows' => 3, 'id' => 'politica_privacidade']); !!}
        </div>
      </div>

      <div class="col-sm-12">
        <div class="form-group">
          {!! Form::label('mensagem_agradecimento', 'Mensagem de agradecimento' . ':') !!}
          {!! Form::textarea('mensagem_agradecimento', $config != null ? $config->mensagem_agradecimento : old('mensagem_agradecimento'), ['class' => 'form-control', 'id' => 'mensagem_agradecimento']); !!}
        </div>
        @if($errors->has('mensagem_agradecimento'))
        <span class="text-danger">
          {{ $errors->first('mensagem_agradecimento') }}
        </span>
        @endif
      </div>

      <div class="clearfix"></div>

      <div class="col-md-5">
        <div class="form-group">
          {!! Form::label('token', 'Api token' . ':*') !!}
          <div class="input-group">

            {!! Form::text('token', $config != null ? $config->token : old('token'), ['class' => 'form-control', 'placeholder' => 'Api Token', 'id' => 'token', 'readonly' ]); !!}
            <span class="input-group-btn">
              <button type="button" id="btn_token" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-code text-danger fa-lg"></i></button>
            </span>
            
          </div>
          @if($errors->has('token'))
          <span class="text-danger">
            {{ $errors->first('token') }}
          </span>
          @endif
        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cor_fundo', 'Cor de destaque' . '*:') !!}
          <input class="form-control" value="{{$config != null ? $config->cor_fundo : old('cor_fundo')}}" type="color" name="cor_fundo">

        </div>
      </div>

      <div class="col-md-2">
        <div class="form-group">
          {!! Form::label('cor_btn', 'Cor Botão' . '*:') !!}
          <input class="form-control" value="{{$config != null ? $config->cor_btn : old('cor_btn')}}" type="color" name="cor_btn">

        </div>
      </div>

      <div class="col-md-3">
        <div class="form-group">
          {!! Form::label('timer_carrossel', 'Tempo carrossel segundos' . ':*') !!}
          {!! Form::text('timer_carrossel', $config != null ? $config->timer_carrossel : old('timer_carrossel'), ['class' => 'form-control', 'placeholder' => 'Tempo carrossel segundos', 'data-mask="000"', 'data-mask-reverse="true"' ]); !!}
          @if($errors->has('timer_carrossel'))
          <span class="text-danger">
            {{ $errors->first('timer_carrossel') }}
          </span>
          @endif
        </div>
      </div>

      <div class="clearfix"></div>
      <div class="col-sm-4">
        <div class="form-group">
          <label for="logo">Logo:</label>
          <input name="logo" type="file" id="logo" accept="image/*">
          <p class="help-block"><i>A logo anterior (se existir) será substituída</i></p>
        </div>
      </div>

      <div class="col-sm-4">
        <div class="form-group">
          <label for="img_contato">Imagem tela contato:</label>
          <input name="img_contato" type="file" id="img_contato" accept="image/*">
          <p class="help-block"><i>A imagem anterior (se existir) será substituída</i></p>
        </div>
      </div>

      <div class="col-sm-4">
        <div class="form-group">
          <label for="fav_icon">Favicon:</label>
          <input name="fav_icon" type="file" id="fav_icon" accept="image/*">
          <p class="help-block"><i>A imagem anterior (se existir) será substituída</i></p>
        </div>
      </div>

      <input type="hidden" value="@if(isset($config)){{$config->img_contato}}@else '' @endif" id="img_contato_aux">
      <input type="hidden" value="@if(isset($config)){{$config->logo}}@else '' @endif" id="logo_aux">
      <input type="hidden" value="@if(isset($config)){{$config->fav_icon}}@else '' @endif" id="fav_aux">

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

    $(document).on('click', '#submit_button', function(e) {
      e.preventDefault();

      $('form#config_form').validate()
      if ($('form#config_form').valid()) {
        $('form#config_form').submit();
      }
    })

    $('#btn_token').click(() => {
      let token = generate_token(25);

      swal({
        title: LANG.sure,
        text: "Esse token é o responsavel pela comunicação com o ecommerce, tenha atenção!!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
      }).then((confirmed) => {
        if (confirmed) {
          $('#token').val(token)
        }
      });

    })

    function generate_token(length){

      var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
      var b = [];  
      for (var i=0; i<length; i++) {
        var j = (Math.random() * (a.length-1)).toFixed(0);
        b[i] = a[j];
      }
      return b.join("");
    }

    setTimeout(() => {
      let img = $('#logo_aux').val();

      var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
          image: { width: '150px', height: '150px', 'max-width': '100%', 'max-height': '100%' },
        },
      };
      if(img){
        img_fileinput_setting.initialPreview = '/uploads/ecommerce_logos/'+img
        img_fileinput_setting.initialPreviewAsData = true

      }
      $('#logo').fileinput(img_fileinput_setting);

      let img2 = $('#img_contato_aux').val();
      var img_fileinput_setting2 = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,

        previewSettings: {
          image: { width: '150px', height: '150px', 'max-width': '100%', 'max-height': '100%' },
        },
      };

      if(img2){
        img_fileinput_setting2.initialPreview = '/uploads/ecommerce_contatos/'+img2
        img_fileinput_setting2.initialPreviewAsData = true

      }

      $('#img_contato').fileinput(img_fileinput_setting2);

      let img3 = $('#fav_aux').val();
      var img_fileinput_setting3 = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,

        previewSettings: {
          image: { width: '150px', height: '150px', 'max-width': '100%', 'max-height': '100%' },
        },
      };

      if(img3){
        img_fileinput_setting3.initialPreview = '/uploads/ecommerce_fav/'+img3
        img_fileinput_setting3.initialPreviewAsData = true

      }

      $('#fav_icon').fileinput(img_fileinput_setting3);
    }, 500);

    if ($('textarea#mensagem_agradecimento').length > 0) {
      tinymce.init({
        selector: 'textarea#mensagem_agradecimento',
        height:350
      });
    }

  </script>
  @endsection

