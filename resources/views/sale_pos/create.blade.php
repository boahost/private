@extends('layouts.app')
@section('title', 'PDV')
@section('content')
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @php
            $is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
            $is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
        @endphp
        {!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_pos_sell_form']) !!}
        <div class="row" style="padding-bottom: 180px">
            <div class="col-md-12">
                <div class="row">
                    <div
                        class="@if (empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
                        <div class="box box-solid mb-12">
                            <div class="box-body pb-0">
                                {!! Form::hidden('location_id', $default_location->id, [
                                    'id' => 'location_id',
                                    'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                                        ? $default_location->receipt_printer_type
                                        : 'browser',
                                    'data-default_accounts' => $default_location->default_payment_accounts,
                                ]) !!}
                                <!-- sub_type -->
                                {!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
                                <input type="hidden" id="item_addition_method"
                                    value="{{ $business_details->item_addition_method }}">
                                @include('sale_pos.partials.pos_form')
                                @include('sale_pos.partials.pos_form_totals')
                                @include('sale_pos.partials.payment_modal')
                                <input type="hidden" id="json_boleto" name="json_boleto">
                                @if (empty($pos_settings['disable_suspend']))
                                    @include('sale_pos.partials.suspend_note_modal')
                                @endif
                                @if (empty($pos_settings['disable_recurring_invoice']))
                                    @include('sale_pos.partials.recurring_invoice_modal')
                                @endif
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="cpf" value="" name="cpf">
                    <input type="hidden" id="valor_recebido" value="0" name="valor_recebido">
                    <input type="hidden" id="token" value="{{ csrf_token() }}">
                    @if (empty($pos_settings['hide_product_suggestion']) && !isMobile())
                        <div class="col-md-5 no-padding">
                            @include('sale_pos.partials.pos_sidebar')
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @include('sale_pos.partials.pos_form_actions')
        {!! Form::close() !!}

        @if (isset($transaction))
            @include('sale_pos.partials.edit_discount_modal', [
                'sales_discount' => $transaction->discount_amount,
                'discount_type' => $transaction->discount_type,
                'rp_redeemed' => $transaction->rp_redeemed,
                'rp_redeemed_amount' => $transaction->rp_redeemed_amount,
                'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0,
            ])
        @else
            @include('sale_pos.partials.edit_discount_modal', [
                'sales_discount' => $business_details->default_sales_discount,
                'discount_type' => 'percentage',
                'rp_redeemed' => 0,
                'rp_redeemed_amount' => 0,
                'max_available' => 0,
            ])
        @endif

        @if (isset($transaction))
            @include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
        @else
            @include('sale_pos.partials.edit_order_tax_modal', [
                'selected_tax' => $business_details->default_sales_tax,
            ])
        @endif

        @include('sale_pos.partials.edit_shipping_modal')

    </section>
    <!-- This will be printed -->
    <section class="invoice print_section" id="receipt_section">
    </section>
    <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('contact.create', ['quick_add' => true])
    </div>
    @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
        @include('sale_pos.partials.mobile_product_suggestions')
    @endif
    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>
    @include('sale_pos.partials.configure_search_modal')
    @include('sale_pos.partials.recent_transactions_modal')
    @include('sale_pos.partials.weighing_scale_modal')

@stop
@section('javascript')
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    @include('sale_pos.partials.keyboard_shortcuts')

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif

    <!-- include module js -->
    @if (!empty($pos_module_data))
        @foreach ($pos_module_data as $key => $value)
            @if (!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
        @endforeach
    @endif
@endsection
