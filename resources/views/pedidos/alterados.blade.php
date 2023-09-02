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


    @component('components.widget', ['class' => 'box-primary', 'title' => 'Pedidos de Ecommerce com Alteração'])
    @can('user.create')
    @slot('tool')
    
    @endslot
    @endcan
    @can('user.view')


    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Valor total</th>
                    <th>Data</th>
                    <th>Forma de pagamento</th>

                </tr>
            </thead>
            <tbody>
                @foreach($pedidos as $p)
                <tr>
                    <td>{{$p->token}}</td>
                    <td>{{number_format($p->valor_total, 2, ',', '.')}}</td>
                    <td>{{\Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i:s')}}</td>
                    <td>{{$p->forma_pagamento}}</td>
                </tr>
                @endforeach

                @if(sizeof($pedidos) == 0)
                <tr>
                    <td colspan="4">
                        <h3 class="text-danger">Nenhum pedido sofreu alteração</h3>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endcan
    @endcomponent


</div>



</section>


<!-- /.content -->
@stop
