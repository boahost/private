@extends('layouts.app')
@section('title', 'Update')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Update
        <small>gerenciar</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Versão App ' . $system->version])

    <div class="box-tools">
        <button class="btn btn-light-primary"><i class="fa fa-paper-plane"></i> Verificar nova atualização</button>
    </div>
    <div class="row">
        <br>
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Atualizar Tabelas'])

        {!! Form::open(['url' => action('UpdateController@sql'), 'method' => 'post', 'id' => 'form', 'files' => true ]) !!}

        <div class="col-sm-3">
            <div class="form-group">
                <label for="file">Tabela:</label>
                <input required name="file" accept=".sql" type="file" id="file">
                <p>Arquivo .sql</p>
            </div>
        </div>

        <div class="col-sm-2">
            <button type="submit" class="btn btn-danger" id="submit_user_button">
                Importar Sql
            </button>
        </div>
        {!! Form::close() !!}

        @endcomponent

    </div>

    @endcomponent

    <div class="modal fade unit_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

@endsection
