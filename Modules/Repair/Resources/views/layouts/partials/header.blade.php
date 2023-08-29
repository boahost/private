@if ($__is_repair_enabled)
    @can('repair.create')
        <a href="{{ action('SellPosController@create') . '?sub_type=repair' }}" title="{{ __('repair::lang.add_repair') }}"
            data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-8 mt-10 pull-left">
            <i class="fa fa-wrench"></i>
            <strong>@lang('repair::lang.repair')</strong>
        </a>
    @endcan
@endif
