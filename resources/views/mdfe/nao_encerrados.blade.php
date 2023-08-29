@extends('layouts.app')
@section('title', 'Documentos não encerrados MDF-e')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Documentos não encerrados MDF-e
        <small>Lista</small>
    </h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    @component('components.widget', ['class' => 'box-primary', 'title' => 'NF-e Lista'])

    @if(isset($msg) && sizeof($msg) > 0)
    @foreach($msg as $m)
    <h5 style="color: red">{{$m}}</h5>
    @endforeach
    @endif

    
    @can('user.view')

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>Local</th>
                    <th>Chave</th>
                    <th>Protocolo</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($naoEncerrados as $n)
                <tr>
                    <td>{{$n['location_id']}}</td>
                    <td>{{$n['chave']}}</td>
                    <td>{{$n['protocolo']}}</td>
                    <td>
                        <a title="Encererrar" onclick='swal("Atenção!", "Deseja encerrar este documento?", "warning").then((sim) => {if(sim){ location.href="/mdfe/encerrar/{{$n['chave']}}/{{$n['protocolo']}}/{{$n['location_id']}}" }else{return false} })' href="#!">
                            <i class="fa fa-times text-danger"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
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


</script>
@endsection
