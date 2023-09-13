<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title no-print">
                @lang('purchase.view_payments')
                (
                @if (in_array($transaction->type, ['purchase', 'expense', 'purchase_return', 'payroll']))
                    @lang('purchase.ref_no'): {{ $transaction->ref_no }}
                @elseif(in_array($transaction->type, ['sell', 'sell_return']))
                    @lang('sale.invoice_no'): {{ $transaction->invoice_no }}
                @endif
                )
            </h4>
            <h4 class="modal-title visible-print-block">
                @if (in_array($transaction->type, ['purchase', 'expense', 'purchase_return', 'payroll']))
                    @lang('purchase.ref_no'): {{ $transaction->ref_no }}
                @elseif($transaction->type == 'sell')
                    @lang('sale.invoice_no'): {{ $transaction->invoice_no }}
                @endif
            </h4>
        </div>

        <div class="modal-body">
            @if (in_array($transaction->type, ['purchase', 'purchase_return']))
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        @include('transaction_payment.transaction_supplier_details')
                    </div>
                    <div class="col-md-4 invoice-col">
                        @include('transaction_payment.payment_business_details')
                    </div>

                    <div class="col-sm-4 invoice-col">
                        <b>@lang('purchase.ref_no'):</b> #{{ $transaction->ref_no }}<br />
                        <b>@lang('messages.date'):</b> {{ @format_date($transaction->transaction_date) }}<br />
                        <b>@lang('purchase.purchase_status'):</b> {{ __('lang_v1.' . $transaction->status) }}<br>
                        <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $transaction->payment_status) }}<br>
                    </div>
                </div>
            @elseif($transaction->type == 'expense')
                <div class="row invoice-info">
                    @if (!empty($transaction->contact))
                        <div class="col-sm-4 invoice-col">
                            @lang('expense.expense_for'):
                            <address>
                                <strong>{{ $transaction->contact->supplier_business_name }}</strong>
                                {{ $transaction->contact->name }}
                                @if (!empty($transaction->contact->landmark))
                                    <br>{{ $transaction->contact->landmark }}
                                @endif
                                @if (
                                    !empty($transaction->contact->city) ||
                                        !empty($transaction->contact->state) ||
                                        !empty($transaction->contact->country))
                                    <br>{{ implode(',', array_filter([$transaction->contact->city, $transaction->contact->state, $transaction->contact->country])) }}
                                @endif
                                @if (!empty($transaction->contact->tax_number))
                                    <br>@lang('contact.tax_no'): {{ $transaction->contact->tax_number }}
                                @endif
                                @if (!empty($transaction->contact->mobile))
                                    <br>@lang('contact.mobile'): {{ $transaction->contact->mobile }}
                                @endif
                                @if (!empty($transaction->contact->email))
                                    <br>@lang('business.email'): {{ $transaction->contact->email }}
                                @endif
                            </address>
                        </div>
                    @endif
                    <div class="col-md-4 invoice-col">
                        @include('transaction_payment.payment_business_details')
                    </div>

                    <div class="col-sm-4 invoice-col">
                        <b>@lang('purchase.ref_no'):</b> #{{ $transaction->ref_no }}<br />
                        <b>@lang('messages.date'):</b> {{ @format_date($transaction->transaction_date) }}<br />
                        <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $transaction->payment_status) }}<br>
                    </div>
                </div>
            @elseif($transaction->type == 'payroll')
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        @lang('essentials::lang.payroll_for'):
                        <address>
                            <strong>{{ $transaction->transaction_for->user_full_name }}</strong>
                            @if (!empty($transaction->transaction_for->address))
                                <br>{{ $transaction->transaction_for->address }}
                            @endif
                            @if (!empty($transaction->transaction_for->contact_number))
                                <br>@lang('contact.mobile'): {{ $transaction->transaction_for->contact_number }}
                            @endif
                            @if (!empty($transaction->transaction_for->email))
                                <br>@lang('business.email'): {{ $transaction->transaction_for->email }}
                            @endif
                        </address>
                    </div>
                    <div class="col-md-4 invoice-col">
                        @include('transaction_payment.payment_business_details')
                    </div>
                    <div class="col-sm-4 invoice-col">
                        <b>@lang('purchase.ref_no'):</b> #{{ $transaction->ref_no }}<br />
                        @php
                            $transaction_date = \Carbon::parse($transaction->transaction_date);
                        @endphp
                        <b>@lang('essentials::lang.month_year'):</b> {{ $transaction_date->format('F') }}
                        {{ $transaction_date->format('Y') }}<br />
                        <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $transaction->payment_status) }}<br>
                    </div>
                </div>
            @else
                <div class="row invoice-info">
                    <div class="col-sm-4 invoice-col">
                        @lang('contact.customer'):
                        <address>
                            <strong>{{ $transaction->contact->name }}</strong>

                            @if (!empty($transaction->contact->landmark))
                                <br>{{ $transaction->contact->landmark }}
                            @endif
                            @if (
                                !empty($transaction->contact->city) ||
                                    !empty($transaction->contact->state) ||
                                    !empty($transaction->contact->country))
                                <br>{{ implode(',', array_filter([$transaction->contact->city, $transaction->contact->state, $transaction->contact->country])) }}
                            @endif
                            @if (!empty($transaction->contact->tax_number))
                                <br>@lang('contact.tax_no'): {{ $transaction->contact->tax_number }}
                            @endif
                            @if (!empty($transaction->contact->mobile))
                                <br>@lang('contact.mobile'): {{ $transaction->contact->mobile }}
                            @endif
                            @if (!empty($transaction->contact->email))
                                <br>@lang('business.email'): {{ $transaction->contact->email }}
                            @endif
                        </address>
                    </div>
                    <div class="col-md-4 invoice-col">
                        @include('transaction_payment.payment_business_details')
                    </div>
                    <div class="col-sm-4 invoice-col">
                        <b>@lang('sale.invoice_no'):</b> #{{ $transaction->invoice_no }}<br />
                        <b>@lang('messages.date'):</b> {{ @format_date($transaction->transaction_date) }}<br />
                        <b>@lang('purchase.payment_status'):</b> {{ __('lang_v1.' . $transaction->payment_status) }}<br>
                    </div>
                </div>
            @endif

            @can('send_notification')
                @if ($transaction->type == 'purchase')
                    <div class="row no-print">
                        <div class="text-right col-md-12">
                            <button type="button" class="btn btn-info btn-modal btn-xs"
                                data-href="{{ action('NotificationController@getTemplate', ['transaction_id' => $transaction->id, 'template_for' => 'payment_paid']) }}"
                                data-container=".view_modal">
                                <i class="fa fa-envelope"></i> @lang('lang_v1.payment_paid_notification')</button>
                        </div>
                    </div>
                    <br>
                @endif
                @if ($transaction->type == 'sell')
                    <div class="row no-print">
                        <div class="text-right col-md-12">
                            <button type="button" class="btn btn-info btn-modal btn-xs"
                                data-href="{{ action('NotificationController@getTemplate', ['transaction_id' => $transaction->id, 'template_for' => 'payment_received']) }}"
                                data-container=".view_modal">
                                <i class="fa fa-envelope"></i> @lang('lang_v1.payment_received_notification')</button>

                            @if ($transaction->payment_status != 'paid')
                                &nbsp;
                                <button type="button" class="btn btn-warning btn-modal btn-xs"
                                    data-href="{{ action('NotificationController@getTemplate', ['transaction_id' => $transaction->id, 'template_for' => 'payment_reminder']) }}"
                                    data-container=".view_modal">
                                    <i class="fa fa-envelope"></i> @lang('lang_v1.send_payment_reminder')</button>
                            @endif
                        </div>
                    </div>
                    <br>
                @endif
            @endcan
            @if ($transaction->payment_status != 'paid')
                <div class="row">
                    <div class="col-md-12">
                        @if (
                            (auth()->user()->can('purchase.payments') &&
                                in_array($transaction->type, ['purchase', 'purchase_return'])) ||
                                (auth()->user()->can('sell.payments') &&
                                    in_array($transaction->type, ['sell', 'sell_return'])) ||
                                auth()->user()->can('expense.access'))
                            <a href="{{ action('TransactionPaymentController@addPayment', [$transaction->id]) }}"
                                class="btn btn-primary btn-xs pull-right add_payment_modal no-print">
                                <i class="fa fa-plus" aria-hidden="true"></i>
                                @lang('purchase.add_payment')
                            </a>
                        @endif
                    </div>
                </div>
            @endif
            <br>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <tr>
                                <th>
                                    <div>
                                        @lang('purchase.ref_no')
                                    </div>
                                    <div class="text-nowrap text-muted">
                                        @lang('messages.date')
                                    </div>
                                </th>
                                <th>
                                    <div>
                                        @lang('purchase.payment_method')
                                    </div>
                                    @if ($accounts_enabled)
                                        <div class="text-nowrap text-muted">
                                            Conta
                                        </div>
                                    @endif
                                </th>
                                <th>
                                    <div>
                                        @lang('purchase.amount')
                                    </div>
                                </th>
                                <th class="text-center no-print">@lang('messages.actions')</th>
                            </tr>
                            @forelse ($payments as $payment)
                                <tr title="{{ $payment->note }}">
                                    <td>
                                        <div>
                                            {{ $payment->payment_ref_no }}
                                        </div>
                                        <div class="text-nowrap text-muted">
                                            {{ @format_datetime($payment->vencimento != null ? $payment->vencimento : $payment->paid_on) }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($accounts_enabled)
                                            <div>{{ $payment->payment_account->name ?? '' }}</div>
                                        @endif
                                        <div class="text-nowrap text-muted">
                                            {{ $payment_types[$payment->method] ?? '' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="display_currency"
                                            data-currency_symbol="true">{{ $payment->amount }}
                                        </span>
                                    </td>
                                    <td class="text-right no-print">
                                        @if (
                                            (auth()->user()->can('purchase.payments') &&
                                                in_array($transaction->type, ['purchase', 'purchase_return'])) ||
                                                (auth()->user()->can('sell.payments') &&
                                                    in_array($transaction->type, ['sell', 'sell_return'])) ||
                                                auth()->user()->can('expense.access'))
                                            {{--  --}}
                                            {{-- <button type="button" class="btn btn-info btn-xs edit_payment"
                                                data-href="{{ action('TransactionPaymentController@edit', [$payment->id]) }}">
                                                <i class="glyphicon glyphicon-edit"></i>
                                            </button> --}}

                                            <button type="button" class="btn btn-danger btn-xs delete_payment"
                                                data-href="{{ action('TransactionPaymentController@destroy', [$payment->id]) }}">
                                                <i class="fa fa-trash" aria-hidden="true"></i>
                                            </button>

                                            <button type="button" class="btn btn-primary btn-xs view_payment"
                                                data-href="{{ action('TransactionPaymentController@viewPayment', [$payment->id]) }}">
                                                <i class="fa fa-eye" aria-hidden="true"></i>
                                            </button>
                                            {{--  --}}
                                        @endif
                                        @if (!empty($payment->document_path))
                                            <a href="{{ $payment->document_path }}" class="btn btn-success btn-xs"
                                                download="{{ $payment->document_name }}">
                                                <i class="fa fa-download" data-toggle="tooltip"
                                                    title="{{ __('purchase.download_document') }}"></i>
                                            </a>
                                            @if (isFileImage($payment->document_name))
                                                <button data-href="{{ $payment->document_path }}"
                                                    class="btn btn-info btn-xs view_uploaded_document"
                                                    data-toggle="tooltip" title="{{ __('lang_v1.view_document') }}">
                                                    <i class="fa fa-picture-o"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="6">@lang('purchase.no_records_found')</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-primary no-print" aria-label="Print"
                onclick="$(this).closest('div.modal').printThis();">
                <i class="fa fa-print"></i> @lang('messages.print')
            </button>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
