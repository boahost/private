@extends('layouts.app')
@section('title', 'Lista de Naturezas')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>IBPT
        <small>Gerencia {{$ibpt->uf}}</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'IBPT '.$ibpt->uf])

    @can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Nacional/Federal</th>
                    <th>Importado/Federal</th>
                    <th>Estadual</th>
                    <th>Municipal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($itens as $i)
                <tr>
                    <td>{{$i->codigo}}</td>
                    <td>{{$i->descricao}}</td>
                    <td>{{$i->nacional_federal}}</td>
                    <td>{{$i->importado_federal}}</td>
                    <td>{{$i->estadual}}</td>
                    <td>{{$i->municipal}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="dataTables_paginate paging_simple_numbers" id="sell_table_paginate">
            <ul class="pagination">
                <li class="paginate_button previous" id="sell_table_previous">
                    {{$itens->links()}}
                </li>
                
            </ul>
        </div>
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

@endsection
