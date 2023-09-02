@extends('layouts.app')
@section('title', 'Boletos sem remessa')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>Boletos sem remessa
		<small>Gerenciar</small>
	</h1>
</section>

<!-- Main content -->
<section class="content">
	@component('components.widget', ['class' => 'box-primary'])
	@can('user.create')
	@slot('tool')
	
	@endslot
	@endcan
	@can('user.view')
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="banks_table">
			<thead>
				<tr>
					<th></th>
					<th>Cliente</th>
					<th>Valor</th>
					<th>Banco</th>
					<th>Vencimento</th>
					<th>Nº boleto</th>
					<th>Nº documento</th>
					<th>Juros</th>
					<th>Multa</th>
				</tr>
			</thead>

			<tbody>
				@foreach($boletos as $b)
				<tr>
					<td>
						<input class="check-boleto check-{{$b->id}}" type="checkbox"  onclick="boleto_selecionado('{{$b->id}}')"/>
					</td>
					<td>{{$b->revenue->contact->name}}</td>
					<td>{{ number_format($b->revenue->valor_total, 2, ',', '.') }}</td>
					<td>{{$b->bank->info}}</td>
					<td>{{ \Carbon\Carbon::parse($b->vencimento)->format('d/m/Y') }}</td>
					<td>{{ $b->numero }}</td>
					<td>{{ $b->numero_documento }}</td>
					<td>{{ number_format($b->juros, 2, ',', '.') }}</td>
					<td>{{ number_format($b->multa, 2, ',', '.') }}</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	@endcan
	@endcomponent

	<button class="btn btn-success" onclick="gerarBoletos()">Gerar Remessa</button>

</section>
<!-- /.content -->
@stop

@section('javascript')
<script type="text/javascript">
	var BOELTOS = [];

	function boleto_selecionado(id){
		if($('.check-'+id).is(':checked')){
			BOELTOS.push(id)
		}else{
			let temp = BOELTOS.filter((x) => {
				return x != id
			})
			BOELTOS = temp
		}
	}

	function gerarBoletos(){
		if(BOELTOS.length > 0){
			var path = window.location.protocol + '//' + window.location.host

			location.href = path + '/remessasBoleto/gerarRemessaMulti/'+BOELTOS
		}else{
			swal("Atenção", "Slecione 1 ou mais boletos.", "warning")
		}
	}
</script>
@endsection

