<div class="flex flex-col gap-2 md:flex-row">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Importando jQuery -->

    <div class="flex-auto flex-grow">
        <div class="row">
            <input type="hidden" class="payment_row_index" value="{{ $row_index }}">
            @php
                $col_class = 'col-md-12';
                if (!empty($accounts)) {
                    $col_class = 'col-md-12';
                }
            @endphp
            <div class="{{ $col_class }}">
                <div class="form-group">
                    <div class="col-md-12">

                        <div class="row">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-money-bill-alt"></i>
                                    </span>
                                    <!-- Campo de valor a ser dividido -->
                                    {{-- <input type="text" class="form-control payment-amount input_number reckreck" id="amount_{{$row_index}}" placeholder="Amount" value="{{$payment_line['amount']}}"> --}}
                                    {!! Form::text("payment[$row_index][amount]", @num_format($payment_line['amount']), [
                            'class' => 'form-control payment-amount input_number reckreck',
                            'required',
                            'id' => "amount_$row_index",
                            'placeholder' => __('sale.amount'),
                        ]) !!}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fas fa-divide"></i>
                                    </span>
                                    <!-- Campo de quantidade de divisão -->
                                    <input type="text" class="form-control input_number" id="quantidade_divisao" placeholder="Dividir para quantas pessoas?">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <h4 id="resultado_divisao"></h4>
                            </div>

                        </div>
                    </div>

                    <script>
                        function calcularDivisao() {
                            var valor = parseFloat($(".reckreck").val().replace(',', '.')); // Remover possíveis formatações
                            var quantidadeDivisao = parseFloat($("#quantidade_divisao").val());

                            if (!isNaN(valor) && !isNaN(quantidadeDivisao) && quantidadeDivisao !== 0) {
                                var resultado = valor / quantidadeDivisao;
                                $("#resultado_divisao").text("R$ " + resultado.toFixed(2).replace(".", ",")); // Fixar em duas casas decimais
                            } else {
                                $("#resultado_divisao").text("");
                            }
                        }
                        $(".reckreck, #quantidade_divisao").on("input", calcularDivisao);
                        calcularDivisao();
                    </script>
                </div>
            </div>
            <div class="col-md-12">
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
                                $inputDate = \Carbon::createFromFormat('d/m/y', $payment_line['vencimento'] ?? '')->format('Y-m-d');
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

            <div class="clearfix"></div>
            @include('sale_pos.partials.payment_type_details')
            <div class="col-md-12">
                <div class="form-group">
                    {!! Form::label("note_$row_index", 'Observação de pagamento:') !!}
                    {!! Form::textarea("payment[$row_index][note]", $payment_line['note'], [
                        'class' => 'form-control',
                        'rows' => 4,
                        'id' => "note_$row_index",
                    ]) !!}
                </div>
            </div>
        </div>
    </div>
    <div class="hidden w-full space-y-6 md:max-w-sm" id="modal_efi_{{ $row_index }}">
        <div class="w-full">
            <img name="efi_qr_code_img" alt="Aguardando Gerar"
                src="https://placehold.co/228x228/fff/222?text=Aguarde..." class="w-full h-full">
        </div>
        <div class="w-full space-y-2">
            <div name="tools" class="">
                <div class="w-full text-right">
                    <div class="input-group">
                        <input class="form-control" id="payment_whatsapp_{{ $row_index }}" placeholder="Nº WhatsApp"
                            value="" autocomplete="off" type="text">
                        <span class="input-group-btn">
                            <a trigger="whatsapp" title="Enviar por WhatsApp"
                                class="text-white btn btn-flat btn-whatsapp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <button trigger="print" title="Imprimir" type="button" class="btn btn-flat btn-secondary">
                                <i class="m-2 fa fa-print"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
            <div name="tools" class="flex gap-2">
                <button trigger="cancel" type="button" class="w-full btn btn-flat btn-secondary">
                    <i class="mr-8 fa fa-chevron-left"></i>
                    Cancelar
                </button>
                <button trigger="update" type="button" class="w-full btn btn-flat btn-primary">
                    <i class="mr-8 fa fa-redo-alt"></i>
                    Atualizar
                </button>
            </div>
            <div name="done" class="hidden">
                <div class="w-full">
                    <button trigger="close" type="button" class="w-full btn btn-flat btn-primary">
                        <i class="mr-8 fa fa-redo-alt"></i>
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
