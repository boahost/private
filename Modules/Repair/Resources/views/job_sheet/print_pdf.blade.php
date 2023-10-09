<link rel="stylesheet" href="{{ asset('css/app.css?v=' . $asset_v) }}">
<style type="text/css">
    .box {
        border: 1px solid;
    }

    .table-pdf {
        width: 100%;
    }

    .table-pdf td,
    .table-pdf th {
        padding: 6px;
        /* text-align: unset; */
    }

    .text-start {
        text-align: start;
    }

    .w-20 {
        width: 20%;
        float: left;
    }

    .checklist {
        padding: 5px 15px;
        width: 100%;
    }

    .checkbox {
        width: 20%;
        float: left;
    }

    .checkbox-text {
        width: 80%;
        float: left;
    }

    .content-div {
        padding: 6px;
    }

    .table-slim {
        width: 100%;
    }

    .table-slim td,
    .table-slim th {
        padding: 1px !important;
        /* font-size: 12px; */
    }

    .font-14 {
        /* font-size: 14px; */
    }

    .font-12 {
        /* font-size: 12px; */
    }

    body,
    table {
        font-size: 12px;
    }
</style>
{{-- @dd('asd'); --}}
@php
    // \Log::debug('message', [$job_sheet]);
@endphp
<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <tr>
            <th width="80" class="text-start">
                @if (!empty(Session::get('business.logo')))
                    <img id="logo" height="80" width="80"
                        src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}" title="Logomarca"
                        alt="Logomarca" />
                @else
                    <img id="logo" height="80" width="80"
                        src="https://privatevisualsistemas.com//uploads/logo.png" title="Logomarca" alt="Logomarca" />
                @endif
            </th>
            <th colspan="3" class="text-start">
                <strong class="font-14">
                    {{ $job_sheet->customer->business->name }}
                </strong>
                <br>
                <span class="font-12">
                    {!! $job_sheet->businessLocation->name !!} <br>
                    {!! $job_sheet->businessLocation->location_address !!}
                    @if (!empty($job_sheet->businessLocation->mobile))
                        <br>
                        @lang('business.mobile'): {{ $job_sheet->businessLocation->mobile }},
                    @endif
                    @if (!empty($job_sheet->businessLocation->alternate_number))
                        @lang('invoice.show_alternate_number'): {{ $job_sheet->businessLocation->alternate_number }},
                    @endif
                    @if (!empty($job_sheet->businessLocation->email))
                        <br>
                        @lang('business.email'): {{ $job_sheet->businessLocation->email }},
                    @endif

                    @if (!empty($job_sheet->businessLocation->website))
                        @lang('lang_v1.website'): {{ $job_sheet->businessLocation->website }}
                    @endif
                </span>
            </th>
        </tr>
    </table>
</div>
<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <tr>
            <th class="text-start">@lang('receipt.date'):</th>
            <th class="text-start">@lang('repair::lang.service_type'):</th>
            <th class="text-start">@lang('repair::lang.job_sheet_no'):</th>
            <th rowspan="2">
                <img
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($job_sheet->job_sheet_no, 'C128', 1, 50, [39, 48, 10], true) }}">
            </th>
            <th class="text-start">@lang('repair::lang.expected_delivery_date'):</th>
        </tr>
        <tr>
            <td style="padding-top: -8">{{ @format_datetime($job_sheet->created_at) }}</td>
            <td style="padding-top: -8">@lang('repair::lang.' . $job_sheet->service_type)</td>
            <td style="padding-top: -8">{{ $job_sheet->job_sheet_no }}</td>
            <td style="padding-top: -8">{{ @format_datetime($job_sheet->delivery_date) }}</td>
        </tr>
    </table>
</div>
<div class="box mb-10">
    <table class="table-pdf">
        <tr>
            <td>
                <b>@lang('role.customer'):</b>
                <br />
                <span>{{ $job_sheet->customer->name }}</span>
            </td>
            <td>
                <b>@lang('product.brand'):</b>
                <br />
                <span>{{ optional($job_sheet->brand)->name ?? 'N/A' }}</span>
            </td>
            <td colspan="2">
                <b>@lang('repair::lang.device'):</b>
                <br />
                <span>{{ optional($job_sheet->device)->name ?? 'N/A' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <b>@lang('contact.mobile'):</b>
                <br />
                <span>{{ $job_sheet->customer->mobile ?: 'N/A' }}</span>
            </td>
            <td>
                <b>@lang('repair::lang.device_model'):</b>
                <br />
                <span>{{ optional($job_sheet->deviceModel)->name ?? 'N/A' }}</span>
            </td>
            <td>
                <b>@lang('lang_v1.password'):</b>
                <br />
                <span>{{ $job_sheet->security_pwd ?? 'N/A' }}</span>
            </td>
            <td rowspan="2">
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
                                        $padrao .= "<span style='color: #16c316;'>$sequencia</span>";
                                    } else {
                                        $padrao .= "<span style='color: #9d9d9d;'>0</span>";
                                    }
                                }
                                $padrao .= "\n";
                            }

                            echo nl2br($padrao);
                            ?>
                        </div>
                    </div>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <b>@lang('business.address'):</b>
                <br />
                <span>{{ $job_sheet->customer->getFormatedAddressAttribute() }}</span>
            </td>
            <td>
                <b>@lang('repair::lang.serial_no'):</b>
                <br />
                <span>{{ $job_sheet->serial_no }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <b>@lang('business.email'):</b>
                <br />
                <span>{{ $job_sheet->customer->email }}</span>
            </td>
            <td>
                <b>@lang('sale.invoice_no'):</b>
                <br />
                <span>
                    {{ join(', ', $job_sheet->invoices->pluck('invoice_no')->toArray()) }}
                </span>
            </td>
            <td>
                <b>@lang('repair::lang.estimated_cost'):</b>
                <br />
                <span>@format_currency($job_sheet->estimated_cost)</span>
            </td>
            <td>
                <b>@lang('sale.status'):</b>
                <br />
                <span>{{ optional($job_sheet->status)->name }}</span>
            </td>
        </tr>
    </table>
</div>
<div class="box mb-10">
    <div class="width-100 content-div">
        {{-- <div>
            <div class="width-100">
                <strong>@lang('repair::lang.pre_repair_checklist'):</strong>
            </div>
            @php
                $checklists = [];
                if (!empty($job_sheet->deviceModel) && !empty($job_sheet->deviceModel->repair_checklist)) {
                    $checklists = explode('|', $job_sheet->deviceModel->repair_checklist);
                }
            @endphp
            @if (!empty($checklist))
                <div class="width-100">
                    @foreach ($checklists as $check)
                        @php
                            if (!isset($job_sheet->checklist[$check])) {
                                continue;
                            }
                        @endphp
                        <div class="w-20">
                            <div class="checklist">
                                @if ($job_sheet->checklist[$check] == 'yes')
                                    <div class="checkbox">&#10004;</div>
                                @elseif($job_sheet->checklist[$check] == 'no')
                                    <div class="checkbox">&#10006;</div>
                                @elseif($job_sheet->checklist[$check] == 'not_applicable')
                                    <div class="checkbox">&nbsp;</div>
                                @endif
                                <div class="checkbox-text">{{ $check }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div> --}}
        <div style="margin-bottom: 5px;">
            <strong>@lang('repair::lang.product_configuration'):</strong>
            {!! $job_sheet->product_configuration !!}
        </div>
        <div style="margin-bottom: 5px;">
            <strong>@lang('repair::lang.condition_of_product'):</strong>
            {!! $job_sheet->product_condition !!}
        </div>
        <div style="margin-bottom: 5px;">
            <strong>@lang('repair::lang.problem_reported_by_customer'):</strong>
            {!! $job_sheet->defects !!}
        </div>

        <div style="margin-bottom: 5px;">
            @if (!empty($job_sheet->custom_field_1))
                <div class="width-50 f-left">
                    <strong>{{ $repair_settings['job_sheet_custom_field_1'] ?? __('lang_v1.custom_field', ['number' => 1]) }}:</strong>
                    {{ $job_sheet->custom_field_1 }}
                </div>
            @endif
            @if (!empty($job_sheet->custom_field_2))
                <div class="width-50 f-left">
                    <strong>{{ $repair_settings['job_sheet_custom_field_2'] ?? __('lang_v1.custom_field', ['number' => 2]) }}:</strong>
                    {{ $job_sheet->custom_field_2 }}
                </div>
            @endif
            @if (!empty($job_sheet->custom_field_3))
                <div class="width-50 f-left">
                    <strong>{{ $repair_settings['job_sheet_custom_field_3'] ?? __('lang_v1.custom_field', ['number' => 3]) }}:</strong>
                    {{ $job_sheet->custom_field_3 }}
                </div>
            @endif
            @if (!empty($job_sheet->custom_field_4))
                <div class="width-50 f-left">
                    <strong>{{ $repair_settings['job_sheet_custom_field_4'] ?? __('lang_v1.custom_field', ['number' => 4]) }}:</strong>
                    {{ $job_sheet->custom_field_4 }}
                </div>
            @endif
            @if (!empty($job_sheet->custom_field_5))
                <div class="width-50 f-left">
                    <strong>{{ $repair_settings['job_sheet_custom_field_5'] ?? __('lang_v1.custom_field', ['number' => 5]) }}:</strong>
                    {{ $job_sheet->custom_field_5 }}
                </div>
            @endif
        </div>
    </div>
</div>
@if (!empty($job_sheet->sheet_lines))
    <div class="box">
        <table class="table-pdf">
            <thead>
                <tr>
                    <th class="col-8" align="left">
                        <strong>
                            Nome
                        </strong>
                    </th>
                    <th class="col-2 text-right" align="right">
                        <strong>
                            Quantidade
                        </strong>
                    </th>
                    <th class="col-2 text-right" align="right">
                        <strong>
                            Tipo
                        </strong>
                    </th>
                    <th class="col-2 text-right" align="right">
                        <strong>
                            Valor
                        </strong>
                    </th>
                <tr>
            </thead>
            <tbody>
                @foreach ($job_sheet->sheet_lines as $part)
                    <tr>
                        <td>
                            {{ mb_strtoupper($part->product->name) }}
                        </td>
                        <td class="text-right" align="right">
                            {{ $part->quantity }}
                        </td>
                        <td class="text-right" align="right">
                            {{ $part->product->unit->short_name }}
                        </td>
                        <td class="text-right" align="right">
                            @format_currency($part->unit_price_inc_tax)
                        </td>
                    </tr>
                @endforeach
                {{-- <tr>
                    <td colspan="4">
                        <hr />
                    </td>
                </tr> --}}
                <tr>
                    <td colspan="2" class="text-right" align="right">
                        <strong>
                            @lang('repair::lang.service_costs')
                        </strong>
                    </td>
                    <td colspan="2" class="text-right" align="right">
                        @format_currency($job_sheet->service_costs)
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right" align="right">
                        <strong>
                            @lang('sale.total_amount')
                        </strong>
                    </td>
                    <td colspan="2" class="text-right" align="right">
                        @format_currency($job_sheet->total_costs)
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
<div class="width-100 content-div">
    <strong>@lang('lang_v1.terms_conditions'):</strong>
    @if (!empty($repair_settings['repair_tc_condition']))
        {!! $repair_settings['repair_tc_condition'] !!}
    @endif
</div>
<table class="table-pdf">
    <tr>
        <th>
            @lang('repair::lang.customer_signature'):
        </th>
        <th>@lang('repair::lang.authorized_signature'):</th>
        <td><strong>@lang('repair::lang.technician'):</strong> {{ optional($job_sheet->technician)->user_full_name }}</td>
    </tr>
</table>
<span style='font-size:20px;'><br /><br />&#9986;
    ------------------------------------------------------------------------------------------------------</span>

<table class="table-pdf">
    <tr>
        <td><strong>@lang('repair::lang.job_sheet_no'):</strong><br>
            {{ $job_sheet->job_sheet_no }}
        </td>
        <td><img
                src="data:image/png;base64,{{ DNS1D::getBarcodePNG($job_sheet->job_sheet_no, 'C128', 1, 50, [39, 48, 10], true) }}">
        </td>
        <td>
            <strong>@lang('repair::lang.device_model'):</strong> {{ optional($job_sheet->deviceModel)->name ?? 'N/A' }} &nbsp;
            <strong>@lang('lang_v1.password'):</strong> {{ $job_sheet->security_pwd }}<br>
            <strong>@lang('repair::lang.serial_no'): </strong>{{ $job_sheet->serial_no }} <br>
            <strong>@lang('repair::lang.security_pattern_code'):</strong>
            {{ $job_sheet->security_pattern }}
        </td>
        <td>
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
                                    $padrao .= "<span style='font-size: 11px; color: #16c316;'>$sequencia</span>";
                                } else {
                                    $padrao .= "<span style='font-size: 11px; color: #9d9d9d;'>0</span>";
                                }
                            }
                            $padrao .= "\n";
                        }

                        echo nl2br($padrao);
                        ?>
                    </div>
                </div>
            @endif
        </td>
    </tr>
    <tr>
        <td><strong>@lang('repair::lang.expected_delivery_date'):</strong><br>{{ @format_datetime($job_sheet->delivery_date) }}</td>
        <td colspan="2">
            <strong>@lang('repair::lang.problem_reported_by_customer'):</strong> <br>
            {{ $job_sheet->defects }}
        </td>
    </tr>
</table>
