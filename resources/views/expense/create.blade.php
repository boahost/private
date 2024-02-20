@extends('layouts.app')
@section('title', 'Adicionar conta a pagar')

@section('content')

<script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>



<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>Nova conta a pagar</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action('ExpenseController@store'), 'method' => 'post', 'id' => 'add_expense_form', 'files' => true ]) !!}
	<div class="box box-primary">
		<div class="box-body">
			<div class="row">

				@if(count($business_locations) == 1)
				@php
				$default_location = current(array_keys($business_locations->toArray()))
				@endphp
				@else
				@php $default_location = null; @endphp
				@endif
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('location_id', __('purchase.business_location').':*') !!}
						{!! Form::select('location_id', $business_locations, $default_location, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']); !!}
					</div>
				</div>

				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('supplier_id', __('purchase.supplier') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-user"></i>
							</span>
							{!! Form::select('contact_id', [], null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'required', 'id' => 'supplier_id']); !!}
							<span class="input-group-btn">
								<button type="button" class="btn btn-default bg-white btn-flat add_new_supplier" data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
							</span>
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('expense_category_id', 'Categoria:') !!}
						{!! Form::select('expense_category_id', $expense_categories, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('ref_no', __('purchase.ref_no').':') !!}
						{!! Form::text('ref_no', null, ['class' => 'form-control']); !!}
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('transaction_date', __('messages.date') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('transaction_date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'expense_transaction_date dataInicial']); !!}
						</div>
					</div>
				</div>
				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label('expense_for', 'Conta para:') !!} @show_tooltip('Escolha o usuário para quem a conta está relacionada. (opcional)')
						{!! Form::select('expense_for', $users, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]); !!}
					</div>
				</div>
				<div class="col-sm-4">
					<div class="form-group">
						{!! Form::label('document', 'Documento anexo' . ':') !!}
						{!! Form::file('document', ['id' => 'upload_document', 'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types')))]); !!}
						<p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])
						@includeIf('components.document_help_text')</p>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-group">
						{!! Form::label('additional_notes', 'Observação' . ':') !!}
						{!! Form::textarea('additional_notes', null, ['class' => 'form-control', 'rows' => 3]); !!}
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-md-2">
					<div class="form-group">
						{!! Form::label('tax_id', __('product.applicable_tax') . ':' ) !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-info"></i>
							</span>
							{!! Form::select('tax_id', $taxes['tax_rates'], null, ['class' => 'form-control'], $taxes['attributes']); !!}

							<input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount"
							value="0">
						</div>
					</div>
				</div>

            <div class="col-sm-2">
					<div class="form-group">
						{!! Form::label('final_total', __('sale.total_amount') . ':*') !!}
						{!! Form::text('final_total', null, ['class' => 'form-control input_number', 'id' => 'ValorFatura' ,'placeholder' => __('sale.total_amount'), 'required']); !!}
					</div>
				</div>

                <div class="col-sm-2">
                    <div class="form-group">
                        <label> Fatura recorrente? </label>
                        <select class="form-control" name="recorrente" id="recorrente">
                            <option value="1">Sim</option>
                            <option selected value="2">Não</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-2" id="parcela">
                    <div class="form-group">
                        <label> Quantas parcelas? </label>
                        <select class="form-control" id="numParcelas" name="numParcelas">
                            <option value="1">1x</option>
                            <option value="2">2x</option>
                            <option value="3">3x</option>
                            <option value="4">4x</option>
                            <option value="5">5x</option>
                            <option value="6">6x</option>
                            <option value="7">7x</option>
                            <option value="8">8x</option>
                            <option value="9">9x</option>
                            <option value="10">10x</option>
                            <option value="11">11x</option>
                            <option value="12">12x</option>
                        </select>
                    </div>
                </div>

                 <div class="col-sm-2" id="vencimento">
                    <div class="form-group">
                        <label> Vencimento de cada mês </label>
                        <select class="form-control" id="diaVencimento" name="diaVencimento">
                            <option value='01'>1</option>
                            <option value='02'>2</option>
                            <option value='03'>3</option>
                            <option value='04'>4</option>
                            <option value='05'>5</option>
                            <option value='06'>6</option>
                            <option value='07'>7</option>
                            <option value='08'>8</option>
                            <option value='09'>9</option>
                            <option value='10'>10</option>
                            <option value='11'>11</option>
                            <option value='12'>12</option>
                            <option value='13'>13</option>
                            <option value='14'>14</option>
                            <option value='15'>15</option>
                            <option value='16'>16</option>
                            <option value='17'>17</option>
                            <option value='18'>18</option>
                            <option value='19'>19</option>
                            <option value='20'>20</option>
                            <option value='21'>21</option>
                            <option value='22'>22</option>
                            <option value='23'>23</option>
                            <option value='24'>24</option>
                            <option value='25'>25</option>
                            <option value='26'>26</option>
                            <option value='27'>27</option>
                            <option value='28'>28</option>
                            <option value='29'>29</option>
                            <option value='30'>30</option>
                            <option value='31'>31</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-2" id="boleto">
                    <label> # </label>
                    <div class="form-group">
                        <a id="gerarFaturas" class="btn btn-primary"> Gerar faturas </a>
                    </div>
                </div>

                <div class="col-sm-6">
                    <div id="faturas"></div>
                </div>
			</div>
		</div>
        <script>

        $("#parcela").hide()
        $("#vencimento").hide()
        $("#boleto").hide()
        $('#recorrente').change(function(){
      var respostaForm = $('#recorrente').val()

        if (respostaForm == 1){
            $("#parcela").show()
            $("#vencimento").show()
            $("#boleto").show()
        } else {
            $("#parcela").hide()
            $("#vencimento").hide()
            $("#boleto").hide()
        }

  })

  </script>


<script>
        $("#gerarFaturas").click(function(){

            var hoje = new Date();
            var dia = hoje.getDate();
            var mes = hoje.getMonth() + 1;
            var ano = hoje.getFullYear();
            if (dia < 10) {
                dia = '0' + dia;
            }
            if (mes < 10) {
                mes = '0' + mes;
            }
            var dataInicial = ano + '-' + mes + '-' + dia;


            var numParcelas = parseInt($("#numParcelas").val());
            var diaVencimento = parseInt($("#diaVencimento").val());
            var valorFatura = $("#ValorFatura").val(); // Obtém o valor da fatura do campo de entrada


            var faturasDiv = $("#faturas");
            faturasDiv.empty();

            for (var i = 0; i < numParcelas; i++) {
                var novaData = new Date(dataInicial);
                novaData.setMonth(novaData.getMonth() + i);
                novaData.setDate(diaVencimento);

                var novaFatura = $("<div class='col-sm-12'><labe>Fatura " + (i + 1 +1) + ": " + novaData.toLocaleDateString('pt-BR') + " </label> <input class='form-control' name='fatura[]' type='text' value='" + valorFatura + "'></div>");
                faturasDiv.append(novaFatura);
            }
        });
</script>



	</div> <!--box end-->
	@component('components.widget', ['class' => 'box-primary', 'id' => "payment_rows_div", 'title' => __('purchase.add_payment')])
	<div class="payment_row">
		@include('sale_pos.partials.payment_row_form', ['row_index' => 0])
		<hr>
		<div class="row">
			<div class="col-sm-12">
				<div class="pull-right">
					<strong>Valor total:</strong>
					<span id="payment_due">{{@num_format(0)}}</span>
				</div>
			</div>
		</div>
	</div>
	@endcomponent
	<div class="col-sm-12">
		<button type="submit" class="btn btn-primary pull-right">@lang('messages.save')</button>
	</div>
	{!! Form::close() !!}
</section>

<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
@endsection
@section('javascript')
<script type="text/javascript">
	$(document).on('change', 'input#final_total, input.payment-amount', function() {
		calculateExpensePaymentDue();
	});

	function calculateExpensePaymentDue() {
		var final_total = __read_number($('input#final_total'));
		var payment_amount = __read_number($('input.payment-amount'));
		var payment_due = final_total - payment_amount;
		$('#payment_due').text(__currency_trans_from_en(payment_due, true, false));
	}
</script>
<script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script>

@endsection
