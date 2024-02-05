@extends('layouts.app')
@section('title','Inutilização')

@section('content')


<!-- Content Header (Page header) -->
<section class="content-header">

    <div class="row">
        <div class="col-lg-2">
            <h3>Inutilização</h3>
        </div>
        <div class="col-lg-2 col-lg-offset-8">
            <a class="btn btn-primary pull-right"
            href="{{action('InutilController@index')}}">Voltar</a>
        </div>
    </div>
</section>
<!-- Main content -->

@component('components.widget', ['class' => 'box-primary'])
<section class="content">


    <form class="row" id="formResource" name="formResource"
    @if(isset($item))
    action="{{action('InutilController@update',$item->id)}}"
    method="post">
    @method('PUT')
    @else
    action="{{action('InutilController@store')}}"
    method="post">
    @endif

    @csrf
    <div class="col-sm-4">
        <div class="form-group">
            <label for="nNFIni">Número de Ínicio</label>
            <input disabled @if(isset($item))
            @if($item->status=='aprovado')
            disabled
            @endif
            value="{{$item->nNFIni??''}}"
            @else
            value="{{old('nNFIni')}}"
            @endif

            type="number"
            name="nNFIni"
            min="1"
            max="999999999"
            class="form-control"
            id="nNFIni"
            required
            >
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label for="nNFFin">Número de Fim</label>
            <input disabled @if(isset($item))
            @if($item->status=='aprovado')
            disabled
            @endif
            @else
            value="{{old('nNFFin')}}"
            @endif
            value="{{$item->nNFFin??''}}"
            type="number"
            name="nNFFin"
            min="1"
            max="999999999"
            class="form-control"
            id="nNFFin"
            required
            >
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label for="">Serie</label>
            <input disabled @if(isset($item))
            @if($item->status=='aprovado')
            disabled
            @endif
            value="{{$item->serie??''}}"
            @else
            value="{{old('serie')}}"
            @endif
            type="number"
            name="serie"
            min="1"
            max="999"
            class="form-control"
            id="serie"
            required
            >
        </div>
    </div>

    <div class="col-sm-4">
        <div class="form-group">
            <label for="">Modelo</label>
            <select disabled @if(isset($item)) @if($item->status=='aprovado')disabled
                @endif @endif  name="modelo" class="form-control" id="modelo">
                <option @if(isset($item))
                {{$item->modelo==55?'selected':''}}
                @endif value="55">
                NF-e
            </option>
            <option disabled class="d-none" @if(isset($item)){{$item->modelo==65?'selected':''}}@endif value="65">
                NFC-e
            </option>
        </select>
    </div>
</div>

<div class="col-sm-8">
    <div class="form-group">
        <label for="">Justificativa</label>
        <input disabled @if(isset($item))
        @if($item->status=='aprovado')
        disabled
        @endif  value="{{$item->xJust??''}}"
        @else
        value="{{old('xJust')}}"
        @endif
        type="text"
        name="xJust"
        class="form-control"
        id="xJust"
        required
        >
    </div>
</div>

<div class="col-sm-12">


    @if(isset($item))
    <button id="emitirInutilizacao" type="button" class="btn btn-danger pull-right @if(isset($item)) @if($item->status=='aprovado') d-none @endif @endif"
        id="emitir">
        <i class="fas spinner"></i>Emitir
    </button>
    @endif
    @if($item->status == 'aprovado')
    <a style="margin-right: 3px;" class="btn btn-success pull-right @if($item->status!='aprovado') d-none @endif" id="download" href="/inutilizar/{{$item->id}}/download">Download</a>
    @endif
</div>

</form>
</section>
@endcomponent
<!-- /.content -->
@stop
@if(isset($item))
@section('javascript')

<script>
    $(document).ready(function () {
        $('#emitirInutilizacao').click(function () {
            console.clear()
            $('#emitirInutilizacao').attr('disabled', true);
            $('.spinner').addClass('fa-spinner fa-pulse fa-spin fa-fw');
            var path = window.location.protocol + '//' + window.location.host

            $.ajax
            ({
                type: 'POST',
                data: {
                    id: {{$item->id}},
                    _token: '{{csrf_token()}}'
                },
                url: path + '/inutilizacao/issue',
                dataType: 'json',
                success: function(e){
                    $('#emitirInutilizacao').removeAttr('disabled');
                    $('.spinner').removeClass('fa-spinner fa-pulse fa-spin fa-fw');
                    console.log(e)

                    swal("Erro", "["+e.infInut.cStat+"] " + e.infInut.xMotivo, "success")


                }, error: function(e){
                    $('#emitirInutilizacao').removeAttr('disabled');
                    $('.spinner').removeClass('fa-spinner fa-pulse fa-spin fa-fw');

                    e = e.responseJSON
                    console.log(e)

                    try{
                        swal("Erro", "["+e.infInut.cStat+"] " + e.infInut.xMotivo, "error")
                    }catch{
                        swal("Erro", "Algo deu errado!", "error")
                    }
                }

            });

        });
    });
</script>
@endsection
@endif
