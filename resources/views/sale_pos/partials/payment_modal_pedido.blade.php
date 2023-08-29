<div class="modal fade" tabindex="-1" role="dialog" id="modal_payment">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Pagamento</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-9">
						<div class="row">
							<div id="payment_rows_div">
								@foreach($payment_lines as $payment_line)
								
								@if($payment_line['is_return'] == 1)
								@php
								$change_return = $payment_line;
								@endphp

								@continue
								@endif

								@include('sale_pos.partials.payment_row', ['removable' => !$loop->first, 'row_index' => $loop->index, 'payment_line' => $payment_line])
								@endforeach
							</div>
							<input type="hidden" id="payment_row_index" value="{{count($payment_lines)}}">
						</div>
						<div class="row">
							<div class="col-md-12">
								<button type="button" class="btn btn-primary btn-block" id="add-payment-row">@lang('sale.add_payment_row')</button>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									{!! Form::label('sale_note', 'Observação da venda:') !!}
									{!! Form::textarea('sale_note', !empty($transaction)? $transaction->additional_notes:null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Observação da venda']); !!}
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
									{!! Form::label('staff_note', 'Observação geral:') !!}
									{!! Form::textarea('staff_note', 
									!empty($transaction)? $transaction->staff_note:null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Observação geral']); !!}
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-3">
						<div class="box box-solid bg-orange">
							<div class="box-body">
								<div class="col-md-12">
									<strong>
										@lang('lang_v1.total_items'):
									</strong>
									<br/>
									<span class="lead text-bold total_quantity">0</span>
								</div>

								<div class="col-md-12">
									<hr>
									<strong>
										@lang('sale.total_payable'):
									</strong>
									<br/>
									<span class="lead text-bold total_payable_span">0</span>
								</div>

								<div class="col-md-12">
									<hr>
									<strong>
										@lang('lang_v1.total_paying'):
									</strong>
									<br/>
									<span class="lead text-bold total_paying">0</span>
									<input type="hidden" id="total_paying_input">
								</div>

								<div class="col-md-12">
									<hr>
									<strong>
										@lang('lang_v1.change_return'):
									</strong>
									<br/>
									<span class="lead text-bold change_return_span">0</span>
									{!! Form::hidden("change_return", $change_return['amount'], ['class' => 'form-control change_return input_number', 'required', 'id' => "change_return", 'placeholder' => __('sale.amount'), 'readonly']); !!}
									<!-- <span class="lead text-bold total_quantity">0</span> -->
									@if(!empty($change_return['id']))
									<input type="hidden" name="change_return_id" 
									value="{{$change_return['id']}}">
									@endif
								</div>

								<div class="col-md-12">
									<hr>
									<strong>
										@lang('lang_v1.balance'):
									</strong>
									<br/>
									<span class="lead text-bold balance_due">0</span>
									<input type="hidden" id="in_balance_due" value=0>
								</div>


								
							</div>
							<!-- /.box-body -->
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
				<button type="button" class="btn btn-primary" id="pos-save-pedido-print">@lang('sale.finalize_payment') imprimir</button>
				<button type="button" class="btn btn-success" id="pos-save-pedido">@lang('sale.finalize_payment')</button>
				
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Used for express checkout card transaction -->
<div class="modal fade" tabindex="-1" role="dialog" id="card_details_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">@lang('lang_v1.card_transaction_details')</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("card_number", __('lang_v1.card_no')) !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_no'), 'id' => "card_number", 'autofocus']); !!}
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("card_holder_name", "Nome do titular") !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_holder_name'), 'id' => "card_holder_name"]); !!}
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("card_transaction_number",__('lang_v1.card_transaction_no')) !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.card_transaction_no'), 'id' => "card_transaction_number"]); !!}
							</div>
						</div>
						<div class="clearfix"></div>
						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label("card_type", __('lang_v1.card_type')) !!}
								{!! Form::select("", ['credit' => 'Crédito', 'debit' => 'Débito'], 'visa',['class' => 'form-control select2', 'id' => "card_type" ]); !!}
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label("card_month", __('lang_v1.month')) !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.month'),
								'id' => "card_month" ]); !!}
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label("card_year", __('lang_v1.year')) !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.year'), 'id' => "card_year" ]); !!}
							</div>
						</div>
						<div class="col-md-3">
							<div class="form-group">
								{!! Form::label("card_security",__('lang_v1.security_code')) !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => __('lang_v1.security_code'), 'id' => "card_security"]); !!}
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="pos-save-card">@lang('sale.finalize_payment')</button>
			</div>

		</div>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="modal_edit_line_boleto">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" id="close-modal-line-boleto"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Editar boleto</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">

						<input type="hidden" id="id_doc" value="" name="">
						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("vencimento_boleto", "Vencimento") !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => "Vencimento", 'id' => "vencimento_boleto", 'autofocus']); !!}
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("valor_boleto", "Valor") !!}
								{!! Form::text("", null, ['class' => 'form-control money', 'placeholder' => "Valor", 'id' => "valor_boleto"]); !!}
							</div>
						</div>

						<div class="col-md-4">
							<div class="form-group">
								{!! Form::label("boleto_doc", "Nº Doc") !!}
								{!! Form::text("", null, ['class' => 'form-control', 'placeholder' => "Nº Doc", 'id' => "boleto_doc"]); !!}
							</div>
						</div>

					</div>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="btn-save-line-bleto">Salvar</button>
			</div>

		</div>
	</div>
</div>