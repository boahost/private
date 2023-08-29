@php
    $is_mobile = isMobile();
@endphp
<style>
    .shortcuts .table-bordered>tbody>tr>td,
    .shortcuts .table-bordered>tbody>tr>th,
    .shortcuts .table-bordered>tfoot>tr>td,
    .shortcuts .table-bordered>tfoot>tr>th,
    .shortcuts .table-bordered>thead>tr>td,
    .shortcuts .table-bordered>thead>tr>th {
        border: 1px solid #636363;
    }
</style>
<div style="bottom: 0px; position: fixed; left: 0; right: 0; background-color: #4f4f4f; color: white; z-index: 1040;">
    <div class="row" id="pos-form-actions">
        {{-- <div class="pos-form-actions"> --}}

        <div class="col-xs-12">
            <div class="pull-left @if ($is_mobile) width-100 @endif"
                style="padding: 10px 10px 0px 10px;">
                @if ($is_mobile)
                    <div class="col-md-12 text-right">
                        <b>@lang('sale.total_payable'):</b>
                        <input type="hidden" name="final_total" id="final_total_input" value=0>
                        <span id="total_payable" class="text-yellow text-bold" style="font-size: 36px;">0</span>
                    </div>
                @endif
                <button type="button"
                    class="@if ($is_mobile) col-xs-6 @endif btn bg-info text-white btn-flat @if ($pos_settings['disable_draft'] != 0) hide @endif"
                    id="pos-draft"><i class="fas fa-edit"></i> @lang('sale.draft')
                </button>
                <button type="button" class="btn bg-yellow btn-flat @if ($is_mobile) col-xs-6 @endif"
                    id="pos-quotation"><i class="fas fa-edit"></i> @lang('Orçamentos')
                </button>

                <button type="button"
                    class="btn bg-info btn-flat @if ($is_mobile) col-xs-6 @endif text-white hide"
                    id="pos-table"><i class="fas fa-table"></i> Salvar pedido
                </button>

                @if (empty($pos_settings['disable_suspend']))
                    <button type="button"
                        class="@if ($is_mobile) col-xs-6 @endif btn bg-red btn-flat no-print pos-express-finalize"
                        data-pay_method="suspend" title="@lang('lang_v1.tooltip_suspend')">
                        <i class="fas fa-pause" aria-hidden="true"></i>
                        Comandas
                    </button>
                @endif

                @if (!empty($pos_settings['show_credit_sale_button']))
                    <input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
                    <button type="button"
                        class="btn bg-purple btn-flat no-print pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                        data-pay_method="credit_sale" title="@lang('lang_v1.tooltip_credit_sale')">
                        <i class="fas fa-check" aria-hidden="true"></i> @lang('sale.spun')
                    </button>
                @endif

                <button type="button"
                    class="btn bg-navy @if (!$is_mobile)  @endif btn-flat no-print @if ($pos_settings['disable_pay_checkout'] != 0) hide @endif @if ($is_mobile) col-xs-6 @endif"
                    id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt"
                        aria-hidden="true"></i>
                    @lang('lang_v1.checkout_multi_pay')
                </button>

                <button type="button"
                    class="btn btn-success @if (!$is_mobile)  @endif btn-flat no-print @if ($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if ($is_mobile) col-xs-6 @endif"
                    data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt"
                        aria-hidden="true"></i>
                    @lang('lang_v1.express_checkout_cash')
                </button>

                @if (empty($edit))
                    <button type="button"
                        class="btn btn-danger btn-flat @if ($is_mobile) col-xs-6 @endif"
                        id="pos-cancel">
                        <i class="fas fa-ban"></i> @lang('sale.cancel')
                    </button>
                @else
                    <button type="button"
                        class="btn btn-danger btn-flat hide @if ($is_mobile) col-xs-6 @endif"
                        id="pos-delete"> <i class="fas fa-trash-alt"></i> @lang('messages.delete')
                    </button>
                @endif

                <button type="button"
                    class="btn btn-primary btn-flat @if ($is_mobile) col-xs-6 @endif"
                    data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"> <i
                        class="fas fa-clock"></i> Transações
                </button>
                @if (!$is_mobile)
                    <div class="pull-right text-right" title="@lang('sale.total_payable')"
                        style="margin-top: -8px; margin-left: 20px;">

                        <input type="hidden" name="final_total" id="final_total_input" value=0>
                        @lang('sale.total_payable')
                        <span id="total_payable" class="text-yellow text-bold" style="font-size: 36px;">0,00</span>
                    </div>
                @endif
            </div>
        </div>
        @if (!$is_mobile)
            <div class="col-xs-12 anim">
                <button type="button" data-target=".shortcuts" data-transition="false"
                    style="background-color: transparent; width: 100%; border: none; padding: 5px;">
                    <i aria-expanded="false" class="fa fa-chevron-down"></i>
                </button>
                <div aria-expanded="true" class="shortcuts p-10">
                    <table
                        class='table table-bordered table-condensed table-no-top-cell-border table-slim table-text-center'
                        style="font-size: 11px; margin: 0; border: transparent;">
                        <tr class="text-uppercase text-center">
                            @if ($pos_settings['disable_express_checkout'] == 0)
                                <td>@lang('sale.express_finalize'):</td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>@lang('sale.finalize'):</td>
                            @endif
                            @if ($pos_settings['disable_draft'] == 0)
                                <td>@lang('sale.draft'):</td>
                            @endif
                            <td>@lang('messages.cancel'):</td>
                            @if ($pos_settings['disable_discount'] == 0)
                                <td>@lang('sale.edit_discount'):</td>
                            @endif
                            @if ($pos_settings['disable_order_tax'] == 0)
                                <td>@lang('sale.edit_order_tax'):</td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>@lang('sale.add_payment_row'):</td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>@lang('sale.finalize_payment'):</td>
                            @endif
                            <td>@lang('lang_v1.recent_product_quantity'):</td>
                            <td>@lang('lang_v1.add_new_product'):</td>
                            @if (isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
                                <td>@lang('lang_v1.weighing_scale'):</td>
                            @endif
                        </tr>
                        <tr class="text-uppercase text-center">
                            @if ($pos_settings['disable_express_checkout'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['express_checkout']))
                                        {{ $shortcuts['pos']['express_checkout'] }}
                                    @endif
                                </td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['pay_n_ckeckout']))
                                        {{ $shortcuts['pos']['pay_n_ckeckout'] }}
                                    @endif
                                </td>
                            @endif
                            @if ($pos_settings['disable_draft'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['draft']))
                                        {{ $shortcuts['pos']['draft'] }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                @if (!empty($shortcuts['pos']['cancel']))
                                    {{ $shortcuts['pos']['cancel'] }}
                                @endif
                            </td>
                            @if ($pos_settings['disable_discount'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['edit_discount']))
                                        {{ $shortcuts['pos']['edit_discount'] }}
                                    @endif
                                </td>
                            @endif
                            @if ($pos_settings['disable_order_tax'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['edit_order_tax']))
                                        {{ $shortcuts['pos']['edit_order_tax'] }}
                                    @endif
                                </td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['add_payment_row']))
                                        {{ $shortcuts['pos']['add_payment_row'] }}
                                    @endif
                                </td>
                            @endif
                            @if ($pos_settings['disable_pay_checkout'] == 0)
                                <td>
                                    @if (!empty($shortcuts['pos']['finalize_payment']))
                                        {{ $shortcuts['pos']['finalize_payment'] }}
                                    @endif
                                </td>
                            @endif
                            <td>
                                @if (!empty($shortcuts['pos']['recent_product_quantity']))
                                    {{ $shortcuts['pos']['recent_product_quantity'] }}
                                @endif
                            </td>
                            <td>
                                @if (!empty($shortcuts['pos']['add_new_product']))
                                    {{ $shortcuts['pos']['add_new_product'] }}
                                @endif
                            </td>
                            @if (isset($pos_settings['enable_weighing_scale']) && $pos_settings['enable_weighing_scale'] == 1)
                                <td>
                                    @if (!empty($shortcuts['pos']['weighing_scale']))
                                        {{ $shortcuts['pos']['weighing_scale'] }}
                                    @endif
                                </td>
                            @endif
                        </tr>
                    </table>
                </div>
            </div>
        @endif {{-- </div> --}}
    </div>
</div>
