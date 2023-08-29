<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action('TypesOfServiceController@store'), 'method' => 'post', 'id' => 'types_of_service_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'lang_v1.add_type_of_service' )</h4>
    </div>

    <div class="modal-body">
      <div class="row">
      <div class="form-group col-md-12">
        {!! Form::label('name', 'Nome' . ':*') !!}
          {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => 'Nome']); !!}
      </div>

      <div class="form-group col-md-12">
        {!! Form::label('description', __( 'lang_v1.description' ) . ':') !!}
          {!! Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.description' ), 'rows' => 3]); !!}
      </div>

      <div class="form-group col-md-12">
      <table class="table table-slim">
        <thead>
          <tr>
            <th>@lang('sale.location')</th>
            <th>@lang('lang_v1.price_group')</th> 
          </tr>
          @foreach($locations as $key => $value)
            <tr>
              <td>{{$value}}</td>
              <td>{!! Form::select('location_price_group[' . $key . ']', $price_groups, null, ['class' => 'form-control input-sm select2', 'style' => 'width: 100%;']); !!}</td>
            </tr>
          @endforeach
        </thead>
      </table>
      </div>
       <div class="form-group col-md-6">
        {!! Form::label('packing_charge_type', 'Tipo de embalagem' . ':') !!}
          {!! Form::select('packing_charge_type', ['fixed' => __('lang_v1.fixed'), 'percent' => __('lang_v1.percentage')], 'fixed', ['class' => 'form-control']); !!}
      </div>
      <div class="form-group col-md-6">
        {!! Form::label('packing_charge', 'Taxa de embalagem' . ':') !!}
          {!! Form::text('packing_charge', null, ['class' => 'form-control input_number', 'placeholder' => 'Taxa de embalagem']); !!}
      </div>
      <div class="form-group col-md-12">
          <div class="checkbox">
            <label>
               {!! Form::checkbox('enable_custom_fields', 1, false); !!} Habilitar campos personalizados
            </label> @show_tooltip('Quatro campos personalizados estarão disponíveis ao adicionar venda')
          </div>
      </div>
      </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->