@extends('layouts.app')
@section('title', 'Receber conta')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>Receber conta</h1>
</section>

<!-- Main content -->
<section class="content">
	{!! Form::open(['url' => action('RevenueController@receivePut', [$item->id]), 'method' => 'put', 'id' => 'add_form', 'files' => true ]) !!}
	<div class="box box-primary">
		<div class="box-body">
			<div class="row">

				<div class="col-sm-3">
					<div class="form-group">
						{!! Form::label("tipo_pagamento" , 'Forma de pagamento' . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fas fa-list"></i>
							</span>
							{!! Form::select("tipo_pagamento", $payment_types, $item->forma_pagamento, ['class' => 'form-control col-md-12 payment_types_dropdown', 'required', 'id' => "forma_pagamento", 'style' => 'width:100%;']); !!}
						</div>
					</div>
				</div>
				<div class="col-sm-2">
					<div class="form-group">
						{!! Form::label('vencimento', 'Vencimento:*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('vencimento', \Carbon\Carbon::parse($item->vencimento)->format('d/m/Y'), ['class' => 'form-control', 'disabled', 'required', 'id' => '']); !!}
						</div>
					</div>
				</div>

				<div class="col-sm-2">
					<div class="form-group">
						{!! Form::label('recebimento', 'Recebimento:*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="fa fa-calendar"></i>
							</span>
							{!! Form::text('recebimento', \Carbon\Carbon::parse($item->vencimento)->format('d/m/Y'), ['class' => 'form-control', 'readonly', 'required', 'id' => 'vencimento']); !!}
						</div>
					</div>
				</div>

				<div class="col-sm-2">
					<div class="form-group">

						{!! Form::label('final_total', __('sale.total_amount') . ':*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="glyphicon glyphicon-tag"></i>
							</span>
							{!! Form::text('final_total', number_format($item->valor_total,2), ['class' => 'form-control input_number money', 'readonly', 'placeholder' => __('sale.total_amount'), 'required']); !!}
						</div>
					</div>
				</div>

				<div class="col-sm-2">
					<div class="form-group">

						{!! Form::label('valor_recebido', 'Valor recebido:*') !!}
						<div class="input-group">
							<span class="input-group-addon">
								<i class="glyphicon glyphicon-tag"></i>
							</span>
							{!! Form::text('valor_recebido', number_format($item->valor_total,2), ['class' => 'form-control input_number money', 'placeholder' => __('sale.total_amount'), 'required']); !!}
						</div>
					</div>
				</div>
				

			</div>
		</div>
	</div> <!--box end-->
	<div class="col-sm-12">
		<button type="submit" id="submit_button" class="btn btn-primary pull-right">Receber</button>
	</div>
	{!! Form::close() !!}

</section>

@endsection


@section('javascript')
<script type="text/javascript">


</script>
@endsection