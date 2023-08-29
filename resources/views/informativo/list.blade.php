@extends('layouts.app')
@section('title', 'Informativo de Ecommerce')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Ecommerce
        <small>Email</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Informativo'])
    @can('user.create')
    @slot('tool')
    
    @endslot
    @endcan
    @can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>Email</th>
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
            ajax: '/informativoEcommerce',
            columnDefs: [ {
                "targets": [0],
                "orderable": false,
                "searchable": true
            } ],
            "columns":[
            {"data":"email"},
            ]
        });
    });

    function verTexto(msg){
        $('#modal-msg').modal('show')
        $('#msg').html(msg)
    }
    
    
</script>
@endsection
