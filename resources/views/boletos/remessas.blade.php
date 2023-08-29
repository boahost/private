@extends('layouts.app')
@section('title', 'Lista de remessas')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>Remessas
		<small>Gerenciar remessas</small>
	</h1>
</section>

<!-- Main content -->
<section class="content">
	@component('components.widget', ['class' => 'box-primary', 'title' => 'Todas as remessas'])
	@can('user.create')
	@slot('tool')
	<div class="box-tools" style="margin-right: 3px;">
		<a type="button" class="btn btn-block btn-info btn-gerar-boletos" href="/remessasBoleto/boletosSemRemessa">
			<i class="fa fa-file"></i> Boletos sem remessa
		</a>
	</div>
	@endslot
	@endcan
	@can('user.view')
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="banks_table">
			<thead>
				<tr>
					<th>Nome do arquivo</th>
					<th>Total de boletos</th>
					<th>Ação</th>
				</tr>
			</thead>

			<tbody>
				@foreach($remessas as $r)
				<tr>
					<td>{{ $r->nome_arquivo }}</td>
					<td>{{ sizeof($r->boletos) }}</td>

					<td>
						<form action="{{ route('remessa.destroy', $r->id) }}" method="post"
							id="form-{{$r->id}}">
							@csrf
							@method('delete')
							<a href="/remessasBoleto/download/{{$r->id}}" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> Downlaod arquivo</a>
							&nbsp;<button type="button" class="btn btn-xs btn-danger delete_user_button btn-delete"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>
							</button>
						</form>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
		@endcan
		@endcomponent

	</section>
	<!-- /.content -->
	@stop

	@section('javascript')
	<script type="text/javascript">
		$(".btn-delete").on("click", function(e) {
			e.preventDefault();
			var form = $(this)
			.parents("form")
			.attr("id");
			swal({
				title: "Você está certo?",
				text:
				"Uma vez deletado, você não poderá recuperar esse item novamente!",
				icon: "warning",
				buttons: true,
				buttons: ["Cancelar", "Excluir"],
				dangerMode: true
			}).then(isConfirm => {
				if (isConfirm) {
					document.getElementById(form).submit();
				} else {
					swal("Este item está salvo!");
				}
			});
		});
	</script>
	@endsection

