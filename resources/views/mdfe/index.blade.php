@extends('layouts.app')
@section('title', 'Lista de MDFe')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        <small>Gerencia MDFe</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Todos os Documentos'])
    @can('user.create')
    @slot('tool')

    <div class="box-tools">
        <a style="margin-left: 4px;" class="btn btn-block btn-primary" 
        href="{{ route('mdfe.create')}}" >
        <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
    </div>
    <div class="box-tools">
        <a class="btn btn-block btn-danger" 
        href="{{ route('mdfe.nao-encerrados')}}" >
        <i class="fa fa-list"></i> Documentos não encerrados</a>
    </div>

    @endslot
    @endcan
    @can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>UF Início</th>
                    <th>UF Fim</th>
                    <th>Data início da viagem</th>
                    <th>Condutor</th>
                    <th>Valor da carga</th>
                    <th>Quantidade da carga</th>
                    <th>Veículo de tração</th>
                    <th>Estado</th>
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
            ajax: {
                'url': '/mdfe',
                error: function(err){
                    console.log(err)
                }
            },
            columnDefs: [ {
                "targets": [4],
                "orderable": false,
                "searchable": false
            } ],
            "columns":[
            {"data":"uf_inicio"},
            {"data":"uf_fim"},
            {"data":"data_inicio_viagem"},
            {"data":"condutor_nome"},
            {"data":"valor_carga"},
            {"data":"quantidade_carga"},
            {"data":"veiculo"},
            {"data":"estado"},
            {"data":"action"}
            ]
        });

        
    });
    
    
</script>
@endsection
