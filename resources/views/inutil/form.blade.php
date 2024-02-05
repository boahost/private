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
                        <input @if(isset($item))
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
                        <input @if(isset($item))
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
                        <input @if(isset($item))
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
                        <select @if(isset($item)) @if($item->status=='aprovado')disabled
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
                        <input @if(isset($item))
                               @if($item->status=='aprovado')
                               disabled
                               @endif  value="{{$item->xJust??''}}"
                               @else
                               value="{{old('xJust')}}"
                               @endif
                               type="text"
                               name="xJust"
                               class="form-control"
                               id="xJust" required minlength="15"
                        >
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">

                        <input
                            class="btn btn-primary pull-right @if(isset($item)) @if($item->status=='aprovado') d-none @endif @endif"
                            style="margin-right:20px"
                            type="submit"
                            id="salvar"
                            value="salvar"
                        >

                       <!--  @if(isset($item))
                            <button id="emitirInutilizacao"
                                    type="button"
                                    class="btn btn-danger pull-right margin-r-5 @if(isset($item)) @if($item->status=='aprovado') d-none @endif @endif"
                                    id="emitir">Emitir
                            </button>
                        @endif
                        @if(isset($item))
                            <a class="btn btn-success pull-right @if($item->status!='aprovado') d-none @endif"
                               id="download"
                               href="/inutilizar/{{$item->id}}.xml">Download</a>
                        @endif -->


                    </div>
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

                //desabilita todos os campos para emitir a inutilização
                $('#emitirInutilizacao').attr('disabled', true);
                $('#nNFIni').attr('disabled', true);
                $('#nNFFin').attr('disabled', true);
                $('#serie').attr('disabled', true);
                $('#modelo').attr('disabled', true);
                $('#status').attr('disabled', true);
                $('#xJust').attr('disabled', true);
                $('#emitirInutilizacao').html('<i class="fas fa-spinner fa-pulse fa-spin fa-fw"></i> Emitindo');

                //envia para a api para fazer a inutilização com os campos
                axios.post('{{getenv('API_DFE_URL')}}/api/inutilizar', {
                    nNFIni: $('#nNFIni').val(),
                    nNFFin: $('#nNFFin').val(),
                    serie: $('#serie').val(),
                    ambiente: {{request()->user()->business->ambiente}},
                    modelo: $('#modelo').val(),
                    status: $('#status').val(),
                    xJust: $('#xJust').val(),
                    cUF: '{{request()->user()->business->getcUF(request()->user()->business->cidade->uf)}}',
                    cnpj: '{{str_replace(['.', '-', '/', ''], '', request()->user()->business->cnpj)}}',
                })
                    .then(function (response) {

                        //em caso de sucesso o mesmo e manda os dados aprovados para o banco de dados  e notifica na tela
                        if (response.data.success) {

                            axios.put('/inutilizacao/{{$item->id??''}}', {
                                nNFIni: $('#nNFIni').val(),
                                nNFFin: $('#nNFFin').val(),
                                serie: $('#serie').val(),
                                modelo: $('#modelo').val(),
                                status: $('#status').val(),
                                xJust: $('#xJust').val(),
                                jsonFile: response.data.message
                            })
                                .then(function (response) {
                                    //em caso de sucesso o mesmo notitica na tela que tudo deu certo
                                    toastr.success('Nota aprovada com sucesso');
                                    console.log(response);
                                    //tira o botão de emissão e de salvar da tela
                                    $('#emitirInutilizacao').attr('disabled', false);
                                    $('#emitirInutilizacao').html('Emitir');
                                    $('#emitirInutilizacao').addClass('d-none');
                                    $('#emitirInutilizacao').addClass('d-none');
                                    $('#salvar').addClass('d-none');

                                    //e motra o botão de download
                                    $('#download').removeClass('d-none');
                                })
                                .catch(function (error) {
                                    //notifica erro salvar
                                    toastr.error('Erro ao salvar...');
                                    console.log(error);

                                    //retira a desabilitação dos inputs
                                    $('#emitirInutilizacao').attr('disabled', false);
                                    $('#emitirInutilizacao').html('Emitir');
                                    $('#nNFIni').attr('disabled', false);
                                    $('#nNFFin').attr('disabled', false);
                                    $('#serie').attr('disabled', false);
                                    $('#modelo').attr('disabled', false);
                                    $('#status').attr('disabled', false);
                                    $('#xJust').attr('disabled', false);
                                });
                        } else {
                            //notifica o erro da API
                            toastr.error(JSON.stringify(response.data), {timeOut: 12000});
                            console.log(response);

                            //retira a desabilitação dos inputs
                            $('#emitirInutilizacao').attr('disabled', false);
                            $('#emitirInutilizacao').html('Emitir');
                            $('#nNFIni').attr('disabled', false);
                            $('#nNFFin').attr('disabled', false);
                            $('#serie').attr('disabled', false);
                            $('#modelo').attr('disabled', false);
                            $('#status').attr('disabled', false);
                            $('#xJust').attr('disabled', false);
                        }
                    })
                    .catch(function (error) {
                        console.log(error);

                        //notifica o erro de transmissão
                        toastr.error('Erro ao emitir');
                        $('#emitirInutilizacao').attr('disabled', false);
                        $('#emitirInutilizacao').html('Emitir');
                        $('#nNFIni').attr('disabled', false);
                        $('#nNFFin').attr('disabled', false);
                        $('#serie').attr('disabled', false);
                        $('#modelo').attr('disabled', false);
                        $('#status').attr('disabled', false);
                        $('#xJust').attr('disabled', false);
                    });
            });

        });
    </script>
@endsection
@endif
