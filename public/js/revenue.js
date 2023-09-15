$(document).ready(function() {

    // var banks_table = $('#revenue_table').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     ajax: '/revenues',
    //     columnDefs: [ {
    //         "targets": [0],
    //         "orderable": false,
    //         "searchable": false
    //     } ],

    // });

    if ($('#expense_date_range').length == 1) {
        $('#expense_date_range').daterangepicker(
            dateRangeSettings, 
            function(start, end) {
                $('#expense_date_range').val(
                    start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                revenues_table.ajax.reload();
            }
            );

        $('#expense_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#product_sr_date_filter').val('');
            revenues_table.ajax.reload();
        });
    }

    $('#location_id').change( function(ev, picker) {
        revenues_table.ajax.reload();
    });

    $('#expense_payment_status').change( function(ev, picker) {
        revenues_table.ajax.reload();
    });

    var revenues_table = $('#revenue_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[1, 'desc']],
        ajax: {
            url: '/revenues',
            data: function(d) {
                console.log(d)
                d.location_id = $('select#location_id').val();
                d.expense_category_id = $('select#expense_category_id').val();
                d.status = $('select#expense_payment_status').val();
                d.start_date = $('input#expense_date_range')
                    .data('daterangepicker')
                    .startDate.format('YYYY-MM-DD');
                d.end_date = $('input#expense_date_range')
                    .data('daterangepicker')
                    .endDate.format('YYYY-MM-DD');
            },
        },
        columns: [
        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
        { data: 'action', name: 'action', orderable: false, searchable: false },
        { data: 'contact', name: 'contact' },
        { data: 'vencimento', name: 'vencimento' },
        { data: 'referencia', name: 'referencia' },
        { data: 'expense_category_id', name: 'expense_category_id' },
        { data: 'location_name', name: 'location_name' },
        { data: 'status', name: 'status', orderable: false },
        { data: 'valor_total', name: 'valor_total' },
        { data: 'valor_recebido', name: 'valor_recebido' },
        { data: 'observacao', name: 'observacao' },
        { data: 'created_by', name: 'created_by'},
        ],
        fnDrawCallback: function(oSettings) {
            var revenue_total = sum_table_col($('#revenue_table'), 'final-total');

            $('#footer_revenue_total').text(revenue_total);
            var total_receive = sum_table_col($('#revenue_table'), 'valor-recebido');
            $('#footer_total_receive').text(total_receive);

            // $('#footer_payment_status_count').html(
            //     __sum_status_html($('#revenue_table'), 'payment-status')
            // );

            __currency_convert_recursively($('#revenue_table'));
        }
    });


    $(document).on('click', 'a.delete_revenue', function(){
        swal({
            title: LANG.sure,
            text: 'Esta conta será excluida',
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                var href = $(this).data('href');
                var data = $(this).serialize();
                $.ajax({
                    method: "DELETE",
                    url: href,
                    dataType: "json",
                    data: data,
                    success: function(result){
                        if(result.success == true){
                            toastr.success(result.msg);
                            revenues_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});

function selecionarVarios(){
    if($('.check-boleto').css("visibility") == "hidden"){
        $('.check-boleto').css('visibility', 'visible')
        $('.btn-gerar-boletos').css('display', 'block')
    }else{
        $('.check-boleto').css('visibility', 'hidden')
        $('.btn-gerar-boletos').css('display', 'none')
    }

}

var BOELTOS = []
function boleto_selecionado(id){

    if($('.check-'+id).is(':checked')){
        BOELTOS.push(id)
    }else{
        let temp = BOELTOS.filter((x) => {
            return x != id
        })
        BOELTOS = temp
    }
}

function gerarBoletos(){
    if(BOELTOS.length > 0){
        var path = window.location.protocol + '//' + window.location.host

        location.href = path + '/boletos/gerarMultiplos/'+BOELTOS
    }else{
        swal("Atenção", "Selecione 1 ou mais boletos.", "warning")
    }
}
