@extends('layouts.app')

@section('title', __('repair::lang.view_job_sheet'))

@section('content')
    @include('repair::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>
            @lang('repair::lang.job_sheet')
            (<code>{{ $job_sheet->job_sheet_no }}
            </code>)
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-solid">
                    <div class="box-header no-print">
                        <div class="box-tools">
                            @if (auth()->user()->can('job_sheet.edit'))
                                <a href="{{ action('\Modules\Repair\Http\Controllers\JobSheetController@edit', [$job_sheet->id]) }}"
                                    class="cursor-pointer btn btn-info">
                                    <i class="fa fa-edit">

                                    </i>
                                    @lang('messages.edit')
                                </a>
                            @endif
                            <button type="button" class="btn btn-primary" aria-label="Print" id="print_jobsheet">
                                <i class="fa fa-print">

                                </i>
                                @lang('repair::lang.print_format_1')
                            </button>

                            <a class="btn btn-success"
                                href="{{ action('\Modules\Repair\Http\Controllers\JobSheetController@print', ['id' => $job_sheet->id]) }}"
                                target="_blank">
                                <i class="fas fa-file-pdf">

                                </i>
                                @lang('repair::lang.print_format_2')
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        {{-- business address --}}
                        {{-- <div class="width-100">
                            <div class="width-50 f-left" style="padding-top: 40px;">
                                @if (!empty(Session::get('business.logo')))
                                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                        alt="Logo" style="width: auto; max-height: 90px; margin: auto;" />
                                @endif
                            </div>
                        </div> --}}
                        {{-- Job sheet details --}}

                        <div class="container-fluid invoice-container">
                            <div id="job_sheet">
                                <header style="margin-top: 30px;">
                                    <div class="row align-items-center" style="margin-bottom: 30px;">
                                        <div class="mb-3 text-left col-xs-7 text-xs-start mb-xs-0">
                                            <div class="row">
                                                <div class="col-xs-3">
                                                    @if (!empty(Session::get('business.logo')))
                                                        <img id="logo" height="80" width="80"
                                                            src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                                            title="Logomarca" alt="Logomarca" />
                                                    @else
                                                        <img id="logo" height="80" width="80"
                                                            src="https://privatevisualsistemas.com//uploads/logo.png"
                                                            title="Logomarca" alt="Logomarca" />
                                                    @endif
                                                </div>
                                                <div class="col-xs-9">
                                                    {{-- empresa --}}
                                                    <div class="row">
                                                        <div class="col-xs-12">
                                                            <strong>
                                                                @lang('business.business_name'):
                                                            </strong>
                                                            <span>
                                                                {{ $job_sheet->businessLocation->name }}
                                                            </span>
                                                            <br>
                                                        </div>
                                                        <div class="col-xs-12">
                                                            <strong>
                                                                @lang('business.address'):
                                                            </strong>
                                                            <span>
                                                                {{ $job_sheet->businessLocation->getFormatedAddressAttribute() }}
                                                            </span>
                                                        </div>
                                                        <div class="col-xs-12">
                                                            <strong>
                                                                @lang('business.mobile'):
                                                            </strong>
                                                            <span>
                                                                {{ $job_sheet->businessLocation->mobile }}
                                                            </span>
                                                        </div>
                                                        <div class="col-xs-12">
                                                            <strong>
                                                                @lang('business.email'):
                                                            </strong>
                                                            <span>
                                                                {{ $job_sheet->businessLocation->email }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    {{-- empresa --}}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-right col-xs-5">
                                            <h4 class="m-0">
                                                @lang('repair::lang.repair')
                                            </h4>
                                            <p class="mb-0">
                                                @lang('repair::lang.job_sheet_no'):
                                                {{ $job_sheet->job_sheet_no }}
                                            </p>
                                            <h5 class="mb-0">
                                                {{-- @lang('sale.status'): --}}
                                                {{ $job_sheet->status->name ?? 'Aguardando' }}
                                            </h5>
                                        </div>
                                    </div>
                                    {{-- <hr> --}}
                                </header>
                                <!-- Main Content -->
                                <main>
                                    {{-- cliente --}}
                                    <div class="row">
                                        <div class="border col-xs-12">
                                            <strong>
                                                @lang('contact.name'):
                                            </strong>
                                            <span>
                                                {{ $job_sheet->customer->name }}
                                            </span>
                                        </div>
                                        <div class="border col-xs-12">
                                            <strong>
                                                @lang('business.address'):
                                            </strong>
                                            <span>
                                                {{ $job_sheet->customer->getFormatedAddressAttribute() }}
                                            </span>
                                        </div>
                                        <div class="border col-xs-6">
                                            <strong>
                                                @lang('business.mobile'):
                                            </strong>
                                            <span>
                                                {{ $job_sheet->customer->mobile }} -
                                                {{ $job_sheet->customer->tax_number }}
                                            </span>
                                        </div>
                                        <div class="border text-right col-xs-6">
                                            <strong>
                                                @lang('business.email'):
                                            </strong>
                                            <span>
                                                {{ $job_sheet->customer->email }}
                                            </span>
                                        </div>
                                    </div>
                                    {{-- cliente --}}
                                    {{-- O.S --}}
                                    @if (!empty($job_sheet->pick_up_on_site_addr))
                                        <div class="row">
                                            <div class="border col-xs-12">
                                                <strong>
                                                    @lang('repair::lang.pick_up_on_site_addr')
                                                </strong>
                                                <br />
                                                {{ $job_sheet->pick_up_on_site_addr }}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row">
                                        <div class="border col-xs-4">
                                            <strong>
                                                @lang('repair::lang.service_type'):
                                            </strong>
                                            <br />
                                            <span>
                                                @lang('repair::lang.' . $job_sheet->service_type)
                                            </span>
                                        </div>
                                        <div class="border text-right col-xs-4">
                                            <strong>
                                                @lang('receipt.date')
                                            </strong>
                                            <br />
                                            <span>
                                                {{ @format_datetime($job_sheet->created_at) }}
                                            </span>
                                        </div>
                                        <div class="border text-right col-xs-4">
                                            <strong>
                                                @lang('repair::lang.expected_delivery_date'):
                                            </strong>
                                            <br />
                                            <span>
                                                {{ @format_datetime($job_sheet->delivery_date) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="border col-xs-3">
                                            <strong>
                                                @lang('product.brand')
                                            </strong>
                                            {{-- <br> --}}
                                            <span>
                                                {{ $job_sheet->brand->name ?? 'N/A' }}
                                            </span>
                                            <br>
                                        </div>
                                        <div class="border text-center col-xs-3">
                                            <strong>
                                                @lang('repair::lang.device')
                                            </strong>
                                            {{-- <br> --}}
                                            <span>
                                                {{ $job_sheet->device->name ?? 'N/A' }}
                                            </span>
                                            <br>
                                        </div>
                                        <div class="border text-center col-xs-3">
                                            <strong>
                                                @lang('repair::lang.device_model')
                                            </strong>
                                            {{-- <br> --}}
                                            <span>
                                                {{ $job_sheet->deviceModel->name ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <div class="border text-right col-xs-3">
                                            <strong>
                                                @lang('repair::lang.serial_no_abreviated')
                                            </strong>
                                            {{-- <br> --}}
                                            <span>
                                                {{ $job_sheet->serial_no }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="border col-xs-6">
                                            <strong>
                                                @lang('repair::lang.product_configuration'):
                                            </strong>
                                            <br />
                                            {{ $job_sheet->product_configuration ?? 'Nada Consta' }}
                                        </div>
                                        <div class="border col-xs-6">
                                            <strong>
                                                @lang('repair::lang.problem_reported_by_customer'):
                                            </strong>
                                            <br />
                                            {{ $job_sheet->defects ?? 'Nada Consta' }}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="border col-xs-6">
                                            <strong>
                                                @lang('repair::lang.condition_of_product'):
                                            </strong>
                                            <br />
                                            {{ $job_sheet->product_condition ?? 'Nada Consta' }}
                                        </div>
                                        @if (!empty($repair_settings['job_sheet_custom_field_1']))
                                            <div class="border col-xs-6">
                                                <strong>
                                                    {{ $repair_settings['job_sheet_custom_field_1'] }}:
                                                </strong>
                                                <br />
                                                {{ $job_sheet->custom_field_1 ?? 'Nada Consta' }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="row">
                                        @if (!empty($repair_settings['job_sheet_custom_field_2']))
                                            <div class="border col-xs-6">
                                                <strong>
                                                    {{ $repair_settings['job_sheet_custom_field_2'] }}:
                                                </strong>
                                                <br />
                                                {{ $job_sheet->custom_field_2 ?? 'Nada Consta' }}
                                            </div>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_3']))
                                            <div class="border col-xs-6">
                                                <strong>
                                                    {{ $repair_settings['job_sheet_custom_field_3'] }}:
                                                </strong>
                                                <br />
                                                {{ $job_sheet->custom_field_3 ?? 'Nada Consta' }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="row">
                                        @if (!empty($repair_settings['job_sheet_custom_field_4']))
                                            <div class="border col-xs-6">
                                                <strong>
                                                    {{ $repair_settings['job_sheet_custom_field_4'] }}:
                                                </strong>
                                                <br />
                                                {{ $job_sheet->custom_field_4 ?? 'Nada Consta' }}
                                            </div>
                                        @endif
                                        @if (!empty($repair_settings['job_sheet_custom_field_5']))
                                            <div class="border col-xs-6">
                                                <strong>
                                                    {{ $repair_settings['job_sheet_custom_field_5'] }}:
                                                </strong>
                                                <br />
                                                {{ $job_sheet->custom_field_5 ?? 'Nada Consta' }}
                                            </div>
                                        @endif
                                    </div>


                                    <div class="row">
                                        <div class="border col-xs-12">
                                            <strong>
                                                @lang('repair::lang.services_performed'):
                                            </strong>
                                            <br />
                                            {{ $job_sheet->services_performed ?? 'Nada Consta' }}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="border col-xs-8">
                                            <strong>
                                                @lang('repair::lang.comment_by_ss'):
                                            </strong>
                                            <br />
                                            {{ $job_sheet->comment_by_ss ?? 'Nada Consta' }}
                                        </div>
                                        <div
                                            class="@empty($job_sheet->security_pattern) col-xs-4 @else col-xs-2 @endempty border text-right">
                                            <strong>
                                                Pin
                                            </strong>
                                            <br>
                                            <span>
                                                {{ $job_sheet->security_pwd ?? 'N/A' }}
                                            </span>
                                        </div>
                                        @if (!empty($job_sheet->security_pattern))
                                            <div class="border text-right col-xs-2">
                                                <strong>
                                                    Padrão
                                                </strong>
                                                <br>
                                                <div
                                                    style="line-height: 18px; letter-spacing: 6px; font-size: 1.3em; font-family: monospace; font-weight: 600;">
                                                    <?php
                                                    $padrao = '';
                                                    $password = str_split($job_sheet->security_pattern ?? '');
                                                    $first = $password[0] ?? '';

                                                    for ($linha = 1; $linha <= 3; $linha++) {
                                                        for ($coluna = 1; $coluna <= 3; $coluna++) {
                                                            $posicao = ($linha - 1) * 3 + $coluna;
                                                            $sequencia = array_search($posicao, $password) + 1;
                                                            if (in_array($posicao, $password)) {
                                                                $padrao .= "<span class=\"text-success\">$sequencia</span>";
                                                            } else {
                                                                $padrao .= "<span class=\"text-muted\">0</span>";
                                                            }
                                                        }
                                                        $padrao .= "\n";
                                                    }

                                                    echo nl2br($padrao);
                                                    ?>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- O.S --}}
                                    <br>
                                    @if (count($job_sheet->sheet_lines))
                                        <div class="card">
                                            <div class="card-header">
                                                <span class="fw-600 text-4">
                                                    @lang('report.products')
                                                </span>
                                            </div>
                                            <div class="p-0 card-body">
                                                <div class="table-responsive table-bordered">
                                                    <table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>
                                                                    <strong>
                                                                        Nome
                                                                    </strong>
                                                                </th>
                                                                <th class="text-right">
                                                                    <strong>
                                                                        Quantidade
                                                                    </strong>
                                                                </th>
                                                                <th class="text-right">
                                                                    <strong>
                                                                        Tipo
                                                                    </strong>
                                                                </th>
                                                                <th class="text-right">
                                                                    <strong>
                                                                        R$ Unit.
                                                                    </strong>
                                                                </th>
                                                                <th class="text-right">
                                                                    <strong>
                                                                        R$ Total
                                                                    </strong>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (!empty($job_sheet->sheet_lines))
                                                                @foreach ($job_sheet->sheet_lines as $part)
                                                                    <tr>
                                                                        {{-- <td>
                                                                    {{ dd($part->toArray()) }}
                                                                </td> --}}
                                                                        <td class="text-left">
                                                                            {{ mb_strtoupper($part->product->name) }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ $part->quantity }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            {{ $part->product->unit->short_name }}
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <span class="display_currency"
                                                                                data-currency_symbol="true">
                                                                                {{ $part->unit_price_inc_tax }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="text-right">
                                                                            <span class="display_currency"
                                                                                data-currency_symbol="true">
                                                                                {{ $part->quantity * $part->unit_price_inc_tax }}
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </main>
                                <div style="margin-top: 30px;">
                                    <div class="row">
                                        <div class="text-right col-xs-9">
                                            <strong>
                                                @lang('repair::lang.estimated_cost'):
                                            </strong>
                                        </div>
                                        <div class="text-right col-xs-3">
                                            <span class="display_currency" data-currency_symbol="true">
                                                {{ $job_sheet->estimated_cost }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="text-right col-xs-9">
                                            <strong>
                                                @lang('repair::lang.service_costs'):
                                            </strong>
                                        </div>
                                        <div class="text-right col-xs-3">
                                            <span class="display_currency" data-currency_symbol="true">
                                                {{ $job_sheet->service_costs }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="text-right col-xs-9">
                                            <strong>
                                                @lang('sale.total_amount'):
                                            </strong>
                                        </div>
                                        <div class="text-right col-xs-3">
                                            <span class="display_currency" data-currency_symbol="true">
                                                {{ $job_sheet->total_costs }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                @if (!empty($repair_settings['repair_tc_condition']))
                                    <div style="margin-top: 30px;">
                                        <div class="row">
                                            <div class="text-sm text-justify col-xs-12 text-muted">
                                                <strong>@lang('lang_v1.terms_conditions'):</strong>
                                                {!! $repair_settings['repair_tc_condition'] !!}
                                            </div>
                                            <div class="text-justify col-xs-12 text-muted">
                                                <table class="table-pdf">
                                                    <tr>
                                                        <td class="px-2" width="33%">
                                                            <hr class="mt-16" />
                                                        </td>
                                                        <td class="px-2" width="33%">
                                                            <hr class="mt-16" />
                                                        </td>
                                                        <td class="px-2" width="33%">
                                                            <hr class="mt-16" />
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-center">
                                                            @lang('repair::lang.customer_signature'):
                                                        </td>
                                                        <td class="text-center">
                                                            @lang('repair::lang.authorized_signature'):
                                                        </td>
                                                        <td class="text-center">
                                                            @lang('repair::lang.technician'):
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Job sheet details --}}
                    </div>
                </div>
            </div>
        </div>
        <div class="mx-auto row">
            @if ($job_sheet->media->count() > 0)
                <div class="col-md-6">
                    <div class="box box-solid no-print">
                        <div class="box-header with-border">
                            <h4 class="box-title">
                                @lang('repair::lang.uploaded_image_for', ['job_sheet_no' => $job_sheet->job_sheet_no])
                            </h4>
                        </div>
                        <div class="box-body">
                            @includeIf('repair::job_sheet.partials.document_table_view', [
                                'medias' => $job_sheet->media,
                            ])
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-md-6">
                <div class="box box-solid no-print">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ __('repair::lang.activities') }}:</h3>
                    </div>
                    <!-- /.box-header -->
                    @include('repair::repair.partials.activities')
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
@stop
@section('css')
    <style type="text/css">
        .table-bordered>tbody>tr>td,
        .table-bordered>tbody>tr>th,
        .table-bordered>tfoot>tr>td,
        .table-bordered>tfoot>tr>th,
        .table-bordered>thead>tr>td,
        .table-bordered>thead>tr>th {
            border: 1px solid #ddd !important;
        }

        /* .border {
            border: 1px solid #ddd !important;
        } */

        hr {
            border-top: 1px solid #ddd !important;
        }

        .card {}

        .card-header {
            background-color: #eee;
            padding: .75rem .75rem .75rem .75rem;
            border: 1px solid #ddd !important;
        }

        /* =================================== */
        /*  Helpers Classes
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    /* =================================== */
        /* Border Radius */
        .rounded-top-0 {
            border-top-left-radius: 0px !important;
            border-top-right-radius: 0px !important;
        }

        .rounded-bottom-0 {
            border-bottom-left-radius: 0px !important;
            border-bottom-right-radius: 0px !important;
        }

        .rounded-left-0 {
            border-top-left-radius: 0px !important;
            border-bottom-left-radius: 0px !important;
        }

        .rounded-right-0 {
            border-top-right-radius: 0px !important;
            border-bottom-right-radius: 0px !important;
        }


        @media print {

            .table td,
            .table th {
                background-color: transparent !important;
            }

            .table td.bg-light,
            .table th.bg-light {
                background-color: #FFF !important;
            }

            .table td.bg-light-1,
            .table th.bg-light-1 {
                background-color: #f9f9fb !important;
            }

            .table td.bg-light-2,
            .table th.bg-light-2 {
                background-color: #f8f8fa !important;
            }

            .table td.bg-light-3,
            .table th.bg-light-3 {
                background-color: #f5f5f5 !important;
            }

            .table td.bg-light-4,
            .table th.bg-light-4 {
                background-color: #eff0f2 !important;
            }

            .table td.bg-light-5,
            .table th.bg-light-5 {
                background-color: #ececec !important;
            }
        }

        /* =================================== */
        /*  Layouts
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            /* =================================== */
        .invoice-container {
            margin: 15px auto;
            padding: 25px 50px;
            max-width: 850px;
            background-color: #fff;
            border: 1px solid #ccc;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            -o-border-radius: 6px;
            border-radius: 6px;
        }
    </style>
@stop
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#print_jobsheet').click(function() {
                $('#job_sheet').printThis({
                    importStyle: true
                });
            });
            $(document).on('click', '.delete_media', function(e) {
                e.preventDefault();
                var url = $(this).data('href');
                var this_btn = $(this);
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((confirmed) => {
                    if (confirmed) {
                        $.ajax({
                            method: 'GET',
                            url: url,
                            dataType: 'json',
                            success: function(result) {
                                if (result.success == true) {
                                    this_btn.closest('tr').remove();
                                    toastr.success(result.msg);
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
@stop
