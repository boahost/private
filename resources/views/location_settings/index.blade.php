@extends('layouts.app')
@section('title', __('messages.business_location_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang( 'messages.business_location_settings' ) - {{$location->name}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
          <!-- Custom Tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">@lang('receipt.receipt_settings')</a></li>
                <li><a href="#tab_2" data-toggle="tab" aria-expanded="true">Certificado</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="tab_1">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>@lang('receipt.receipt_settings')
                                <small>@lang( 'receipt.receipt_settings_mgs')</small>
                            </h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::open(['url' => route('location.settings_update', [$location->id]), 'method' => 'post', 'id' => 'bl_receipt_setting_form']) !!}

                            <div class="col-sm-4">
                                <div class="form-group">
                                    {!! Form::label('print_receipt_on_invoice', 'Impressão automática após a conclusão' . ':') !!}
                                    @show_tooltip(__('tooltip.print_receipt_on_invoice'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-file-alt"></i>
                                        </span>
                                        {!! Form::select('print_receipt_on_invoice', $printReceiptOnInvoice, $location->print_receipt_on_invoice, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    {!! Form::label('receipt_printer_type', __('receipt.receipt_printer_type') . ':*') !!} @show_tooltip(__('tooltip.receipt_printer_type'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-print"></i>
                                        </span>
                                        {!! Form::select('receipt_printer_type', $receiptPrinterType, $location->receipt_printer_type, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                    @if(config('app.env') == 'demo')
                                    <span class="help-block">Only Browser based option is enabled in demo.</span>
                                    @endif

                                </div>
                            </div>

                            <div class="col-sm-4" id="location_printer_div">
                                <div class="form-group">
                                    {!! Form::label('printer_id', 'Impressoras de recibos' . ':*') !!}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-share-alt"></i>
                                        </span>
                                        {!! Form::select('printer_id', $printers, $location->printer_id, ['class' => 'form-control select2']); !!}
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <br/>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    {!! Form::label('invoice_layout_id', __('invoice.invoice_layout') . ':*') !!} @show_tooltip(__('tooltip.invoice_layout'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-info"></i>
                                        </span>
                                        {!! Form::select('invoice_layout_id', $invoice_layouts, $location->invoice_layout_id, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-3">
                                <div class="form-group">
                                    {!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':*') !!} @show_tooltip(__('tooltip.invoice_scheme'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fa fa-info"></i>
                                        </span>
                                        {!! Form::select('invoice_scheme_id', $invoice_schemes, $location->invoice_scheme_id, ['class' => 'form-control select2', 'required']); !!}
                                    </div>
                                </div>
                            </div>


                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::label('invoice_scheme_id', 'Informação complementar' . ':*') !!} @show_tooltip('Informação complementar para NFe')
                                    {!! Form::textarea('info_complementar', $location->info_complementar, ['class' => 'form-control', 'rows' => 3]); !!}

                                </div>
                            </div>




                            <div class="row">
                                <div class="col-sm-12">
                                    <button class="btn btn-primary pull-right" type="submit">@lang('messages.update')</button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab_2">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Configuração de Certificado
                            </h4>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-12">

                            {!! Form::open(['url' => route('location.settings_update_certificado', [$location->id]), 'method' => 'post', 'id' => 'bl_receipt_setting_form', 'files' => true ]) !!}


                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="certificado">Certificado:</label>
                                        <input name="certificado" type="file" id="certificado">
                                    </div>
                                </div>

                                <div class="col-sm-3">
                                    <div class="form-group">
                                        {!! Form::label('senha_certificado', 'Senha' . ':') !!}
                                        {!! Form::text('senha_certificado', '', ['class' => 'form-control',
                                        'placeholder' => 'Senha']); !!}
                                    </div>
                                </div>

                                @if($infoCertificado != null && $infoCertificado != -1)
                                <h5>Serial: <strong>{{$infoCertificado['serial']}}</strong></h5>
                                <h5>Expiração: <strong>{{$infoCertificado['expiracao']}}</strong></h5>
                                <h5>ID: <strong>{{$infoCertificado['id']}}</strong></h5>
                                @endif

                                @if($infoCertificado == -1)
                                <h5 style="color: red">Erro na leitura do certificado, verifique a senha e outros dados, e realize o upload novamente!!</h5>
                                @endif
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button class="btn btn-primary pull-right" type="submit">Salvar</button>
                        </div>
                    </div>
                    {!! Form::close() !!}

                </div>
            </div>
        </div>
    </div>
    <!-- /.tab-content -->
</div>
<!-- nav-tabs-custom -->
</div>
</div>

<div class="modal fade invoice_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade invoice_edit_modal" tabindex="-1" role="dialog" 
aria-labelledby="gridSystemModalLabel">
</div>

</section>
<!-- /.content -->

@endsection
