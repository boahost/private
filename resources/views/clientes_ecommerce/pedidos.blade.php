@extends('layouts.app')
@section('title', 'Pedidos')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Pedidos
        <small>Total de pedidos {{sizeof($cliente->pedidos())}}</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Pedidos cliente  '.$cliente->nome])
    @can('user.create')
    @slot('tool')

    @endslot
    @endcan
    @can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Forma de pagamento</th>
                    <th>Status do pedido</th>

                    <th>Ação</th>
                </tr>
            </thead>
        </table>
    </div>
    @endcan
    @endcomponent

    <div class="modal fade user_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        var users_table = $('#users_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '/clienteEcommerce/pedidos/{{$cliente->id}}',
            columnDefs: [ {
                "targets": [3],
                "orderable": false,
                "searchable": false
            } ],
            "columns":[
            {"data":"valor_total"},
            {"data":"created_at"},
            {"data":"forma_pagamento"},
            {"data":"status"},

            {"data":"action"}
            ]
        });
        
        
    });
    
    
</script>
@endsection
