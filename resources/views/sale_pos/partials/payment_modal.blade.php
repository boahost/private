<div class="modal fade" tabindex="-1" role="dialog" id="modal_payment">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('lang_v1.payment')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-9">
                        <div class="row">
                            <div id="payment_rows_div">
                                @foreach ($payment_lines as $payment_line)
                                    @if ($payment_line['is_return'] == 1)
                                        @php
                                            $change_return = $payment_line;
                                        @endphp

                                        @continue
                                    @endif

                                    @include('sale_pos.partials.payment_row', [
                                        'removable' => !$loop->first,
                                        'row_index' => $loop->index,
                                        'payment_line' => $payment_line,
                                    ])
                                @endforeach
                            </div>
                            <input type="hidden" id="payment_row_index" value="{{ count($payment_lines) }}">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-primary btn-block"
                                    id="add-payment-row">@lang('sale.add_payment_row')</button>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('sale_note', __('sale.sell_note') . ':') !!}
                                    {!! Form::textarea('sale_note', !empty($transaction) ? $transaction->additional_notes : null, [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => __('sale.sell_note'),
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('staff_note', __('sale.staff_note') . ':') !!}
                                    {!! Form::textarea('staff_note', !empty($transaction) ? $transaction->staff_note : null, [
                                        'class' => 'form-control',
                                        'rows' => 3,
                                        'placeholder' => __('sale.staff_note'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="box box-solid bg-orange">
                            <div class="box-body">
                                <div class="col-md-12">
                                    <strong>
                                        @lang('lang_v1.total_items'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold total_quantity">0</span>
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('sale.total_payable'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold total_payable_span">0</span>
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('lang_v1.total_paying'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold total_paying">0</span>
                                    <input type="hidden" id="total_paying_input">
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('lang_v1.change_return'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold change_return_span">0</span>
                                    {!! Form::hidden('change_return', $change_return['amount'], [
                                        'class' => 'form-control change_return input_number',
                                        'required',
                                        'id' => 'change_return',
                                        'placeholder' => __('sale.amount'),
                                        'readonly',
                                    ]) !!}
                                    <!-- <span class="lead text-bold total_quantity">0</span> -->
                                    @if (!empty($change_return['id']))
                                        <input type="hidden" name="change_return_id"
                                            value="{{ $change_return['id'] }}">
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('lang_v1.balance'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold balance_due">0</span>
                                    <input type="hidden" id="in_balance_due" value=0>
                                </div>



                            </div>
                            <!-- /.box-body -->
                        </div>

                        <div class="box box-solid bg-white">
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-lg-12 flex items-center justfy-between mb-10">
                                        Pagamentos PIX
                                        <button trigger="pixlist_refresh" type="button"
                                            class="btn btn-flat btn-secondary">
                                            <i class="fa fa-redo-alt"></i>
                                        </button>
                                    </div>
                                </div>
                                <ul id="pixlist" class="list-group" style="max-height: 200px; overflow-y: auto;">
                                    <li class="list-group-item">
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                <button type="submit" class="btn btn-primary" id="pos-save">@lang('sale.finalize_payment')</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Used for express checkout card transaction -->
<div class="modal fade" tabindex="-1" role="dialog" id="card_details_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang('lang_v1.card_transaction_details')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('card_number', __('lang_v1.card_no')) !!}
                                {!! Form::text('', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.card_no'),
                                    'id' => 'card_number',
                                    'autofocus',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('card_holder_name', 'CNPJ') !!}
                                {!! Form::text('', null, ['class' => 'form-control', 'placeholder' => 'CNPJ', 'id' => 'card_holder_name']) !!}
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                {!! Form::label('card_transaction_number', 'Código de autorização') !!}
                                {!! Form::text('', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Código de autorização',
                                    'id' => 'card_transaction_number',
                                ]) !!}
                            </div>
                        </div>
                        <!-- <div class="clearfix"></div> -->
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('card_type', __('lang_v1.card_type')) !!}
                                {!! Form::select('', ['credit' => 'Crédito', 'debit' => 'Débito'], 'card_type', [
                                    'class' => 'form-control select2',
                                    'id' => 'card_type',
                                    'style="width: 100%"',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('card_security', 'Bandeira') !!}

                                {!! Form::select('', App\Models\Transaction::bandeiras(), 'card_security', [
                                    'class' => 'form-control select2',
                                    'id' => 'card_security',
                                    'style="width: 100%"',
                                ]) !!}
                            </div>
                        </div>

                        <div class="col-md-3" style="visibility: hidden">
                            <div class="form-group">
                                {!! Form::label('card_month', __('lang_v1.month')) !!}
                                {!! Form::text('', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.month'),
                                    'id' => 'card_month',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3" style="visibility: hidden">
                            <div class="form-group">
                                {!! Form::label('card_year', __('lang_v1.year')) !!}
                                {!! Form::text('', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.year'), 'id' => 'card_year']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="pos-save-card">@lang('sale.finalize_payment')</button>
            </div>

        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function() {
        $(() => {
            const keys = [
                'd',
                'f',
                'g',
                'h',
                'j',
                'k',
                'l',
                'ç',
            ]

            $('.payment_types_dropdown .radio-button').each((index, el) => {
                const key = keys[index]

                if (!key)
                    return

                const label = $(el).find('label')

                label.attr('title', `Atalho: shift+${key}`).find('small').text(`(${key})`)

                Mousetrap.bind(`shift+${key}`, function(e) {
                    e.preventDefault();
                    $(el).find('input').prop('checked', true).trigger('change')
                });
            })


            $('body').on('change', '.payment_types_dropdown input', (e) => {
                const el = $(e.currentTarget)

                const payment_type_id = el.val()

                if (payment_type_id == 'pix_efi') {
                    return showModal(e)
                }
            })

            function showModal(e) {
                const el = $(e.currentTarget)
                const row = el.closest('.payment_row')
                const row_index = row.find('.payment_row_index').val()
                const modal = $(`#modal_efi_${row_index}`)

                // $(`[name="payment[${row_index}][cpf]"]`).val()

                let amount = $(`[name="payment[${row_index}][amount]"]`).val().replace(',', '.')
                amount = parseFloat(amount)

                const customer_name = $('#default_customer_name').val()
                const whatsapp_number = row.find(`#payment_whatsapp_${row_index}`)

                whatsapp_number[0].addEventListener('keydown', () => {
                    //   verifica se a tela é enter e clicka no botão whatsapp
                    if (e.keyCode == 13) {
                        e.preventDefault();
                    }
                })

                whatsapp_number.on('input', function(e) {
                    const value = $(this).val()
                    const number = value.replace(/\D/g, '')

                    console.log(e);

                    if (number.length < 11 || number.length > 11)
                        return modal_els.triggers.whatsapp.removeAttr('target').removeAttr(
                            'href')

                    const data = modal.data('pix')

                    if (!data)
                        return;

                    const params = new URLSearchParams()

                    // Copie o código e cole no aplicativo do seu banco:\n\n'${data.qrcode.qrcode}\n\n

                    const text =
                        `Olá, ${customer_name}!\n\nSeu pedido foi confirmado e está aguardando o pagamento.\n\nClique no link abaixo para efetuar o pagamento seguro via PIX:\n\n${data.qrcode.linkVisualizacao}\n\nAo abrir o link, clique em "Copiar" e cole no aplicativo do seu banco ou escaneie o QRCode.\n\nMuito Obrigado!`

                    params.append('phone', `55${number}`)
                    params.append('text', text)

                    modal_els.triggers.whatsapp.attr('target', '_blank').attr('href',
                        `https://api.whatsapp.com/send?${params.toString()}`
                    )
                })

                if (amount < 0.01) {
                    return swal(
                        'Valor não informado',
                        'Primeiro, informe o valor para gerar o QRCode',
                        'warning'
                    )
                }

                // const cpf = ''
                // if (!cpf) {
                //     return swal('CPF não informado',
                //         'Informe o CPF para gerar o QRCode',
                //         'warning')
                // }

                const modal_els = {
                    qr_code: modal.find('[name="efi_qr_code_img"]'),
                    triggers: {
                        print: modal.find('[trigger="print"]'),
                        whatsapp: modal.find('[trigger="whatsapp"]'),
                        close: modal.find('[trigger="close"]'),
                        cancel: modal.find('[trigger="cancel"]'),
                        update: modal.find('[trigger="update"]'),
                    }
                }

                modal_els.qr_code.attr('src',
                    'https://placehold.co/228x228/fff/222?text=Aguarde...');

                function buttonDisables(disabled = false) {
                    if (disabled == true) {
                        modal_els.triggers.cancel.attr('disabled', 'disabled')
                        modal_els.triggers.update.attr('disabled', 'disabled')
                    } else {
                        modal_els.triggers.cancel.removeAttr('disabled')
                        modal_els.triggers.update.removeAttr('disabled')
                    }
                }

                let timer;

                function refresh() {
                    const data = modal.data('pix')
                    const txid = data && data.txid

                    if (!txid) {
                        console.log('txid não encontrado');
                        return;
                    }

                    buttonDisables(true)

                    $.ajax({
                        method: 'GET',
                        url: `/efi/pix/${txid}`,
                        dataType: 'json',
                        success: function(responseJSON) {
                            console.log(responseJSON);

                            buttonDisables(false)

                            if (responseJSON.status == 'CONCLUIDA') {
                                clearInterval(timer);

                                modal_els.qr_code.attr('src',
                                    'https://placehold.co/228x228/4caf50/fff?text=Pago!'
                                );

                                row.find('[name="tools"]').addClass('hidden')
                                row.find('[name="done"]').removeClass('hidden')

                                row.find(':input:not([trigger="close"])').attr(
                                    'disabled', 'disabled')

                                reloadPixList()
                            }
                        },
                        error: function(error) {
                            buttonDisables(false)
                            reloadPixList()
                            console.log(error);
                        }
                    });
                }

                function setTimer() {
                    timer = setInterval(() => {
                        refresh()
                    }, 8000);
                }

                if (modal.data('pix'))
                    return setTimer();

                $.ajax({
                    method: 'POST',
                    url: '/efi/pix',
                    data: {
                        customer_name,
                        amount,
                        // cpf,
                    },
                    dataType: 'json',
                    success: (response) => {
                        buttonDisables(false)

                        modal.data({
                            pix: response
                        })

                        modal_els.qr_code.attr('src', response.qrcode.imagemQrcode);

                        setTimer();
                        reloadPixList()
                    },
                    error: (error) => {
                        console.log(error);
                        const responseJSON = error.responseJSON

                        buttonDisables(false)
                        modal_els.qr_code.attr('src',
                            'https://placehold.co/228x228/fff/222?text=Tente%20Novamente'
                        );

                        if (responseJSON && responseJSON.error) {
                            return swal('Erro ao erar PIX', responseJSON.error, 'error')
                        }

                        return swal('Erro ao erar PIX', 'Por favor, tente novamente',
                            'error')
                        reloadPixList()
                    }
                });

                buttonDisables(true)

                modal.removeClass('hidden');

                modal_els.triggers.whatsapp.on('click', function() {
                    const value = whatsapp_number.val()
                    const number = value.replace(/\D/g, '')

                    if (number.length < 11 || number.length > 11) {
                        return swal(
                            'Número inválido',
                            'Informe o número de celular com DDD',
                            'warning'
                        )
                    }

                    document.dispatchEvent(e)
                })

                modal_els.triggers.cancel.on('click', function() {
                    modal.addClass('hidden');
                    modal.removeData('pix')
                    $(`[name="payment[0][method]"]:checked`).prop('checked', false)

                    row.find('[name="tools"]').removeClass('hidden')
                    row.find('[name="done"]').addClass('hidden')

                    clearInterval(timer);
                })

                modal_els.triggers.print.on('click', function() {
                    $('#receipt_section').html(
                        `${modal_els.qr_code.parent().html()}<br/><br/>${customer_name}<br/>${amount}`
                    );

                    __print_receipt('receipt_section')
                })

                modal_els.triggers.close.on('click', function() {
                    modal.addClass('hidden');
                })

                modal_els.triggers.update.on('click', function() {
                    refresh();
                })
            }

            const ul_pixlist = $('#pixlist')
            const btn_pixlist_refresh = $('[trigger="pixlist_refresh"]')

            reloadPixList()

            btn_pixlist_refresh.on('click', function(e) {
                e.preventDefault();
                $(this).attr('disabled', 'disabled')
                reloadPixList()
            })

            var options_date = {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            };

            function reloadPixList() {
                $.ajax({
                    method: 'GET',
                    url: '/efi/pix/list',
                    dataType: 'json',
                    success: (response) => {
                        ul_pixlist.html('')
                        const cobs = response.cobs

                        cobs.forEach((cob) => {
                            const li = document.createElement('li')
                            li.classList.add('list-group-item')

                            if (cob.status == 'CONCLUIDA')
                                li.classList.add('list-group-item-success')

                            const div = document.createElement('div')
                            div.classList.add('flex')
                            div.classList.add('justfy-between')

                            const span = document.createElement('span')
                            const data_original = new Date(cob.calendario.criacao);
                            span.innerText = data_original.toLocaleDateString(
                                'pt-BR', options_date);

                            const span2 = document.createElement('span')
                            span2.innerText = cob.valor.original

                            div.appendChild(span)
                            div.appendChild(span2)

                            li.appendChild(div)

                            ul_pixlist.append(li)

                            btn_pixlist_refresh.removeAttr('disabled')
                        })
                    },
                    error: (error) => {
                        console.log(error);
                        btn_pixlist_refresh.removeAttr('disabled')
                    }
                });
            }
        })
    })
</script>
{{-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"
    integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous" /> --}}
