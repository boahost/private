@extends('layouts.app')
@section('title', 'Lista de contas bancárias')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
	<h1>Contas bancárias
		<small>Gerencia contas</small>
	</h1>
</section>

<!-- Main content -->
<section class="content">
	@component('components.widget', ['class' => 'box-primary', 'title' => 'Todas as Contas Bancárias'])
	@can('user.create')
	@slot('tool')
	<div class="box-tools">
		<a class="btn btn-block btn-primary" 
		href="{{ route('bank.create') }}" >
		<i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
	</div>
	@endslot
	@endcan
	@can('user.view')
	<div class="table-responsive">
		<table class="table table-bordered table-striped" id="banks_table">
			<thead>
				<tr>
					<th>Banco</th>
					<th>Agência</th>
					<th>Conta</th>
					<th>Títular</th>
					<th>Ação</th>
				</tr>
			</thead>
		</table>
	</div>
	@endcan
	@endcomponent

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
    	var banks_table = $('#banks_table').DataTable({
    		processing: true,
    		serverSide: true,
    		ajax: '/bank',
    		columnDefs: [ {
    			"targets": [4],
    			"orderable": false,
    			"searchable": false
    		} ],
    		"columns":[
    		{"data":"banco"},
    		{"data":"agencia"},
    		{"data":"conta"},
    		{"data":"titular"},
    		{"data":"action"}
    		]
    	});
    	$(document).on('click', 'button.delete_button', function(){
    		swal({
    			title: LANG.sure,
    			text: 'Esta conta será excluida',
    			icon: "warning",
    			buttons: true,
    			dangerMode: true,
    		}).then((willDelete) => {
    			if (willDelete) {
    				var href = $(this).data('href');
    				var data = $(this).serialize();
    				$.ajax({
    					method: "DELETE",
    					url: href,
    					dataType: "json",
    					data: data,
    					success: function(result){
    						if(result.success == true){
    							toastr.success(result.msg);
    							banks_table.ajax.reload();
    						} else {
    							toastr.error(result.msg);
    						}
    					}
    				});
    			}
    		});
    	});

    });
    
    
</script>
@endsection
