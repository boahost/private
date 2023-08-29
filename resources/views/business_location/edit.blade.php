<div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('BusinessLocationController@update', [$location->id]), 'method' => 'PUT', 'id' => 'business_location_add_form', 'files' => true ]) !!}

    {!! Form::hidden('hidden_id', $location->id, ['id' => 'hidden_id']); !!}
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'business.edit_business_location' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('cnpj', 'CNPJ' . ':*') !!}
            {!! Form::text('cnpj', $location->cnpj, ['class' => 'form-control cpf_cnpj', 'required',
            'placeholder' => 'CNPJ']); !!}
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            {!! Form::label('tipo', 'UF' . ':') !!}
            <div class="input-group" style="width: 100%;">
              <span class="input-group-addon">
                <a onclick="buscaDados()"><i class="fa fa-search"></i></a>
              </span>
              {!! Form::select('uf', $estados, '', ['id' => 'uf2', 'class' => 'form-control select2 featured-field']); !!}
            </div>
          </div>
        </div>
       
        <div class="col-sm-7">
          <div class="form-group">
            {!! Form::label('name', 'Nome Fantasia' . ':*') !!}
            {!! Form::text('name', $location->name, ['class' => 'form-control', 'required', 'placeholder' => 'Nome' ]); !!}
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('razao_social',__('business.business_razao') . ':*') !!}
            {!! Form::text('razao_social', $location->razao_social, ['class' => 'form-control', 'required',
            'placeholder' => __('business.business_razao')]); !!}
            @if($errors->has('razao_social'))
            <span class="text-danger">{{ $errors->first('razao_social') }}</span>
            @endif

          </div>
        </div>

        <div class="clearfix"></div>
        <div class="col-sm-2">
          <div class="form-group">
            {!! Form::label('location_id', __( 'lang_v1.location_id' ) . ':') !!}
            {!! Form::text('location_id', $location->location_id, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.location_id' ) ]); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('landmark', __( 'business.landmark' ) . ':') !!}
            {!! Form::text('landmark', $location->landmark, ['class' => 'form-control', 'placeholder' => __( 'business.landmark' ) ]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('zip_code', __( 'business.zip_code' ) . ':*') !!}
            {!! Form::text('zip_code', $location->cep, ['class' => 'form-control', 'placeholder' => __( 'business.zip_code'), 'required', 'data-mask="00000-000"' ]); !!}
          </div>
        </div>


        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('cidade_id', 'Cidade:*') !!}<br>
            {!! Form::select('cidade_id', $cities, $location->cidade ? $location->cidade->id : null, ['class' => 'form-control select2', 'required', 'style' => 'width: 100%']); !!}
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('ie', 'IE' . ':*') !!}
            {!! Form::text('ie', $location->ie, ['class' => 'form-control', 'required',
            'placeholder' => 'IE']); !!}
          </div>
        </div>

        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('rua', 'Rua' . ':*') !!}
            {!! Form::text('rua', $location->rua, ['class' => 'form-control', 'required',
            'placeholder' => 'Rua']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('numero', 'Número' . ':*') !!}
            {!! Form::text('numero', $location->numero, ['class' => 'form-control', 'required',
            'placeholder' => 'Número']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('bairro', 'Bairro' . ':*') !!}
            {!! Form::text('bairro', $location->bairro, ['class' => 'form-control', 'required',
            'placeholder' => 'Bairro']); !!}
          </div>
        </div>


        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('telefone', 'Telefone' . ':*') !!}
            {!! Form::text('telefone', $location->telefone, ['class' => 'form-control', 'required', 'data-mask="00 000000000"',
            'placeholder' => 'Telefone']); !!}
          </div>
        </div>

        <div class="col-md-2">
          <div class="form-group">

            {!! Form::label('regime', 'Regime' . ':') !!}
            {!! Form::select('regime', ['1' => 'Simples', '3' => 'Normal'], $location->regime, ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('ultimo_numero_nfe', 'Ultimo Núm. NFe' . ':*') !!}
            {!! Form::text('ultimo_numero_nfe', $location->ultimo_numero_nfe, ['class' => 'form-control', 'required',
            'placeholder' => 'Ultimo Núm. NFe']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('ultimo_numero_nfce', 'Ultimo Núm. NFCe' . ':*') !!}
            {!! Form::text('ultimo_numero_nfce', $location->ultimo_numero_nfce, ['class' => 'form-control', 'required',
            'placeholder' => 'Ultimo Núm. NFCe']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('ultimo_numero_cte', 'Ultimo Núm. CTe' . ':*') !!}
            {!! Form::text('ultimo_numero_cte', $location->ultimo_numero_cte, ['class' => 'form-control', 'required',
            'placeholder' => 'Ultimo Núm. CTe']); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('ultimo_numero_mdfe', 'Ultimo Núm. MDFe' . ':*') !!}
            {!! Form::text('ultimo_numero_mdfe', $location->ultimo_numero_mdfe, ['class' => 'form-control', 'required',
            'placeholder' => 'Ultimo Núm. MDFe']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('inscricao_municipal', 'Inscrição municipal' . ':*') !!}
            {!! Form::text('inscricao_municipal', $location->inscricao_municipal, ['class' => 'form-control', 'required',
            'placeholder' => 'Inscrição municipal']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('numero_serie_nfe', 'Núm. Série NFe' . ':*') !!}
            {!! Form::text('numero_serie_nfe', $location->numero_serie_nfe, ['class' => 'form-control', 'required',
            'placeholder' => 'Núm. Série NFe']); !!}
          </div>
        </div>


        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('numero_serie_nfce', 'Núm. Série NFCe' . ':*') !!}
            {!! Form::text('numero_serie_nfce', $location->numero_serie_nfce, ['class' => 'form-control', 'required',
            'placeholder' => 'Núm. Série NFCe']); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">

            {!! Form::label('ambiente', 'Ambiente' . ':') !!}
            {!! Form::select('ambiente', ['1' => 'Produção', '2' => 'Homologação'], $location->ambiente, ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('csc_id', 'CSCID' . ':*') !!}
            {!! Form::text('csc_id', $location->csc_id, ['class' => 'form-control', 'required', 
            'placeholder' => 'CSCID']); !!}
          </div>
        </div>

        <div class="col-sm-5">
          <div class="form-group">
            {!! Form::label('csc', 'CSC' . ':*') !!}
            {!! Form::text('csc', $location->csc, ['class' => 'form-control', 'required', 
            'placeholder' => 'CSC']); !!}
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('aut_xml', 'AUT XML' . ':*') !!}
            {!! Form::text('aut_xml', $location->aut_xml, ['class' => 'form-control cnpj', 
            'placeholder' => 'AUT XML', 'data-mask="00.000.000/0000-00"', 'data-mask-reverse="true"']); !!}
          </div>
        </div>


        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('mobile', __( 'business.mobile' ) . ':') !!}
            {!! Form::text('mobile', $location->mobile, ['class' => 'form-control', 'placeholder' => __( 'business.mobile')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('alternate_number', __( 'business.alternate_number' ) . ':') !!}
            {!! Form::text('alternate_number', $location->alternate_number, ['class' => 'form-control', 'placeholder' => __( 'business.alternate_number')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('email', __( 'business.email' ) . ':') !!}
            {!! Form::email('email', $location->email, ['class' => 'form-control', 'placeholder' => __( 'business.email')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('website', __( 'lang_v1.website' ) . ':') !!}
            {!! Form::text('website', $location->website, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.website')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':*') !!} @show_tooltip(__('tooltip.invoice_scheme'))
            {!! Form::select('invoice_scheme_id', $invoice_schemes, $location->invoice_scheme_id, ['class' => 'form-control', 'required',
            'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('invoice_layout_id', __('invoice.invoice_layout') . ':*') !!} @show_tooltip(__('tooltip.invoice_layout'))
            {!! Form::select('invoice_layout_id', $invoice_layouts,  $location->invoice_layout_id, ['class' => 'form-control', 'required',
            'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('selling_price_group_id', 'Grupo de preço de venda padrão' . ':') !!} @show_tooltip(__('lang_v1.location_price_group_help'))
            {!! Form::select('selling_price_group_id', $price_groups, $location->selling_price_group_id, ['class' => 'form-control',
            'placeholder' => __('messages.please_select')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        @php
        $custom_labels = json_decode(session('business.custom_labels'), true);
        $location_custom_field1 = !empty($custom_labels['location']['custom_field_1']) ? $custom_labels['location']['custom_field_1'] : __('lang_v1.location_custom_field1');
        $location_custom_field2 = !empty($custom_labels['location']['custom_field_2']) ? $custom_labels['location']['custom_field_2'] : __('lang_v1.location_custom_field2');
        $location_custom_field3 = !empty($custom_labels['location']['custom_field_3']) ? $custom_labels['location']['custom_field_3'] : __('lang_v1.location_custom_field3');
        $location_custom_field4 = !empty($custom_labels['location']['custom_field_4']) ? $custom_labels['location']['custom_field_4'] : __('lang_v1.location_custom_field4');
        @endphp
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('custom_field1', $location_custom_field1 . ':') !!}
            {!! Form::text('custom_field1', $location->custom_field1, ['class' => 'form-control', 
            'placeholder' => $location_custom_field1]); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('custom_field2', $location_custom_field2 . ':') !!}
            {!! Form::text('custom_field2', $location->custom_field2, ['class' => 'form-control', 
            'placeholder' => $location_custom_field2]); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('custom_field3', $location_custom_field3 . ':') !!}
            {!! Form::text('custom_field3', $location->custom_field3, ['class' => 'form-control', 
            'placeholder' => $location_custom_field3]); !!}
          </div>
        </div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('custom_field4', $location_custom_field4 . ':') !!}
            {!! Form::text('custom_field4', $location->custom_field4, ['class' => 'form-control', 
            'placeholder' => $location_custom_field4]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <hr>
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::label('featured_products', __('lang_v1.pos_screen_featured_products') . ':') !!} @show_tooltip(__('lang_v1.featured_products_help'))
            {!! Form::select('featured_products[]', $featured_products, $location->featured_products, ['class' => 'form-control',
            'id' => 'featured_products', 'multiple']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <hr>
        <div class="col-sm-12">
          <strong>Formas de pagamento: @show_tooltip('habilite as formas de pagamento')</strong>
          <div class="form-group">
            <table class="table table-condensed table-striped">
              <thead>
                <tr>
                  <th class="text-center">@lang('lang_v1.payment_method')</th>
                  <th class="text-center">Ativo</th>
                  <th class="text-center @if(empty($accounts)) hide @endif">@lang('lang_v1.default_accounts') @show_tooltip(__('lang_v1.default_account_help'))</th>
                </tr>
              </thead>
              <tbody>
                @php
                $default_payment_accounts = !empty($location->default_payment_accounts) ?
                json_decode($location->default_payment_accounts, true) : [];
                @endphp
                @foreach($payment_types as $key => $value)
                <tr>
                  <td class="text-center">{{$value}}</td>
                  <td class="text-center">{!! Form::checkbox('default_payment_accounts[' . $key . '][is_enabled]', 1, !empty($default_payment_accounts[$key]['is_enabled'])); !!}</td>
                  <td class="text-center @if(empty($accounts)) hide @endif">
                    {!! Form::select('default_payment_accounts[' . $key . '][account]', $accounts, !empty($default_payment_accounts[$key]['account']) ? $default_payment_accounts[$key]['account'] : null, ['class' => 'form-control input-sm']); !!}
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

    <script type="text/javascript">
      $(document).ready(function() {
        $('#cidade_id').select2();

        var cpfMascara = function(val) {
          return val.replace(/\D/g, "").length > 11
          ? "00.000.000/0000-00"
          : "000.000.000-009";
        },
        cpfOptions = {
          onKeyPress: function(val, e, field, options) {
            field.mask(cpfMascara.apply({}, arguments), options);
          }
        };

        $(".cpf_cnpj").mask(cpfMascara, cpfOptions);
      });

      function buscaDados(){
        let uf = $('#uf2').val();
        let cnpj = $('#cnpj').val();

        var path = window.location.protocol + '//' + window.location.host
        $.ajax
        ({
          type: 'GET',
          data: {
            cnpj: cnpj,
            uf: uf
          },
          url: path + '/nfe/consultaCadastro',

          dataType: 'json',
          success: function(e){
            console.log(e)
            if(e.infCons.infCad){
              let info = e.infCons.infCad;
              console.log(info)

              $('#ie_rg').val(info.IE)
              $('#razao_social').val(info.xNome)
              $('#name').val(info.xFant ? info.xFant : info.xNome)

              $('#rua').val(info.ender.xLgr)
              $('#numero').val(info.ender.nro)
              $('#bairro').val(info.ender.xBairro)
              let cep = info.ender.CEP;
              $('#zip_code').val(cep.substring(0, 5) + '-' + cep.substring(5, 9))

              findCidade(info.ender.xMun, (res) => {

                if(res){

                  var $option = $("<option selected></option>").val(res.id).text(res.nome + " (" + res.uf + ")");
                  $('#cidade_id').append($option).trigger('change');

                }
              })

            }else{
              swal('Algo deu errado', e.infCons.xMotivo, 'warning')
            }
          },
          error: function(e){
            console.log("err",e.responseText)
            swal('Algo deu errado', e.responseText, 'warning')

          }
        });
      }

      function findCidade(nomeCidade, call){
        var path = window.location.protocol + '//' + window.location.host
        $.get(path + '/nfe/findCidade', {nome: nomeCidade} )
        .done((success) => {
          call(success)
        })
        .fail((err) => {
          call(err)
        })
      }
    </script>

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->