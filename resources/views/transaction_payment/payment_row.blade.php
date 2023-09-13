<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action('TransactionPaymentController@store'),
            'method' => 'post',
            'id' => 'transaction_payment_add_form',
            'files' => true,
        ]) !!}
        {!! Form::hidden('transaction_id', $transaction->id) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('purchase.add_payment')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @if (!empty($transaction->contact))
                    <div class="col-md-12">
                        <div class="well">
                            <strong>
                                @if (in_array($transaction->type, ['purchase', 'purchase_return']))
                                    @lang('purchase.supplier')
                                @elseif(in_array($transaction->type, ['sell', 'sell_return']))
                                    @lang('contact.customer')
                                @endif
                            </strong>:{{ $transaction->contact->name }}<br>
                            @if ($transaction->type == 'purchase')
                                <strong>@lang('business.business'): </strong>{{ $transaction->contact->supplier_business_name }}
                            @endif
                        </div>
                    </div>
                @endif
                <div class="col-md-6">
                    <div class="well">
                        @if (in_array($transaction->type, ['sell', 'sell_return']))
                            <strong>@lang('sale.invoice_no'): </strong>{{ $transaction->invoice_no }}
                        @else
                            <strong>@lang('purchase.ref_no'): </strong>{{ $transaction->ref_no }}
                        @endif
                        @if (!empty($transaction->location))
                            <br>
                            <strong>@lang('purchase.location'): </strong>{{ $transaction->location->name }}
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="well">
                        <strong>@lang('sale.total_amount'): </strong><span class="display_currency"
                            data-currency_symbol="true">{{ $transaction->final_total }}</span><br>
                        <strong>@lang('purchase.payment_note'): </strong>
                        @if (!empty($transaction->additional_notes))
                            {{ $transaction->additional_notes }}
                        @else
                            --
                        @endif
                    </div>
                </div>
            </div>
            <div class="row payment_row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('method', __('purchase.payment_method') . ':*') !!}
                        {!! Form::select('method', $payment_types, $payment_line->method, [
                            'class' => 'form-control select2 payment_types_dropdown',
                            'required',
                            'style' => 'width:100%;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('amount', 'Valor' . ':*') !!}
                        {!! Form::text('amount', @num_format($payment_line->amount), [
                            'class' => 'form-control input_number',
                            'required',
                            'placeholder' => 'Valor',
                            'data-rule-max-value' => @num_format($payment_line->amount),
                            'data-msg-max-value' => __('lang_v1.max_amount_to_be_paid_is', ['amount' => $amount_formated]),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('vencimento', __('purchase.expire_date') . ':*') !!}
                        {!! Form::input('datetime-local', 'vencimento', $payment_line->vencimento, [
                            'class' => 'form-control',
                            'required',
                        ]) !!}
                    </div>
                </div>
                @if (!empty(array_filter(array_keys($accounts))))
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('account_id', __('lang_v1.payment_account') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                </span>
                                {!! Form::select('account_id', $accounts, !empty($payment_line->account_id) ? $payment_line->account_id : '', [
                                    'class' => 'form-control select2',
                                    'id' => 'account_id',
                                    'style' => 'width:100%;',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="text-right form-group">
                        {!! Form::label('is_paid', __('lang_v1.paid')) !!}
                        <div>
                            <label class="radio radio-inline">
                                {!! Form::radio('is_paid', 'yes', false, ['class' => 'input-icheck', 'required']) !!}
                                @lang('messages.yes')
                            </label>
                            <label class="radio radio-inline">
                                {!! Form::radio('is_paid', 'no', true, ['class' => 'input-icheck', 'required']) !!}
                                @lang('messages.no')
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('document', __('purchase.attach_document') . ':') !!}
                        {!! Form::file('document', [
                            'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                        ]) !!}
                        <p class="help-block">
                            @includeIf('components.document_help_text')</p>
                    </div>
                </div>
                <div class="clearfix"></div>
                @include('transaction_payment.payment_type_details')
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('note', __('lang_v1.payment_note') . ':') !!}
                        {!! Form::textarea('note', $payment_line->note, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
