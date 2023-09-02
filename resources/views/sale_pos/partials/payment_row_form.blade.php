<div class="row">
    <input type="hidden" class="payment_row_index" value="{{ $row_index }}">
    @php
        $col_class = 'col-md-6';
        if (!empty($accounts)) {
            $col_class = 'col-md-4';
        }
    @endphp
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("amount_$row_index", 'Valor' . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fas fa-money-bill-alt"></i>
                </span>
                {!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount']), [
                    'class' => 'form-control payment-amount input_number',
                    'required',
                    'id' => "amount_$row_index",
                    'placeholder' => __('sale.amount'),
                ]) !!}
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="form-group">
            {!! Form::label("method_$row_index", 'Forma de pagamento' . ':*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fas fa-list"></i>
                </span>
                @foreach ($payment_types as $payment_type)
                    <button class="btn btn-flat btn-secondary">{{ $payment_type }}</button>
                @endforeach
                {!! Form::select("payment[$row_index][method]", $payment_types, $payment_line['method'], [
                    'class' => 'form-control col-md-12 payment_types_dropdown',
                    'required',
                    'id' => "method_$row_index",
                    'style' => 'width:100%;',
                ]) !!}
            </div>
        </div>
    </div>
    <div class="{{ $col_class }}">
        <div class="form-group">
            {!! Form::label("vencimento_$row_index", 'Vencimento:*') !!}
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar"></i>
                </span>
                @php
                    $inputDate = '';
                    try {
                        $inputDate = \Carbon::createFromFormat('d/m/Y', $payment_line['vencimento'] ?? '')->format('Y-m-d');
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                @endphp
                {!! Form::date("payment[$row_index][vencimento]", $inputDate, [
                    'class' => 'form-control payment-vencimento',
                    'id' => "payment_$row_index",
                    'required',
                ]) !!}
                {{-- {!! Form::text("payment[$row_index][vencimento]", $payment_line['vencimento'], [
                    'class' => 'form-control payment-vencimento',
                    'id' => "payment_$row_index",
                    'required',
                    'placeholder' => '00/00/00',
                    'data-mask="00/00/00"',
                    'data-mask-reverse="true"',
                ]) !!} --}}

            </div>
        </div>
    </div>

    @if (!empty($accounts))
        <div class="{{ $col_class }}">
            <div class="form-group">
                {!! Form::label("account_$row_index", __('lang_v1.payment_account') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fas fa-money-bill-alt"></i>
                    </span>
                    {!! Form::select(
                        "payment[$row_index][account_id]",
                        $accounts,
                        !empty($payment_line['account_id']) ? $payment_line['account_id'] : '',
                        ['class' => 'form-control select2', 'id' => "account_$row_index", 'style' => 'width:100%;'],
                    ) !!}
                </div>
            </div>
        </div>
    @endif


    <div class="payment_details_div @if ($payment_line['method'] !== 'pix_efi') {{ 'hide' }} @endif" data-type="pix_efi">
        <div class="col-md-8">
            <div class="form-group">
                {!! Form::label("cpf_number_$row_index", __('invoice.cpf')) !!}
                <div class="input-group">
                    {!! Form::text("payment[$row_index][cpf]", $payment_line['cpf'] ?? '', [
                        'class' => 'form-control cpf_cnpj',
                        'placeholder' => __('invoice.cpf'),
                        'id' => "cpf_number_$row_index",
                        'data-mask="000.000.000-00"',
                        'data-mask-reverse="true"',
                    ]) !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat" trigger="gen_efi_qr_code">
                            Gerar QRCode <i class="fa fa-edit ml-2"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <style>
            .over-modal .body {
                position: fixed;
                z-index: 2000;
                margin: 0 auto;
                max-width: 300px;
                left: 0;
                top: 50px;
                background-color: white;
                right: 0;
                border-radius: 8px;
                overflow: hidden;
            }

            .over-modal .modal-backdrop {
                z-index: 1999;
            }
        </style>

        <div id="modal_efi_{{ $row_index }}" class="over-modal hidden">
            <div class="modal-backdrop in"></div>
            <div class="text-center body">
                <div class="row">
                    <div class="col-xs-12">
                        <img src="https://placehold.co/228x228/fff/222?text=Aguarde..." width="300" height="300"
                            name="efi_qr_code_img" alt="Aguardando Gerar">
                    </div>
                    <div class="col-xs-12">
                        <span class="row hidden">
                            <div class="col-xs-12 text-right">
                                <button trigger="whatsapp" type="button" class="bg-green btn btn-flat">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" width="16px" height="16px"
                                        viewBox="-1.66 0 740.824 740.824" class="m-2">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M630.056 107.658C560.727 38.271 468.525.039 370.294 0 167.891 0 3.16 164.668 3.079 367.072c-.027 64.699 16.883 127.855 49.016 183.523L0 740.824l194.666-51.047c53.634 29.244 114.022 44.656 175.481 44.682h.151c202.382 0 367.128-164.689 367.21-367.094.039-98.088-38.121-190.32-107.452-259.707m-259.758 564.8h-.125c-54.766-.021-108.483-14.729-155.343-42.529l-11.146-6.613-115.516 30.293 30.834-112.592-7.258-11.543c-30.552-48.58-46.689-104.729-46.665-162.379C65.146 198.865 202.065 62 370.419 62c81.521.031 158.154 31.81 215.779 89.482s89.342 134.332 89.311 215.859c-.07 168.242-136.987 305.117-305.211 305.117m167.415-228.514c-9.176-4.591-54.286-26.782-62.697-29.843-8.41-3.061-14.526-4.591-20.644 4.592-6.116 9.182-23.7 29.843-29.054 35.964-5.351 6.122-10.703 6.888-19.879 2.296-9.175-4.591-38.739-14.276-73.786-45.526-27.275-24.32-45.691-54.36-51.043-63.542-5.352-9.183-.569-14.148 4.024-18.72 4.127-4.11 9.175-10.713 13.763-16.07 4.587-5.356 6.116-9.182 9.174-15.303 3.059-6.122 1.53-11.479-.764-16.07-2.294-4.591-20.643-49.739-28.29-68.104-7.447-17.886-15.012-15.466-20.644-15.746-5.346-.266-11.469-.323-17.585-.323-6.117 0-16.057 2.296-24.468 11.478-8.41 9.183-32.112 31.374-32.112 76.521s32.877 88.763 37.465 94.885c4.587 6.122 64.699 98.771 156.741 138.502 21.891 9.45 38.982 15.093 52.307 19.323 21.981 6.979 41.983 5.994 57.793 3.633 17.628-2.633 54.285-22.19 61.932-43.616 7.646-21.426 7.646-39.791 5.352-43.617-2.293-3.826-8.41-6.122-17.585-10.714">
                                        </path>
                                    </svg>
                                </button>
                                <button trigger="print" type="button" class="btn btn-flat btn-secondary">
                                    <i class="fa fa-print m-2"></i>
                                </button>
                            </div>
                        </span>
                        <span class="row">
                            <div class="col-xs-6">
                                <button trigger="cancel" type="button" class="btn btn-flat btn-secondary btn-block">
                                    <i class="fa fa-chevron-left mr-8"></i>
                                    Cancelar
                                </button>
                            </div>
                            <div class="col-xs-6">
                                <button trigger="update" type="button" class="btn btn-flat btn-primary btn-block">
                                    <i class="fa fa-redo-alt mr-8"></i>
                                    Atualizar
                                </button>
                            </div>
                            <div class="col-xs-12">
                                <button trigger="close" type="button"
                                    class="btn btn-flat btn-primary btn-block hidden">
                                    <i class="fa fa-redo-alt mr-8"></i>
                                    OK
                                </button>
                            </div>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
    @include('sale_pos.partials.payment_type_details')
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label("note_$row_index", 'Observação de pagamento:') !!}
            {!! Form::textarea("payment[$row_index][note]", $payment_line['note'], [
                'class' => 'form-control',
                'rows' => 3,
                'id' => "note_$row_index",
            ]) !!}
        </div>
    </div>
</div>
