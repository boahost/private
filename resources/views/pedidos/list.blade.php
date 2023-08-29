@extends('layouts.app')
@section('title', 'Pedidos de Ecommerce')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Ecommerce
        <small>Pedidos</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
    @if(empty($only) || in_array('sell_list_filter_date_range', $only))
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
            {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
        </div>
    </div>
    @endif
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Pedidos de Ecommerce'])
    @can('user.create')
    @slot('tool')
    
    @endslot
    @endcan
    @can('user.view')

    <div class="col-12" style="margin-bottom: 10px;">
        <a class="btn btn-primary" href="/pedidosEcommerce/consultarPagamentos">
            <i class="fa fa-retweet"></i>
            Consultar pagamentos em aberto
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Valor total</th>
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

<div class="modal fade" id="modal-msg" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Mensagem</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="msg"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    //Roles table
    $(document).ready( function(){
        $('#sell_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function (start, end) {
                $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                users_table.ajax.reload();
            }
            );
        $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_list_filter_date_range').val('');
            users_table.ajax.reload();
        });
        var users_table = $('#users_table').DataTable({
            processing: true,
            serverSide: true,
            lengthMenu: [10, 25, 50, 100, 200],
            aaSorting: [[1, 'desc']],
            "ajax": {
                "url": "/pedidosEcommerce",
                "data": function ( d ) {
                    if($('#sell_list_filter_date_range').val()) {
                        var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                    }

                    d = __datatable_ajax_callback(d);
                    
                }
            },
            // ajax: '/pedidosEcommerce',
            columnDefs: [ {
                "targets": [1],
                "orderable": true,
                "searchable": true
            } ],
            "columns":[
            {"data":"nome", "name": "cliente_ecommerces.nome"},
            {"data":"valor_total"},
            {"data":"created_at"},
            {"data":"forma_pagamento"},
            {"data":"status_preparacao"},
            {"data":"action"}
            ]
        });
    });

</script>
@endsection
