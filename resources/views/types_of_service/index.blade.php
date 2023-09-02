@extends('layouts.app')
@section('title', 'Tipos de serviço')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Tipos de serviço @show_tooltip(__('lang_v1.types_of_service_help_long'))
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" 
                    data-href="{{action('TypesOfServiceController@create')}}" 
                    data-container=".type_of_service_modal">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endslot
        @can('brand.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="types_of_service_table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>@lang( 'lang_v1.description' )</th>
                            <th>Taxa de Embalagem</th>
                            <th>@lang( 'messages.action' )</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent

    <div class="modal fade type_of_service_modal contains_select2" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection
