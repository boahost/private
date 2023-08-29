@extends('layouts.app')
@section('title', 'Ecommerce Clientes')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Clientes
        <small>Gerencia Clientes</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Clientes Ecommerce'])
        @can('user.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    href="/clienteEcommerce/new" >
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                 </div>
            @endslot
        @endcan
        @can('user.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="client_table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Sobre nome</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>CPF/CNPJ</th>

                            <th>Ativo</th>
                            <th>Pedidos</th>
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
        var client_table = $('#client_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/clienteEcommerce',
                    columnDefs: [ {
                        "targets": [3],
                        "orderable": false,
                        "searchable": false
                    } ],
                    "columns":[
                        {"data":"nome"},
                        {"data":"sobre_nome"},
                        {"data":"telefone"},
                        {"data":"email"},
                        {"data":"cpf"},
                        {"data":"status"},
                        {"data":"pedidos"},
                        {"data":"action"}
                    ]
                });
        $(document).on('click', 'button.delete_user_button', function(){
            swal({
              title: LANG.sure,
              text: LANG.confirm_delete_user,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: "DELETE",
                        url: href,
                        dataType: "json",
                        data: data,
                        success: function(result){
                            if(result.success == true){
                                toastr.success(result.msg);
                                client_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        }
                    });
                }
             });
        });
        
    });
    
    
</script>
@endsection
