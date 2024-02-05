@extends('layouts.app')
@section('title', 'Inutilização')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Inutilização
        <small>Cancelamento de Números</small>
    </h1>

</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'Lista de inutilização' )])

    @slot('tool')
    <div class="box-tools">
        <a class="btn btn-block btn-primary"
        href="{{action('InutilController@create')}}">
        <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
    </div>
    @endslot

    @can('user.view')
    <table class="table table-bordered table-striped" id="inutilizacao_table">
        <thead>
            <tr>
                <th>Número Inicio</th>
                <th>Número Final</th>
                <th>Série</th>
                <th>Ambiente</th>
                <th>Modelo</th>
                <th>Status</th>
                <th>Data</th>
                <th>Ação</th>
            </tr>
        </thead>
    </table>
    @endcan
    @endcomponent

</section>
<div class="modal fade user_modal" tabindex="-1" role="dialog"
aria-labelledby="gridSystemModalLabel">
</div>
<!-- /.content -->
@stop @section('javascript')
<script type="text/javascript">
    var data = {!! json_encode($inutils)??[] !!};

    $(document).ready(function () {
            // $('body').addClass('sidebar-collapse');
            var inutilizacao_table = $('#inutilizacao_table').DataTable({
                buttons: [
                {
                    extend: 'print',
                    text: 'Imprimir <i class="fa fa-print"></i>'
                }
                ],
                data: data,
                columns: [
                {data: 'nNFIni'},
                {data: 'nNFFin'},
                {data: 'serie'},
                {data: 'tpAmb'},
                {data: 'modelo'},
                {
                    data: null, render: function (value) {
                        if(value.status=='novo'){
                            return '<button type="submit" class="btn btn-xs btn-primary"> <i></i>Novo</button>';
                        }else if(value.status=='aprovado'){
                            return '<button type="button" class="btn btn-xs btn-success " data-type="add">Aprovado</button>';
                        }
                    }
                },
                {data: 'criado'},
                {
                    data: null, render: function (value) {
                        if(value.status=='aprovado'){
                            return '<a href="/inutilizacao/'+value.id+'/edit"class="btn btn-xs btn-info" data-type="add">Ver</a>';
                        }else{
                            return '<a href="/inutilizacao/'+value.id+'/edit"class="btn btn-xs btn-warning" data-type="add">Editar</a>'+
                            '<a href="/inutilizacao/'+value.id+'/issue"class="btn btn-xs btn-info" data-type="add" style="margin-left: 2px;">Emitir</a>';
                        }
                    }
                },
                ]

            });
        });
    </script>
    @endsection
