<div class="payment_details_div @if ($payment_line['method'] !== 'card') {{ 'hide' }} @endif" data-type="card">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label("card_number_$row_index", __('lang_v1.card_no')) !!}
            {!! Form::text("payment[$row_index][card_number]", $payment_line['card_number'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.card_no'),
                'id' => "card_number_$row_index",
            ]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label("card_holder_name_$row_index", 'CNPJ') !!}
            {!! Form::text("payment[$row_index][card_holder_name]", $payment_line['card_holder_name'], [
                'class' => 'form-control',
                'placeholder' => 'CNPJ',
                'id' => "card_holder_name_$row_index",
            ]) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label("card_transaction_number_$row_index", 'Código de autorização') !!}
            {!! Form::text("payment[$row_index][card_transaction_number]", $payment_line['card_transaction_number'], [
                'class' => 'form-control',
                'placeholder' => 'Código de autorização',
                'id' => "card_transaction_number_$row_index",
            ]) !!}
        </div>
    </div>


    <!-- <div class="clearfix"></div> -->
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label("card_type_$row_index", __('lang_v1.card_type')) !!}
            {!! Form::select(
                "payment[$row_index][card_type]",
                ['credit' => 'Crédito', 'debit' => 'Débito'],
                $payment_line['card_type'],
                ['class' => 'form-control', 'id' => "card_type_$row_index"],
            ) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('card_security', 'Bandeira') !!}

            {!! Form::select("payment[$row_index][card_security]", App\Models\Transaction::bandeiras(), 'card_security', [
                'class' => 'form-control select2',
                'id' => 'card_security',
                'style="width: 100%"',
            ]) !!}
        </div>
    </div>
    <div class="col-md-3" style="visibility: hidden;">
        <div class="form-group">
            {!! Form::label("card_month_$row_index", __('lang_v1.month')) !!}
            {!! Form::text("payment[$row_index][card_month]", $payment_line['card_month'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.month'),
                'id' => "card_month_$row_index",
            ]) !!}
        </div>
    </div>
    <div class="col-md-3" style="visibility: hidden;">
        <div class="form-group">
            {!! Form::label("card_year_$row_index", __('lang_v1.year')) !!}
            {!! Form::text("payment[$row_index][card_year]", $payment_line['card_year'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.year'),
                'id' => "card_year_$row_index",
            ]) !!}
        </div>
    </div>

    <div class="clearfix"></div>
</div>


<div class="payment_details_div @if ($payment_line['method'] !== 'cheque') {{ 'hide' }} @endif" data-type="cheque">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("cheque_number_$row_index", __('lang_v1.cheque_no')) !!}
            {!! Form::text("payment[$row_index][cheque_number]", $payment_line['cheque_number'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.cheque_no'),
                'id' => "cheque_number_$row_index",
            ]) !!}
        </div>
    </div>
</div>

<div class="payment_details_div @if ($payment_line['method'] !== 'bank_transfer') {{ 'hide' }} @endif" data-type="bank_transfer">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("bank_account_number_$row_index", __('lang_v1.bank_account_number')) !!}
            {!! Form::text("payment[$row_index][bank_account_number]", $payment_line['bank_account_number'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.bank_account_number'),
                'id' => "bank_account_number_$row_index",
            ]) !!}
        </div>
    </div>
</div>

<div class="payment_details_div @if ($payment_line['method'] !== 'custom_pay_1') {{ 'hide' }} @endif" data-type="custom_pay_1">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("transaction_no_1_$row_index", __('lang_v1.transaction_no')) !!}
            {!! Form::text("payment[$row_index][transaction_no_1]", $payment_line['transaction_no'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.transaction_no'),
                'id' => "transaction_no_1_$row_index",
            ]) !!}
        </div>
    </div>
</div>
<div class="payment_details_div @if ($payment_line['method'] !== 'custom_pay_2') {{ 'hide' }} @endif" data-type="custom_pay_2">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("transaction_no_2_$row_index", __('lang_v1.transaction_no')) !!}
            {!! Form::text("payment[$row_index][transaction_no_2]", $payment_line['transaction_no'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.transaction_no'),
                'id' => "transaction_no_2_$row_index",
            ]) !!}
        </div>
    </div>
</div>
<div class="payment_details_div @if ($payment_line['method'] !== 'custom_pay_3') {{ 'hide' }} @endif" data-type="custom_pay_3">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("transaction_no_3_$row_index", __('lang_v1.transaction_no')) !!}
            {!! Form::text("payment[$row_index][transaction_no_3]", $payment_line['transaction_no'], [
                'class' => 'form-control',
                'placeholder' => __('lang_v1.transaction_no'),
                'id' => "transaction_no_3_$row_index",
            ]) !!}
        </div>
    </div>
</div>

<div class="payment_details_div @if ($payment_line['method'] !== 'boleto') {{ 'hide' }} @endif" data-type="boleto">

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label("data_base_$row_index", 'Qtd parcelas') !!}
            {!! Form::text('', $payment_line['qtd_parcelas'], [
                'class' => 'form-control',
                'placeholder' => 'Qtd parcelas',
                'id' => "qtd_parcelas_$row_index",
                'data-mask="00"',
                'data-mask-reverse="true"',
            ]) !!}
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label("data_base_$row_index", 'Data base parcelamento') !!}
            {!! Form::tel('', $payment_line['data_base'], [
                'class' => 'form-control',
                'placeholder' => 'Data base parcelamento',
                'id' => "data_base_$row_index",
                'data-mask="00/00/0000"',
                'data-mask-reverse="true"',
            ]) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <br>
            <input onclick="diasClick({{ $row_index }})" class="form-check-input" checked type="radio"
                name="flexRadioDefault" id="boleto_check_dias_{{ $row_index }}">
            <label class="form-check-label" for="flexRadioDefault1">
                Dias fixos
            </label>
            <br>
            <input onclick="intervaloClick({{ $row_index }})" class="form-check-input" type="radio"
                name="flexRadioDefault" id="boleto_check_intervalo_{{ $row_index }}">
            <label class="form-check-label" for="flexRadioDefault1">
                Intervalo de dias
            </label>
        </div>
    </div>

    <div class="col-md-2">
        <div class="form-group">
            {!! Form::label("intervalo_$row_index", 'Intervalo') !!}
            {!! Form::text('', $payment_line['intervalo'], [
                'class' => 'form-control',
                'placeholder' => '',
                'id' => "intervalo_$row_index",
                'data-mask="000"',
                'data-mask-reverse="true"',
                'disabled',
            ]) !!}
        </div>
    </div>

    <div class="col-md-2">
        <br>
        <button onclick="gerarFatura()" style="margin-top: 3px;" type="button"
            class="btn bg-navy btn-default gerar-fatura" id="gerar_{{ $row_index }}" title="Gerar">GERAR</button>
    </div>

    <div class="col-md-12">
        <table class="table table-bordered table-striped ajax_view" id="tbl_fatura">
            <thead>
                <tr>
                    <th>Vencimento</th>
                    <th>Documento</th>
                    <th>Valor</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>


</div>
