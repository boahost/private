@extends('layouts.app')
@section('title', 'Contas a receber')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Contas a receber</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_category_id', 'Categoria:') !!}
                        {!! Form::select('expense_category_id', $categories, null, ['placeholder' =>
                        __('report.all'), 'class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'expense_category_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'expense_date_range', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('expense_payment_status',  __('purchase.payment_status') . ':') !!}
                        {!! Form::select('expense_payment_status', ['1' => 'Recebido', '-1' => 'Pendente'], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => ''])
                @can('expense.access')
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary" href="{{action('RevenueController@create')}}">
                            <i class="fa fa-plus"></i> @lang('messages.add')</a>
                        </div>

                    @endslot
                @endcan

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="revenue_table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>@lang('messages.action')</th>
                                <th>Cliente</th>
                                <th>Vencimento</th>
                                <th>Referência</th>
                                <th>Categoria</th>
                                <th>@lang('business.location')</th>
                                <th>Status</th>
                                <th>Valor total</th>
                                <th>Valor recebido</th>
                                <th>Observação</th>
                                <th>@lang('lang_v1.added_by')</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 text-center footer-total">
                                <td colspan="7"><strong>@lang('sale.total'):</strong></td>
                                <td id="footer_payment_status_count"></td>
                                <td><span class="display_currency" id="footer_revenue_total" data-currency_symbol ="true"></span></td>
                                <td><span class="display_currency" id="footer_total_receive" data-currency_symbol ="true"></span></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>

</section>
<!-- /.content -->
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog"
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
 <script src="{{ asset('js/revenue.js') }}"></script>

@endsection
