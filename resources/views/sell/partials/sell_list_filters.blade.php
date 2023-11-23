@if(empty($only) || in_array('sell_list_filter_location_id', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_location_id',  __('purchase.business_location') . ':') !!}

        {!! Form::select('sell_list_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all') ]); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_customer_id', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_customer_id',  __('contact.customer') . ':') !!}
        {!! Form::select('sell_list_filter_customer_id', $customers, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_payment_status', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_payment_status',  __('purchase.payment_status') . ':') !!}
        {!! Form::select('sell_list_filter_payment_status', ['paid' => __('lang_v1.paid'), 'due' => __('lang_v1.due'), 'partial' => __('lang_v1.partial'), 'overdue' => __('lang_v1.overdue')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_date_range', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']); !!}
    </div>
</div>
@endif
@if((empty($only) || in_array('created_by', $only)) && !empty($sales_representative))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('created_by',  __('report.user') . ':') !!}
        {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sales_cmsn_agnt', $only))
@if(!empty($is_cmsn_agent_enabled))
    <div class="col-md-3">
        <div class="form-group">
            {!! Form::label('sales_cmsn_agnt',  __('lang_v1.sales_commission_agent') . ':') !!}
            {!! Form::select('sales_cmsn_agnt', $commission_agents, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!}
        </div>
    </div>
@endif
@endif
@if(empty($only) || in_array('service_staffs', $only))
    @if(!empty($service_staffs))
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('service_staffs', __('restaurant.service_staff') . ':') !!}
                {!! Form::select('service_staffs', $service_staffs, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]); !!}
            </div>
        </div>
    @endif
@endif

<div class="col-md-3">

    <div class="form-group">
        <label> Formas de pagamento </label>
        <select id="formas_pagamento" style="width:100%" class="form-control select2">
            <option value="sem_pgto">Todas </option>
            @foreach($payment_types ?? [] as $row => $value)
                <option value="{{ $row }}"> {!!  $value !!} </option>
            @endforeach
        </select>

        {{-- {!! Form::select('formas_pagamento', $payment_types, null, ['class' => 'form-control select2', 'style' => 'width:100%']); !!} --}}


    </div>

</div>

@if(empty($only) || in_array('only_subscriptions', $only))
<div class="col-md-3">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
              {!! Form::checkbox('only_subscriptions', 1, false,
              [ 'class' => 'input-icheck', 'id' => 'only_subscriptions']); !!} {{ __('lang_v1.subscriptions') }}
            </label>
        </div>
    </div>
</div>
@endif

{{-- <div class="col-md-3">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <br>
              {!! Form::checkbox('ecommerce', 1, false,
              [ 'class' => 'input-icheck', 'id' => 'ecommerce']); !!} Ecommerce
            </label>
        </div>
    </div>
</div> --}}
