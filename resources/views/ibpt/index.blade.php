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
        href="{{ route('ibpt.create') }}" >
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

                       

                        <form id="veiculos{{$i->id}}" method="POST" action="{{ route('ibpt.destroy', $i->id) }}">
                            @method('delete')
                            @csrf
                            <a class="btn" href="{{ route('ibpt.edit', [$i->id])}}">
                                <i class="fa fa-edit" aria-hidden="true"></i>
                            </a>
                            <a class="btn" href="{{ route('ibpt.list', [$i->id])}}">
                                <i class="fa fa-list text-success" aria-hidden="true"></i>
                            </a>

                            <button type="button" class="btn btn-delete">
                                <i class="fa fa-trash text-danger" aria-hidden="true"></i>
                                
                            </button>
                        </form>

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
