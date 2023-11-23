$(document).ready(function () {
    $(document).on('click', '.add_payment_modal', function (e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'json',
            success: function (result) {
                if (result.status == 'due') {
                    container.html(result.view).modal('show');
                    __currency_convert_recursively(container);
                    // $('#paid_on').datetimepicker({
                    //     format: moment_date_format + ' ' + moment_time_format,
                    //     ignoreReadonly: true,
                    // });
                    container.find('form#transaction_payment_add_form').validate();
                } else {
                    toastr.error(result.msg);
                }
            },
        });
    });

    $(document).on('click', '.edit_payment', function (e) {
        e.preventDefault();
        var container = $('.edit_payment_modal');

        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function (result) {
                container.html(result).modal('show');
                __currency_convert_recursively(container);
                // $('#paid_on').datetimepicker({
                //     format: moment_date_format + ' ' + moment_time_format,
                //     ignoreReadonly: true,
                // });
                container.find('form#transaction_payment_add_form').validate();
            },
        });
    });

    $(document).on('click', '.view_payment_modal', function (e) {
        e.preventDefault();
        var container = $('.payment_modal');

        $.ajax({
            url: $(this).attr('href'),
            dataType: 'html',
            success: function (result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });

    $(document).on('click', '.delete_payment', function (e) {
        swal({
            title: LANG.sure,
            text: LANG.confirm_delete_payment,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
            if (willDelete) {
                $.ajax({
                    url: $(this).data('href'),
                    method: 'delete',
                    dataType: 'json',
                    success: function (result) {
                        if (result.success === true) {
                            $('div.payment_modal').modal('hide');
                            $('div.edit_payment_modal').modal('hide');
                            toastr.success(result.msg);
                            if (typeof purchase_table != 'undefined') {
                                purchase_table.ajax.reload();
                            }
                            if (typeof sell_table != 'undefined') {
                                sell_table.ajax.reload();
                            }
                            if (typeof expense_table != 'undefined') {
                                expense_table.ajax.reload();
                            }
                            if (typeof ob_payment_table != 'undefined') {
                                ob_payment_table.ajax.reload();
                            }
                            // project Module
                            if (typeof project_invoice_datatable != 'undefined') {
                                project_invoice_datatable.ajax.reload();
                            }
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });

    //view single payment
    $(document).on('click', '.view_payment', function () {
        var url = $(this).data('href');
        var container = $('.view_modal');
        $.ajax({
            method: 'GET',
            url: url,
            dataType: 'html',
            success: function (result) {
                $(container)
                    .html(result)
                    .modal('show');
                __currency_convert_recursively(container);
            },
        });
    });

    $('body').on('click', '[trigger="gen_pix"]', async (e) => {
        console.log('gen_pix');

        const dataset = e.currentTarget.dataset

        let prompt;
        let whatsapp = '';

        if (parseFloat(dataset.remaining) <= 0.00) {
            return swal({
                title: 'Opa!',
                text: 'O valor do pedido deve ser maior que R$ 0,01!',
                icon: 'error',
            })
        }

        prompt = await swal({
            title: `Deseja gerar um PIX com o valor de ${__currency_trans_from_en(dataset.remaining)}?`,
            buttons: {
                cancel: 'Não',
                confirm: 'Sim',
            },
        })

        if (!prompt)
            return

        const input = $(
            `<input id="pix_whatsapp" class="form-control input-lg" value="${dataset.phone}" placeholder="Ex: 11999999999" />`
        )

        input.on('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '')
        })

        while (prompt && whatsapp.length != 11 && whatsapp.length != 10) {
            prompt = await swal({
                title: `Informe o whatsapp do cliente${whatsapp.length ? ' corretamente' : ''}`,
                content: input[0],
                button: {
                    text: "Gerar PIX",
                    closeModal: false,
                },
            })

            whatsapp = input[0].value
        }

        if (!prompt)
            return

        try {
            const response = await $.ajax({
                url: `/efi/pix/${dataset.id}`,
                method: 'POST'
            })

            if (response.status == 'CONCLUIDA') {
                return swal({
                    title: 'Opa!',
                    text: 'O pedido já foi pago!',
                    content: html[0],
                })
            }

            if (!response.qrcode) {
                return swal({
                    title: 'Erro!',
                    text: 'Ocorreu um erro ao gerar o PIX!',
                    icon: 'error',
                })
            }

            const text =
                `Olá, ${dataset.customer_name}!\n\nSeu pedido foi confirmado e está aguardando o pagamento.\n\nClique no link abaixo para efetuar o pagamento seguro via PIX:\n\n${response.qrcode.linkVisualizacao}\n\nAo abrir o link, clique em "Copiar" e cole no aplicativo do seu banco ou escaneie o QRCode.\n\nMuito Obrigado!`

            const params = new URLSearchParams()

            params.append('phone', `55${whatsapp}`)
            params.append('text', text)

            const html = $(`
                <div>
                    <a href="https://api.whatsapp.com/send?${params.toString()}" target="_blank">Enviar link para o cliente</a>
                </div>
           `)

            swal({
                title: 'PIX gerado com sucesso',
                content: html[0],
            })
        } catch (error) {
            console.log(error);
            swal({
                title: 'Erro!',
                text: error.responseJSON.error ||
                    'Ocorreu um erro ao gerar o PIX!',
                icon: 'error',
            })
        }
    })
});
