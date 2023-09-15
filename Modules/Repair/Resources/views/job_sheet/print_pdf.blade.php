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
        text-align: left;
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
        font-size: 12px;
    }

    .font-14 {
        font-size: 14px;
    }

    .font-12 {
        font-size: 12px;
    }

    body {
        font-size: 11px;
    }
</style>

<div class="width-100 box mb-10">
    <table class="no-border table-pdf">
        <tr>
            <th width="80">
                @if (!empty(Session::get('business.logo')))
                    <img id="logo" height="80" width="80"
                        src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}" title="Logomarca"
                        alt="Logomarca" />
                @else
                    <img id="logo" height="80" width="80"
                        src="https://privatevisualsistemas.com//uploads/logo.png" title="Logomarca" alt="Logomarca" />
                @endif
            </th>
            <th colspan="3">
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
            <th>@lang('receipt.date'):</th>
            <th>@lang('repair::lang.service_type'):</th>
            <th>@lang('repair::lang.job_sheet_no'):</th>
            <th rowspan="2">
                <img
                    src="data:image/png;base64,{{ DNS1D::getBarcodePNG($job_sheet->job_sheet_no, 'C128', 1, 50, [39, 48, 10], true) }}">
            </th>
            <th>@lang('repair::lang.expected_delivery_date'):</th>
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
            <td style="vertical-align: top;">
                <table class="width-100">
                    <tr>
                        <th style="padding-left: 0;">@lang('role.customer'):</th>
                    </tr>
                    <tr>
                        <td style="padding-left: 0; padding-top: -5">
                            <p>
                                {!! $job_sheet->customer->contact_address !!}
                                @if (!empty($contact->email))
                                    <br>@lang('business.email'):
                                    {{ $job_sheet->customer->email }}
                                @endif
                                <br>@lang('contact.mobile'):
                                {{ $job_sheet->customer->mobile }}
                                @if (!empty($contact->tax_number))
                                    <br>@lang('contact.tax_no'):
                                    {{ $job_sheet->customer->tax_number }}
                                @endif
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td colspan="2" style="vertical-align: top;">
                <table class="width-100">
                    <tr>
                        <th>@lang('product.brand'):</th>
                        <td>{{ optional($job_sheet->brand)->name }}</td>
                        <th>@lang('repair::lang.device'):</th>
                        <td>{{ optional($job_sheet->device)->name }}</td>
                    </tr>
                    <tr>
                        <th>@lang('repair::lang.device_model'):</th>
                        <td>{{ optional($job_sheet->deviceModel)->name }}</td>
                        <th>@lang('lang_v1.password'):</th>
                        <td>{{ $job_sheet->security_pwd }}</td>
                    </tr>
                    <tr>
                        <th>@lang('repair::lang.serial_no'):</th>
                        <td colspan="2">{{ $job_sheet->serial_no }}</td>
                    </tr>
                    <tr>
                        <th>@lang('repair::lang.security_pattern_code'):</th>
                        <td colspan="2">{{ $job_sheet->security_pattern }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding-top: 0">
                <strong>@lang('sale.invoice_no'):</strong>
                @if ($job_sheet->invoices->count() > 0)
                    @foreach ($job_sheet->invoices as $invoice)
                        {{ $invoice->invoice_no }}
                        @if (!$loop->last)
                            {{ ', ' }}
                        @endif
                    @endforeach
                @endif
            </td>
            <td style="padding-top: 0">
                <strong>@lang('repair::lang.estimated_cost'):</strong>
                <span>
                    @format_currency($job_sheet->estimated_cost)
                </span>
            </td>
            <td style="padding-top: 0">
                <strong>
                    @lang('sale.status'):
                </strong>
                {{ optional($job_sheet->status)->name }}
            </td>
        </tr>
    </table>
</div>
<div class="box mb-10">
    <div class="width-100 content-div">
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
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.product_configuration'):</strong>
        {!! $job_sheet->product_configuration !!}
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.condition_of_product'):</strong>
        {!! $job_sheet->product_condition !!}
    </div>
    <div class="width-100 content-div">
        <strong>@lang('repair::lang.problem_reported_by_customer'):</strong>
        {!! $job_sheet->defects !!}
    </div>

    <div class="width-100 content-div">
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
@if (!empty($job_sheet->sheet_lines))
    <div class="box">
        <table class="table-pdf">
            <thead>
                <tr>
                    <th class="col-8 f-left">
                        <strong>
                            Nome
                        </strong>
                    </th>
                    <th class="col-2" align="right">
                        <strong>
                            Quantidade
                        </strong>
                    </th>
                    <th class="col-2" align="right">
                        <strong>
                            Tipo
                        </strong>
                    </th>
                    <th class="col-2" align="right">
                        <strong>
                            Valor
                        </strong>
                    </th>
                <tr>
            </thead>
            <tbody>
                @foreach ($job_sheet->sheet_lines as $part)
                    <tr>
                        <td class=" f-left">
                            {{ mb_strtoupper($part->product->name) }}
                        </td>
                        <td align="right">
                            {{ $part->quantity }}
                        </td>
                        <td align="right">
                            {{ $part->product->unit->short_name }}
                        </td>
                        <td align="right">
                            <span>
                                @format_currency($part->unit_price_inc_tax)
                            </span>
                        </td>
                    </tr>
                @endforeach
                {{-- <tr>
                    <td colspan="4">
                        <hr />
                    </td>
                </tr> --}}
                <tr>
                    <td colspan="3" align="right">
                        <strong>
                            @lang('repair::lang.service_costs'):
                        </strong>
                    </td>
                    <td colspan="1" align="right">
                        @format_currency($job_sheet->service_costs)
                    </td>
                </tr>
                <tr>
                    <td colspan="3" align="right">
                        <strong>
                            @lang('sale.total_amount'):
                        </strong>
                    </td>
                    <td colspan="1" align="right">
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
<span style='font-size:20px;'><br/><br/>&#9986;
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
            <strong>@lang('repair::lang.device_model'):</strong> {{ optional($job_sheet->deviceModel)->name }} &nbsp;
            <strong>@lang('lang_v1.password'):</strong> {{ $job_sheet->security_pwd }}<br>
            <strong>@lang('repair::lang.serial_no'): </strong>{{ $job_sheet->serial_no }} <br>
            <strong>@lang('repair::lang.security_pattern_code'):</strong>
            {{ $job_sheet->security_pattern }}
        </td>
    </tr>
    <tr>
        <td><strong>@lang('repair::lang.expected_delivery_date'):</strong><br>{{ @format_datetime($job_sheet->delivery_date) }}</td>
        <td colspan="2">
            <strong>@lang('repair::lang.problem_reported_by_customer'):</strong> <br>
            @php
                $defects = json_decode($job_sheet->defects, true);
            @endphp
            @if (!empty($defects))
                @foreach ($defects as $product_defect)
                    {{ $product_defect['value'] }}
                    @if (!$loop->last)
                        {{ ',' }}
                    @endif
                @endforeach
            @endif
        </td>
    </tr>
</table>
