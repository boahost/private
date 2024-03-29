<div class="modal-dialog modal-lg" role="document" id="register_details">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h3 class="modal-title">@lang('cash_register.register_details') (
                {{ \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('d-m-Y H:i') }} -
                {{ \Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('d-m-Y H:i') }} )</h3>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <table class="table">
                        <tr>
                            <td>
                                @lang('Dinheiro ao Abrir Caixa'):
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->cash_in_hand }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @lang('cash_register.bank_transfer'):
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->total_bank_transfer }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @lang('Dinheiro'):
                                </th>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->total_cash }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @lang('Credito'):
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->total_card }}
                                </span>
                            </td>
                        </tr>
                        {{-- <tr>
                            <td>
                                @lang('Pagamento Débito'):
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->total_debit_card }}
                                </span>
                            </td>
                        </tr> --}}
                        @if (array_key_exists('custom_pay_1', $payment_types))
                            <tr>
                                <td>
                                    {{ $payment_types['custom_pay_1'] }}:
                                </td>
                                <td>
                                    <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_custom_pay_1 }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                        @if (array_key_exists('custom_pay_2', $payment_types))
                            <tr>
                                <td>
                                    {{ $payment_types['custom_pay_2'] }}:
                                </td>
                                <td>
                                    <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_custom_pay_2 }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                        @if (array_key_exists('custom_pay_3', $payment_types))
                            <tr>
                                <td>
                                    {{ $payment_types['custom_pay_3'] }}:
                                </td>
                                <td>
                                    <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_custom_pay_3 }}
                                    </span>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td>
                                @lang('integration.pix_efi'):
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $register_details->total_pix_efi }}
                                </span>
                            </td>
                        </tr>
                        <!--<tr>-->
                        <!--  <td>-->
                        <!--    @lang('PIX'):-->
                        <!--  </td>-->
                        <!--  <td>-->
                        <!--    <span class="display_currency" data-currency_symbol="true">{{ $register_details->total_Pix }}
                        </span>-->
                        <!--  </td>-->
                        <!--</tr>-->


                        @foreach ($lista_suprimentos as $item)
                            <tr style="color: #004d2a;" title="Suprimento">
                                <td>
                                    <i class="fas fa-plus-circle"></i>
                                    {{ $item['justification'] }}
                                </td>
                                <td class="display_currency" data-currency_symbol="true">
                                    {{ $item['amount'] }}
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($lista_sangrias as $item)
                            <tr style="color: #a31515;" title="Sangria">
                                <td>
                                    <i class="fas fa-minus-circle"></i>
                                    {{ $item['justification'] }}
                                </td>
                                <td class="display_currency" data-currency_symbol="true">
                                    {{ $item['amount'] }}
                                </td>
                            </tr>
                        @endforeach


                        <tr class="success">
                            <th>
                                @lang('cash_register.total_refund')
                            </th>
                            <td>
                                <b><span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_refund }}
                                    </span>
                                </b>
                                <br>
                                <small>
                                    @if ($register_details->total_cash_refund != 0)
                                        Cash: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_cash_refund }}
                                        </span>
                                        <br>
                                    @endif
                                    @if ($register_details->total_cheque_refund != 0)
                                        Cheque: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_cheque_refund }}
                                        </span>
                                        <br>
                                    @endif
                                    @if ($register_details->total_card_refund != 0)
                                        Card: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_card_refund }}
                                        </span>
                                        <br>
                                    @endif
                                    @if ($register_details->total_bank_transfer_refund != 0)
                                        Bank Transfer: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}
                                        </span>
                                        <br>
                                    @endif
                                    @if (array_key_exists('custom_pay_1', $payment_types) && $register_details->total_custom_pay_1_refund != 0)
                                        {{ $payment_types['custom_pay_1'] }}: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_custom_pay_1_refund }}
                                        </span>
                                    @endif
                                    @if (array_key_exists('custom_pay_2', $payment_types) && $register_details->total_custom_pay_2_refund != 0)
                                        {{ $payment_types['custom_pay_2'] }}: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_custom_pay_2_refund }}
                                        </span>
                                    @endif
                                    @if (array_key_exists('custom_pay_3', $payment_types) && $register_details->total_custom_pay_3_refund != 0)
                                        {{ $payment_types['custom_pay_3'] }}: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_custom_pay_3_refund }}
                                        </span>
                                    @endif
                                    @if ($register_details->total_other_refund != 0)
                                        Other: <span class="display_currency"
                                            data-currency_symbol="true">{{ $register_details->total_other_refund }}
                                        </span>
                                    @endif

                                </small>
                            </td>
                        </tr>
                        <tr class="success">
                            <th>
                                @lang('lang_v1.total_payment')
                            </th>
                            <td>
                                <b><span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund }}
                                    </span>
                                </b>
                            </td>
                        </tr>
                        <tr class="success">
                            <th>
                                @lang('Vendas NF-e / Fiado'):
                            </th>
                            <td>
                                <b><span class="display_currency"
                                        data-currency_symbol="true">{{ $details['transaction_details']->total_sales - $register_details->total_sale }}
                                    </span>
                                </b>
                            </td>
                        </tr>
                        <tr class="success">
                            <th>
                                @lang('cash_register.total_sales'):
                            </th>
                            <td>
                                <b><span class="display_currency"
                                        data-currency_symbol="true">{{ $details['transaction_details']->total_sales }}
                                    </span>
                                </b>
                            </td>
                        </tr>

                        <tr class="danger">
                            <td>
                                <b>Total de Retirada R$</b>
                            </td>
                            <td>
                                <b>
                                    <span class="display_currency" data-currency_symbol="true">
                                        {{ $register_details->total_cash_sangria }}
                                    </span>
                                </b>
                            </td>
                        </tr>
                        <tr class="success">
                            <td>
                                <b>Total de Suprimento R$</b>
                            </td>
                            <td>
                                <b>
                                    <span class="display_currency" data-currency_symbol="true">
                                        {{ $register_details->total_cash_suprimento }}
                                    </span>
                                </b>
                            </td>
                        </tr>
                    </table>

                    <style>
                        #register_details table.table tr td:last-child {
                            text-align: end;
                        }
                    </style>
                </div>
            </div>



            <div class="modal-footer">
                <button type="button" class="btn btn-primary no-print" aria-label="Print"
                    onclick="$(this).closest('div.modal').printThis();">
                    <i class="fa fa-print"></i> @lang('messages.print')
                </button>

                <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.cancel')
                </button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
