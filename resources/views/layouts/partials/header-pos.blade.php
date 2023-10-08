<!-- default value -->
@php
    $go_back_url = action('SellPosController@index');
    $transaction_sub_type = '';
    $view_suspended_sell_url = action('SellController@index') . '?suspended=1';
    $pos_redirect_url = action('SellPosController@create');
@endphp
@if (!empty($pos_module_data))
    @foreach ($pos_module_data as $key => $value)
        @php
            if (!empty($value['go_back_url'])) {
                $go_back_url = $value['go_back_url'];
            }
            if (!empty($value['transaction_sub_type'])) {
                $transaction_sub_type = $value['transaction_sub_type'];
                $view_suspended_sell_url .= '&transaction_sub_type=' . $transaction_sub_type;
                $pos_redirect_url .= '?sub_type=' . $transaction_sub_type;
            }
        @endphp
    @endforeach
@endif
<input type="hidden" name="transaction_sub_type" id="transaction_sub_type" value="{{ $transaction_sub_type }}">
@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header">
    <input type="hidden" id="pos_redirect_url" value="{{ $pos_redirect_url }}">
    <div class="row">
        <div class="col-md-6">
            <div class="m-6 mt-5 hidden-xs">
                <p><strong>@lang('sale.location'):</strong>
                    @if (!empty($default_location->name))
                        {{ $default_location->name }}
                    @elseif(!empty($transaction->location_id))
                        {{ $transaction->location->name }}
                    @endif {{ @format_datetime('now') }} <i
                        class="fa fa-keyboard hover-q text-muted" aria-hidden="true" data-container="body"
                        data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')"
                        data-html="true" data-trigger="hover" data-original-title="" title=""></i>
                </p>
            </div>
        </div>
        <div class="col-md-6">
            {{-- <div class="flex gap-1 m-5 justfy-end"> --}}
            <a href="{{ $go_back_url }}" title="{{ __('lang_v1.go_back') }}"
                class="btn m-4 btn-info btn-flat pull-right">
                <strong><i class="fa fa-backward fa-lg"></i></strong>
            </a>
            <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}"
                class="btn m-4 btn-danger btn-flat btn-modal pull-right" data-container=".close_register_modal"
                data-href="{{ action('CashRegisterController@getCloseRegister') }}">
                <strong><i class="fa fa-lg fa-times"></i></strong>
            </button>
            <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}"
                class="btn m-4 btn-success btn-flat btn-modal pull-right" data-container=".register_details_modal"
                data-href="{{ action('CashRegisterController@getRegisterDetails') }}">
                <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
            </button>
            <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button"
                class="btn m-4 btn-success btn-flat btn-modal pull-right" data-trigger="click"
                data-content='@include('layouts.partials.calculator')' data-html="true" data-placement="bottom">
                <strong><i class="fa fa-calculator fa-lg" aria-hidden="true"></i></strong>
            </button>
            <button type="button" title="{{ __('lang_v1.full_screen') }}"
                class="btn m-4 btn-primary btn-flat m-6 hidden-xs pull-right" id="full_screen">
                <strong><i class="fa fa-expand fa-lg"></i></strong>
            </button>
            <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}"
                class="btn m-4 bg-yellow btn-flat btn-modal pull-right" data-container=".view_modal"
                data-href="{{ $view_suspended_sell_url }}">
                <strong><i class="fa fa-pause-circle fa-lg"></i></strong>
            </button>


            <button type="button" title="Sangria" class="btn m-4 bg-red btn-flat btn-modal pull-right"
                id="btn_sangria">
                <svg width="20" height="20" x="0" y="0" viewBox="0 0 48 48"
                    style="display: block;" class="fa fa-lg">
                    <g>
                        <path
                            d="M24.405 38.652c-.529.055-1.078.082-1.64.082-9.06 0-16.381-7.583-15.952-16.734.381-8.124 7.066-14.808 15.191-15.188 9.151-.427 16.732 6.894 16.732 15.952 0 .56-.028 1.108-.083 1.639a.988.988 0 0 0 .826 1.065 1.007 1.007 0 0 0 1.167-.902 18 18 0 0 0 .089-1.802c0-10.585-9.2-19.062-20.023-17.856-8.23.919-14.883 7.571-15.802 15.801-1.209 10.824 7.269 20.026 17.855 20.026.615 0 1.218-.03 1.802-.089s1.001-.59.902-1.168a.988.988 0 0 0-1.064-.826z"
                            fill="#fff" opacity="1" data-original="#fff" class=""></path>
                        <path
                            d="M34.765 26.325c-4.65 0-8.44 3.79-8.44 8.44s3.79 8.44 8.44 8.44 8.44-3.79 8.44-8.44-3.79-8.44-8.44-8.44zm5.01 8.57c0 .55-.44 1-1 1h-8.02c-.56 0-1-.45-1-1v-.25c0-.55.44-1 1-1h8.02c.56 0 1 .45 1 1zM22.765 21.764c-1.326 0-2.405-1.079-2.405-2.405s1.079-2.405 2.405-2.405 2.405 1.079 2.405 2.405a1 1 0 0 0 2 0c0-2-1.347-3.673-3.176-4.209v-1.982a1 1 0 0 0-2 0v1.864c-2.062.367-3.635 2.162-3.635 4.327a4.41 4.41 0 0 0 4.405 4.405c1.326 0 2.405 1.08 2.405 2.406s-1.079 2.405-2.405 2.405-2.405-1.079-2.405-2.405a1 1 0 1 0-2 0c0 2.165 1.573 3.961 3.635 4.327v1.866a1 1 0 1 0 2 0v-1.984c1.829-.536 3.176-2.21 3.176-4.209a4.41 4.41 0 0 0-4.405-4.406z"
                            fill="#fff" opacity="1" data-original="#fff" class=""></path>
                    </g>
                </svg>
            </button>

            <button type="button" title="Suprimento" class="btn m-4 bg-green-active btn-flat btn-modal pull-right"
                id="btn_suprimento">
                <div>
                    <svg width="20" height="20" x="0" y="0" viewBox="0 0 48 48"
                        style="display: block;">
                        <g>
                            <path
                                d="M24.405 38.652c-.529.055-1.078.082-1.64.082-9.06 0-16.381-7.583-15.952-16.734.381-8.124 7.066-14.808 15.191-15.188 9.151-.427 16.732 6.894 16.732 15.952 0 .56-.028 1.108-.083 1.639a.988.988 0 0 0 .826 1.065 1.007 1.007 0 0 0 1.167-.902 18 18 0 0 0 .089-1.802c0-10.585-9.2-19.062-20.023-17.856-8.23.919-14.883 7.571-15.802 15.801-1.209 10.824 7.269 20.026 17.855 20.026.615 0 1.218-.03 1.802-.089s1.001-.59.902-1.168a.988.988 0 0 0-1.064-.826z"
                                fill="#fff" opacity="1" data-original="#fff" class=""></path>
                            <path
                                d="M34.765 26.325c-4.65 0-8.44 3.79-8.44 8.44s3.79 8.44 8.44 8.44 8.44-3.79 8.44-8.44-3.79-8.44-8.44-8.44zm5.01 8.57c0 .55-.44 1-1 1h-2.89v2.88c0 .56-.45 1-1 1h-.24c-.55 0-1-.44-1-1v-2.88h-2.89c-.56 0-1-.45-1-1v-.25c0-.55.44-1 1-1h2.89v-2.89c0-.56.45-1 1-1h.24c.55 0 1 .44 1 1v2.89h2.89c.56 0 1 .45 1 1zM22.765 21.764c-1.326 0-2.405-1.079-2.405-2.405s1.079-2.405 2.405-2.405 2.405 1.079 2.405 2.405a1 1 0 0 0 2 0c0-2-1.347-3.673-3.176-4.209v-1.982a1 1 0 0 0-2 0v1.864c-2.062.367-3.635 2.162-3.635 4.327a4.41 4.41 0 0 0 4.405 4.405c1.326 0 2.405 1.08 2.405 2.406s-1.079 2.405-2.405 2.405-2.405-1.079-2.405-2.405a1 1 0 1 0-2 0c0 2.165 1.573 3.961 3.635 4.327v1.866a1 1 0 1 0 2 0v-1.984c1.829-.536 3.176-2.21 3.176-4.209a4.41 4.41 0 0 0-4.405-4.406z"
                                fill="#fff" opacity="1" data-original="#fff" class=""></path>
                        </g>
                    </svg>
                </div>
            </button>


            @if (empty($pos_settings['hide_product_suggestion']) && isMobile())
                <button type="button" title="{{ __('lang_v1.view_products') }}" data-placement="bottom"
                    class="btn m-4 btn-success btn-flat btn-modal pull-right" data-toggle="modal"
                    data-target="#mobile_product_suggestion_modal">
                    <strong><i class="fa fa-cubes fa-lg"></i></strong>
                </button>
            @endif
            @if (Module::has('Repair') && $transaction_sub_type != 'repair')
                @include('repair::layouts.partials.pos_header')
            @endif
            @if (in_array('pos_sale', $enabled_modules) && !empty($transaction_sub_type))
                @can('sell.create')
                    <a href="{{ action('SellPosController@create') }}" title="@lang('sale.pos_sale')"
                        class="btn m-4 btn-success btn-flat pull-right">
                        <strong><i class="fa fa-th-large"></i> &nbsp; @lang('sale.pos_sale')</strong>
                    </a>
                @endcan
            @endif
            {{-- </div> --}}
        </div>
    </div>
</div>
