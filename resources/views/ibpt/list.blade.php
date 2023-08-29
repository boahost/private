@extends('layouts.app')
@section('title', 'IBPT')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>IBPT
        <small>Gerencia Tabelas</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Lista de tabelas'])
    @can('user.create')
    @slot('tool')
    <div class="box-tools">
        <a class="btn btn-block btn-primary" 
        href="/ibpt/new" >
        <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
    </div>
    @endslot
    @endcan
    @can('user.view')

    <div class="row">
        @foreach($tabelas as $i)
        <div class="col-md-6">
            <div class="card">
                <div class="card-content">
                    <h3>
                        <strong style="margin-right: 5px;" class="text-info">{{$i->uf}}</strong> {{$i->versao}} - {{ \Carbon\Carbon::parse($i->updated_at)->format('d/m/Y H:i:s')}}

                        <a onclick='swal("Atenção!", "Deseja remover este registro?", "warning").then((sim) => {if(sim){ location.href="/ibpt/delete/{{ $i->id }}" }else{return false} })' href="#!">
                            <i class="fa fa-trash text-danger"></i>
                        </a>
                        <a href="/ibpt/edit/{{$i->id}}">
                            <i class="fa fa-edit" aria-hidden="true"></i>
                        </a>
                        <a href="/ibpt/list/{{$i->id}}">
                            <i class="fa fa-list text-success" aria-hidden="true"></i>
                        </a>
                    </h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @endcan
    @endcomponent


</section>
<!-- /.content -->
@stop
@section('javascript')

@endsection
