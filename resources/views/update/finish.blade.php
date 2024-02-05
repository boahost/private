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
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Comanda Sql'])

    @foreach($logMessage as $l)
    <div class="row">
        {!! $l !!}
    </div>
    @endforeach

    @endcomponent

    <div class="modal fade unit_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

@endsection
