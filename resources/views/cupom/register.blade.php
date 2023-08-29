@extends('layouts.app')

@section('title', 'Novo Cupom')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Novo Cupom</h1>
</section>

<!-- Main content -->
<section class="content">
  {!! Form::open(['url' => action('CupomController@save'), 'method' => 'post', 'id' => 'natureza_add_form' ]) !!}
  <div class="row">
    <div class="col-md-12">
      @component('components.widget')

      
      <div class="col-md-4">
        <div class="form-group">
          {!! Form::label('codigo', 'Código' . ':*') !!}
          <div class="input-group">

            {!! Form::text('codigo', '', ['class' => 'form-control', 'required', 'placeholder' => 'Código', 'data-mask="AAAAAA"', 'data-mask-reverse="true"' ]); !!}
            <span class="input-group-btn">
              <button type="button" id="btn_codigo" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-code text-danger fa-lg"></i></button>
            </span>
          </div>
        </div>
      </div>

      <div class="col-sm-2">
        <div class="form-group">
          {!! Form::label('valor',  'Valor' . ':') !!}
          {!! Form::text('valor', null, ['class' => 'form-control', 'placeholder' => 'Valor', 'required',  'data-mask="000000,00"', 'data-mask-reverse="true"']); !!}
        </div>
      </div>

      <div class="col-sm-2">
        <div class="form-group">
          {!! Form::label('tipo', 'Tipo de desconto' . ':*') !!}
          {!! Form::select('tipo', ['percentual' => 'Percentual', 'valor' => 'Valor'], null, ['class' => 'form-control']); !!}
        </div>
      </div>

      <div class="col-sm-2">
        <div class="form-group">
          <br>
          <label style="margin-top: 5px;">
            {!! Form::checkbox('status', 1, 1, ['class' => 'input-icheck', 'id' => 'status']); !!} <strong>Ativo</strong>
          </label>
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
      $('#selected_contacts').on('ifChecked', function(event){
        $('div.selected_contacts_div').removeClass('hide');
      });
      $('#selected_contacts').on('ifUnchecked', function(event){
        $('div.selected_contacts_div').addClass('hide');
      });

      $('#allow_login').on('ifChecked', function(event){
        $('div.user_auth_fields').removeClass('hide');
      });
      $('#allow_login').on('ifUnchecked', function(event){
        $('div.user_auth_fields').addClass('hide');
      });
    });

    $('form#user_add_form').validate({
      rules: {
        first_name: {
          required: true,
        },
        email: {
          email: true,
          remote: {
            url: "/business/register/check-email",
            type: "post",
            data: {
              email: function() {
                return $( "#email" ).val();
              }
            }
          }
        },
        password: {
          required: true,
          minlength: 5
        },
        confirm_password: {
          equalTo: "#password"
        },
        username: {
          minlength: 5,
          remote: {
            url: "/business/register/check-username",
            type: "post",
            data: {
              username: function() {
                return $( "#username" ).val();
              },
              @if(!empty($username_ext))
              username_ext: "{{$username_ext}}"
              @endif
            }
          }
        }
      },
      messages: {
        password: {
          minlength: 'Password should be minimum 5 characters',
        },
        confirm_password: {
          equalTo: 'Should be same as password'
        },
        username: {
          remote: 'Invalid username or User already exist'
        },
        email: {
          remote: '{{ __("validation.unique", ["attribute" => __("business.email")]) }}'
        }
      }
    });
    $('#username').change( function(){
      if($('#show_username').length > 0){
        if($(this).val().trim() != ''){
          $('#show_username').html("{{__('lang_v1.your_username_will_be')}}: <b>" + $(this).val() + "{{$username_ext}}</b>");
        } else {
          $('#show_username').html('');
        }
      }
    });

    $('#btn_codigo').click(() => {
      let token = generate_token(6);

      $('#codigo').val(token)
    })

    function generate_token(length){

      var a = "abcdefghijklmnopqrstuvwxyz1234567890".split("");
      var b = [];  
      for (var i=0; i<length; i++) {
        var j = (Math.random() * (a.length-1)).toFixed(0);
        b[i] = a[j];
      }
      return b.join("");
    }
  </script>
  @endsection
