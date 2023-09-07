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
            <div class="flex gap-1 payment_types_dropdown" style="overflow-x: scroll;">
                @foreach ($payment_types as $key => $payment_type)
                    <div class="radio-button">
                        <input required type="radio" class="hidden" id="{{ "method_{$row_index}_$key" }}"
                            name="payment[{{ $row_index }}][method]" value="{{ $key }}">
                        <label for="{{ "method_{$row_index}_$key" }}" class="btn btn-flat btn-secondary">
                            <small></small>
                            {{ $payment_type }}
                            {{-- <br /> --}}
                        </label>
                    </div>
                @endforeach
                {{-- {!! Form::select("payment[$row_index][method]", $payment_types, $payment_line['method'], [
                    'class' => 'form-control col-md-12 payment_types_dropdown',
                    'required',
                    'id' => "method_$row_index",
                    'style' => 'width:100%;',
                ]) !!} --}}
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
        {{-- <div class="col-md-8">
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
        </div> --}}

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
                        <span name="tools" class="row">
                            <div class="col-xs-12 text-right">
                                <div class="input-group">
                                    <input class="form-control" id="payment_whatsapp_{{ $row_index }}"
                                        placeholder="Nº WhatsApp" value="" autocomplete="off" type="text">
                                    <span class="input-group-btn">
                                        <a trigger="whatsapp" title="Enviar por WhatsApp"
                                            class="btn btn-flat btn-whatsapp text-white">
                                            <i class="fab fa-whatsapp"></i>
                                        </a>
                                        <button trigger="print" title="Imprimir" type="button"
                                            class="btn btn-flat btn-secondary">
                                            <i class="fa fa-print m-2"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </span>
                        <span name="tools" class="row">
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
                        </span>
                        <span name="done" class="row hidden">
                            <div class="col-xs-12">
                                <button trigger="close" type="button" class="btn btn-flat btn-primary btn-block">
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
