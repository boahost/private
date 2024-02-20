@extends('layouts.app')

@section('title', __('sale.add_sale'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>@lang('sale.add_sale')</h1>
</section>
<!-- Main content -->
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	@if(!empty($pos_settings['allow_overselling']))
	<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
	<input type="hidden" id="reward_point_enabled">
	@endif
	@if(is_null($default_location))
	<div class="row">
		<div class="col-sm-3">
			<div class="form-group">
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-map-marker"></i>
					</span>
					{!! Form::select('select_location_id', $business_locations, null, ['class' => 'form-control input-sm',
					'placeholder' => __('lang_v1.select_location'),
					'id' => 'select_location_id',
					'required', 'autofocus'], $bl_attributes); !!}
					<span class="input-group-addon">
						@show_tooltip(__('tooltip.sale_location'))
					</span>
				</div>
			</div>
		</div>
	</div>
	@endif
	<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
	{!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_sell_form' ]) !!}
	<div class="row">
		<div class="col-md-12 col-sm-12">
			@component('components.widget', ['class' => 'box-primary'])
			{!! Form::hidden('location_id', !empty($default_location) ? $default_location->id : null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_accounts' => !empty($default_location) ? $default_location->default_payment_accounts : '']); !!}

			@if(!empty($price_groups))
			@if(count($price_groups) > 1)
			<div class="col-sm-4">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fas fa-money-bill-alt"></i>
						</span>
						@php
						reset($price_groups);
						@endphp
						{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
						{!! Form::select('price_group', $price_groups, null, ['class' => 'form-control select2', 'id' => 'price_group']); !!}
						<span class="input-group-addon">
							@show_tooltip(__('lang_v1.price_group_help_text'))
						</span>
					</div>
				</div>
			</div>

			@else
			@php
			reset($price_groups);
			@endphp
			{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
			@endif
			@endif

			{!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}

			@if(in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
			<div class="col-md-4 col-sm-6">
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
						</span>
						{!! Form::select('types_of_service_id', $types_of_service, null, ['class' => 'form-control', 'id' => 'types_of_service_id', 'style' => 'width: 100%;', 'placeholder' => 'Tipo de serviço']); !!}

						{!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

						<span class="input-group-addon">
							@show_tooltip('Tipo de serviço significa serviços como jantares, encomendas, entrega ao domicílio, entrega a terceiros, etc.')
						</span>
					</div>


					<small><p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p></small>
				</div>
			</div>
			<div class="modal fade types_of_service_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
			@endif

			@if(in_array('subscription', $enabled_modules))
			<div class="col-md-4 pull-right col-sm-6">
				<div class="checkbox">
					<label>
						{!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
					</label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
				</div>
			</div>
			@endif

			<div class="clearfix"></div>
			<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
				<div class="form-group">
					{!! Form::label('contact_id', __('contact.customer') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-user"></i>
						</span>
						<input type="hidden" id="default_customer_id"
						value="{{ $walk_in_customer['id']}}" >
						<input type="hidden" id="default_customer_name"
						value="{{ $walk_in_customer['name']}}" >
						{!! Form::select('contact_id',
						[], null, ['class' => 'form-control mousetrap', 'id' => 'customer_id', 'placeholder' => 'Entre com nome do cliente', 'required']); !!}
						<span class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
						</span>
					</div>
				</div>
			</div>



			@if(!empty($commission_agent))
			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
					{!! Form::select('commission_agent',
					$commission_agent, null, ['class' => 'form-control select2']); !!}
				</div>
			</div>
			@endif

			<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
				<div class="form-group">
					{!! Form::label('transaction_date', __('sale.sale_date') . ':*') !!}
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</span>
						{!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']); !!}
					</div>
				</div>
			</div>

			<div class="@if(!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
				<div class="form-group">
					{!! Form::label('status', __('sale.status') . ':*') !!}
					{!! Form::select('status', ['final' => 'Final', 'draft' => __('sale.draft'), 'quotation' => __('lang_v1.quotation')], null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
				</div>
			</div>

			<div class="col-sm-3">
				<div class="form-group">
					{!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':') !!}
					{!! Form::select('invoice_scheme_id', $invoice_schemes, $default_invoice_schemes->id, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
				</div>
			</div>

			<div class="col-sm-4">
				<div class="form-group">
					{!! Form::label('natureza_id', 'Natureza de Operação'. ':*') !!}
					{!! Form::select('natureza_id', $naturezas, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
				</div>
			</div>

			<div class="clearfix"></div>
			<!-- Call restaurant module if defined -->
			@if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
			<span id="restaurant_module_span">
				<div class="col-md-3"></div>
			</span>
			@endif


			<div class="col-sm-3" style="visibility: hidden">
				<div class="form-group">
					<div class="multi-input">
						{!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
						<br/>
						{!! Form::number('pay_term_number', $walk_in_customer['pay_term_number'], ['class' => 'form-control width-40 pull-left', 'placeholder' => __('contact.pay_term')]); !!}

						{!! Form::select('pay_term_type',
						['months' => __('lang_v1.months'),
						'days' => __('lang_v1.days')],
						$walk_in_customer['pay_term_type'],
						['class' => 'form-control width-60 pull-left','placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
			</div>
			@endcomponent

            <script src="https://code.jquery.com/jquery-1.10.2.js"></script>
            <script>
                $(document).ready(function() {
                    $('ul').on('click', 'a', function(e) {
                        e.preventDefault();
                        var ncm = $(this).closest('li').data('ncm');
                        $('#search_product').val(ncm).trigger('input');
                        $('#search_product').trigger('change');
                    });
                });
                </script>

                @component('components.widget', ['class' => 'box-primary', 'title' => 'Produtos da Venda'])
                <div class="col-sm-10 col-sm-offset-1">
                @if(!empty($produto))
                <input type="hidden" value="{!! Request::input('id_venda') !!}" name="id_venda">
                <h5> Produtos do PDV </h5>
                    <ul>
                    @foreach($produto as $row)
                        <li data-ncm="{!! $row["produto"]->name !!}">
                            <a href="#"><span class="btn btn-success btn-sm mt-2">{!! $row["produto"]->name !!} - Adicionar a nota fiscal </span> -
                                Quantidade - {!! $row['quantidade'] !!}</a>
                        </li>
                    @endforeach
                        </ul>
                @endif

				<div class="form-group">
					<div class="input-group">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat" data-toggle="modal" data-target="#configure_search_modal" title="{{__('lang_v1.configure_product_search')}}"><i class="fa fa-barcode"></i></button>
						</div>
						{!! Form::text('search_product',null, ['class' => 'form-control', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
						'disabled' => is_null($default_location)? true : false,
						'autofocus' => is_null($default_location)? false : true,
						]); !!}
						<span class="input-group-btn">
							<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
						</span>
					</div>
				</div>
			</div>

			<div class="row col-sm-12 pos_product_div" style="min-height: 0">

				<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

				<!-- Keeps count of product rows -->
				<input type="hidden" id="product_row_count"
				value="0">
				@php
				$hide_tax = '';
				if( session()->get('business.enable_inline_tax') == 0){
					$hide_tax = 'hide';
				}
				@endphp
				<div class="table-responsive">
					<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
						<thead>
							<tr>
								<th class="text-center">
									@lang('sale.product')
								</th>
								<th class="text-center">
									@lang('sale.qty')
								</th>
								@if(!empty($pos_settings['inline_service_staff']))
								<th class="text-center">
									@lang('restaurant.service_staff')
								</th>
								@endif
								<th class="text-center {{$hide_tax}}">
									@lang('sale.price_inc_tax')
								</th>
								<th class="text-center">
									@lang('sale.subtotal')
								</th>
								<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<div class="table-responsive">
					<table class="table table-condensed table-bordered table-striped">
						<tr>
							<td>
								<div class="pull-right">
									<b>@lang('sale.item'):</b>
									<span class="total_quantity">0</span>
									&nbsp;&nbsp;&nbsp;&nbsp;
									<b>@lang('sale.total'): </b>
									<span class="price_total">0</span>
								</div>
							</td>
						</tr>
					</table>
				</div>
			</div>
			@endcomponent


			<!-- aqui desconto -->

			<div class="box @if(!empty($class)) {{$class}} @else box-primary @endif" id="accordion">
				<div class="box-header with-border" style="cursor: pointer;">
					<h3 class="box-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseDesconto">
							Desconto
						</a>
					</h3>
				</div>
				<div id="collapseDesconto" class="panel-collapse active collapse" aria-expanded="true">
					<div class="box-body">

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('discount_type', 'Tipo do desconto*' ) !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!! Form::select('discount_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'percentage' , ['class' => 'form-control','placeholder' => __('messages.please_select'), 'required', 'data-default' => 'percentage']); !!}
								</div>
							</div>
						</div>
						@php
						$max_discount = !is_null(auth()->user()->max_sales_discount_percent) ? auth()->user()->max_sales_discount_percent : '';

						//if sale discount is more than user max discount change it to max discount
						$sales_discount = $business_details->default_sales_discount;
						if($max_discount != '' && $sales_discount > $max_discount) $sales_discount = $max_discount;
						@endphp
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('discount_amount', __('sale.discount_amount') . ':*' ) !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!! Form::text('discount_amount', @num_format($sales_discount), ['class' => 'form-control input_number', 'data-default' => $sales_discount, 'data-max-discount' => $max_discount, 'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', ['discount' => $max_discount != '' ? @num_format($max_discount) : '']) ]); !!}
								</div>
							</div>
						</div>
						<div class="col-md-4"><br>
							<b>@lang( 'sale.discount_amount' ):</b>(-)
							<span class="display_currency" id="total_discount">0</span>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-12 well well-sm bg-light-gray @if(session('business.enable_rp') != 1) hide @endif">
							<input type="hidden" name="rp_redeemed" id="rp_redeemed" value="0">
							<input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="0">
							<div class="col-md-12"><h4>{{session('business.rp_name')}}</h4></div>
							<div class="col-md-4">
								<div class="form-group">
									{!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':' ) !!}
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-gift"></i>
										</span>
										{!! Form::number('rp_redeemed_modal', 0, ['class' => 'form-control direct_sell_rp_input', 'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'), 'min' => 0, 'data-max_points' => 0, 'data-min_order_total' => session('business.min_order_total_for_redeem') ]); !!}
										<input type="hidden" id="rp_name" value="{{session('business.rp_name')}}">
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">0</span></p>
							</div>
							<div class="col-md-4">
								<p><strong>@lang('lang_v1.redeemed_amount'):</strong> (-)<span id="rp_redeemed_amount_text">0</span></p>
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('tax_rate_id', __('sale.order_tax') . ':*' ) !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!! Form::select('tax_rate_id', $taxes['tax_rates'], $business_details->default_sales_tax, ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default'=> $business_details->default_sales_tax], $taxes['attributes']); !!}

									<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount"
									value="@if(empty($edit)) {{@num_format($business_details->tax_calculation_amount)}} @else {{@num_format(optional($transaction->tax)->amount)}} @endif" data-default="{{$business_details->tax_calculation_amount}}">
								</div>
							</div>
						</div>
						<div class="col-md-4 col-md-offset-4">
							<b>@lang( 'sale.order_tax' ):</b>(+)
							<span class="display_currency" id="order_tax">0</span>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('shipping_details', 'Detalhes de envio') !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!! Form::textarea('shipping_details',null, ['class' => 'form-control','placeholder' => 'Detalhes de envio' ,'rows' => '1', 'cols'=>'30']); !!}
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-map-marker"></i>
									</span>
									{!! Form::textarea('shipping_address',null, ['class' => 'form-control','placeholder' => __('lang_v1.shipping_address') ,'rows' => '1', 'cols'=>'30']); !!}
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								{!!Form::label('shipping_charges', 'Custos de envio')!!}
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-info"></i>
									</span>
									{!!Form::text('shipping_charges',@num_format(0.00),['class'=>'form-control input_number','placeholder'=> __('sale.shipping_charges')]);!!}
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
								{!! Form::select('shipping_status',$shipping_statuses, null, ['class' => 'form-control','placeholder' => __('messages.please_select')]); !!}
							</div>
						</div>

						<div class="clearfix"></div>
						<div class="col-md-4 col-md-offset-8">
							@if(!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
							<small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
							<br/>
							<input type="hidden" name="round_off_amount"
							id="round_off_amount" value=0>
							@endif
							<div><b>@lang('sale.total_payable'): </b>
								<input type="hidden" name="final_total" id="final_total_input">
								<span id="total_payable">0</span>
							</div>
						</div>


						<input type="hidden" name="is_direct_sale" value="1">


					</div>
				</div>
			</div>

			<!-- termina desconto -->


			<div class="box @if(!empty($class)) {{$class}} @else box-primary @endif" id="accordion">
				<div class="box-header with-border" style="cursor: pointer;">
					<h3 class="box-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseFilter">
							Transporte
						</a>
					</h3>
				</div>
				<div id="collapseFilter" class="panel-collapse active collapse" aria-expanded="true">
					<div class="box-body">
						<div class="col-md-2">
							<div class="form-group">
								{!! Form::label('placa',  'Placa:' ) !!}
								{!! Form::text('placa', null, ['class' => 'form-control','placeholder' => 'placa',
								'data-mask="AAA-AAAA"', 'data-mask-reverse="true"']); !!}
							</div>
						</div>

						<div class="col-md-1">
							<div class="form-group">
								{!! Form::label('uf', 'UF:' ) !!}

								{!! Form::select('uf', $ufs, 'uf' , ['class' => 'form-control select2','placeholder' => 'UF', 'data-default' => 'percentage']); !!}

							</div>
						</div>

						<div class="col-md-2 col-sm-2">
							<div class="form-group">
								{!! Form::label('tipo', 'Tipo do frete:' ) !!}

								{!! Form::select('tipo', $tiposFrete, 'tipo' , ['class' => 'form-control', 'data-default' => 'percentage']); !!}

							</div>
						</div>

						<div class="col-md-2">
							<div class="form-group">
								{!! Form::label('peso_liquido',  'Peso liquido:' ) !!}
								{!! Form::text('peso_liquido', null, ['class' => 'form-control','placeholder' => 'Peso liquido', 'data-mask="00000000.000"', 'data-mask-reverse="true"']); !!}
							</div>
						</div>

						<div class="col-md-2">
							<div class="form-group">
								{!! Form::label('peso_bruto',  'Peso bruto:' ) !!}
								{!! Form::text('peso_bruto', null, ['class' => 'form-control','placeholder' => 'Peso bruto', 'data-mask="00000000.000"', 'data-mask-reverse="true"']); !!}
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label('especie',  'Espécie:' ) !!}
								{!! Form::text('especie', null, ['class' => 'form-control','placeholder' => 'Espécie']); !!}
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label('qtd_volumes',  'Quantidade de volumes:' ) !!}
								{!! Form::text('qtd_volumes', null, ['class' => 'form-control','placeholder' => 'Quantidade de volumes']); !!}
							</div>
						</div>

						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label('numeracao_volumes',  'Numeração de volumes:' ) !!}
								{!! Form::text('numeracao_volumes', null, ['class' => 'form-control','placeholder' => 'Numeração de volumes']); !!}
							</div>
						</div>

						<div class="col-md-2">
							<div class="form-group">
								{!! Form::label('valor_frete',  'Valor do frete:' ) !!}
								{!! Form::text('valor_frete', 0.00, ['id' => 'valor_frete', 'class' => 'form-control','placeholder' => 'Valor do frete', 'data-mask="00000000,00"', 'data-mask-reverse="true"']); !!}
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('transportadora_id', 'Transportadora:' ) !!}

								{!! Form::select('transportadora_id', $transportadoras, 'transportadora_id' , ['class' => 'form-control select2','placeholder' => 'Transportadora', 'data-default' => 'percentage', 'style' => 'width: 100%']); !!}

							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':' ) !!}
								{!! Form::text('delivered_to', null, ['class' => 'form-control','placeholder' => __('lang_v1.delivered_to')]); !!}
							</div>
						</div>
					</div>
				</div>
			</div>

			@component('components.widget', ['class' => 'box-primary'])
			<div class="col-md-12">
				<div class="form-group">
					{!! Form::label('additional_notes', 'Informação complementar') !!}
					{!! Form::textarea('additional_notes', $default_location ? $default_location->info_complementar : '', ['class' => 'form-control', 'rows' => 3, 'id' => 'info_complementar']); !!}
				</div>
			</div>

			<div class="col-sm-6">
				<div class="form-group">
					{!! Form::label('referencia_nfe', 'Referência NF-e' . ':' ) !!}
					{!! Form::text('referencia_nfe', null, ['class' => 'form-control','placeholder' => 'Referência NF-e', 'data-mask="00000000000000000000000000000000000000000000"', 'data-mask-reverse="true"']); !!}
				</div>
			</div>

			@include('sale_pos.partials.payment_modal_pedido')

			<input type="hidden" id="json_boleto" name="json_boleto">

			{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
			<div class="col-sm-12 text-right">
		<!-- <button type="button" id="submit-sell" class="btn btn-primary btn-flat">@lang('messages.save')</button>
			<button type="button" id="save-and-print" class="btn btn-primary btn-flat">@lang('lang_v1.save_and_print')</button> -->

			<button type="button" class="btn bg-navy btn-default" id="pedido-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-check" aria-hidden="true"></i> Finalizar Venda</button>
		</div>
		@endcomponent

	</div>
</div>





<!-- TAG Pagamento -->


<!-- <div class="row">

	{!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']); !!}
	<div class="col-sm-12 text-right">
	<button type="button" id="submit-sell" class="btn btn-primary btn-flat">@lang('messages.save')</button>
		<button type="button" id="save-and-print" class="btn btn-primary btn-flat">@lang('lang_v1.save_and_print')</button>

		<button type="button" class="btn bg-navy btn-default" id="pedido-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-check" aria-hidden="true"></i> Finalizar</button>
	</div>

</div> -->
<br>

@if(empty($pos_settings['disable_recurring_invoice']))
@include('sale_pos.partials.recurring_invoice_modal')
@endif


{!! Form::close() !!}
</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog"
aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog"
aria-labelledby="gridSystemModalLabel">
</div>

<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

@include('sale_pos.partials.configure_search_modal')
@stop


@section('javascript')


<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

<!-- Call restaurant module if defined -->
@if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>


@endif


<script type="text/javascript">
	$('#select_location_id').change((target) => {
		let id = target.target.value
		if(id){
			$.get('/business-location/'+id+'/settingsAjax')
			.done((res) => {
				console.log(res)
				$('#info_complementar').val(res.info_complementar)
			})
			.fail((err) => {
				console.log(err)
			})
		}
	})
</script>


@endsection
