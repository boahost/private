@extends('layouts.app')
@section('title', 'Categoria de contas')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Categoria de contas
        <small></small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Todas as categorias'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                data-href="{{action('ExpenseCategoryController@create')}}" 
                data-container=".expense_category_modal">
                <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="expense_category_table">
                <thead>
                    <tr>
                        <th>@lang( 'expense.category_name' )</th>
                        <th>Código da categoria</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade expense_category_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
