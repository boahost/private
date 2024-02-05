<?php

namespace App\Http\Controllers;

use App\Helpers\PixHelper;
use App\Models\Business;
use App\Models\Currency;
use App\Models\Integration;
use App\Notifications\TestEmailNotification;
use App\Models\System;
use App\Models\TaxRate;
use App\Models\City;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Utils\BusinessUtil;
use Illuminate\Support\Facades\File;
use Modules\Superadmin\Entities\Package;
use App\Utils\ModuleUtil;
use App\Utils\RestaurantUtil;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use NFePHP\Common\Certificate;
use Modules\Superadmin\Entities\Subscription;


class BusinessController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | BusinessController
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new business/business as well as their
    | validation and creation.
    |
    */

    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;
    protected $restaurantUtil;
    protected $moduleUtil;
    protected $mailDrivers;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil, RestaurantUtil $restaurantUtil, ModuleUtil $moduleUtil)
    {

        $this->businessUtil = $businessUtil;
        $this->moduleUtil   = $moduleUtil;
        $this->theme_colors = [
            'blue'         => 'Blue',
            'black'        => 'Black',
            'purple'       => 'Purple',
            'green'        => 'Green',
            'red'          => 'Red',
            'yellow'       => 'Yellow',
            'blue-light'   => 'Blue Light',
            'black-light'  => 'Black Light',
            'purple-light' => 'Purple Light',
            'green-light'  => 'Green Light',
            'red-light'    => 'Red Light',
        ];

        $this->mailDrivers = [
            'smtp' => 'SMTP',
            // 'sendmail' => 'Sendmail',
            // 'mailgun' => 'Mailgun',
            // 'mandrill' => 'Mandrill',
            // 'ses' => 'SES',
            // 'sparkpost' => 'Sparkpost'
        ];

    }

    /**
     * Shows registration form
     *
     */
    public function getRegister()
    {
        if (!config('constants.allow_registration')) {
            return redirect('/');
        }

        $currencies = $this->businessUtil->allCurrencies();

        $timezone_list = $this->businessUtil->allTimeZones();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = __('business.months.' . $i);
        }

        $accounting_methods = $this->businessUtil->allAccountingMethods();
        $package_id         = request()->package;

        $system_settings = System::getProperties(['superadmin_enable_register_tc', 'superadmin_register_tc'], true);
        return view(
            'business.register',
            compact(
                'currencies',
                'timezone_list',
                'months',
                'accounting_methods',
                'package_id',
                'system_settings'
            )
        );
    }

    /**
     * Handles the registration of a new business and it's owner
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(Request $request)
    {
        if (!config('constants.allow_registration')) {
            return redirect('/');
        }

        try {

            $validator = $request->validate(
                [
                    'name'        => 'required|max:255',
                    'currency_id' => 'required|numeric',
                    // 'country' => 'required|max:255',
                    'state'       => 'required|max:255',
                    'city'        => 'required|max:255',
                    'zip_code'    => 'required|max:255',
                    // 'landmark' => 'required|max:255',
                    // 'time_zone' => 'required|max:255',
                    'surname'     => 'max:10',
                    'email'       => 'sometimes|nullable|email|unique:users|max:255',
                    'first_name'  => 'required|max:255',
                    'username'    => 'required|min:4|max:255|unique:users',
                    'password'    => 'required|min:4|max:255',
                    // 'fy_start_month' => 'required',
                    // 'accounting_method' => 'required',
                ],
                [
                    'name.required'              => __('validation.required', ['attribute' => __('business.business_name')]),
                    'name.currency_id'           => __('validation.required', ['attribute' => __('business.currency')]),
                    'country.required'           => __('validation.required', ['attribute' => __('business.country')]),
                    'state.required'             => __('validation.required', ['attribute' => __('business.state')]),
                    'city.required'              => __('validation.required', ['attribute' => __('business.city')]),
                    'zip_code.required'          => __('validation.required', ['attribute' => __('business.zip_code')]),
                    'landmark.required'          => __('validation.required', ['attribute' => __('business.landmark')]),
                    'time_zone.required'         => __('validation.required', ['attribute' => __('business.time_zone')]),
                    'email.email'                => __('validation.email', ['attribute' => __('business.email')]),
                    'email.email'                => __('validation.unique', ['attribute' => __('business.email')]),
                    'first_name.required'        => __('validation.required', [
                        'attribute' =>
                            __('business.first_name')
                    ]),
                    'username.required'          => __('validation.required', ['attribute' => __('business.username')]),
                    'username.min'               => __('validation.min', ['attribute' => __('business.username')]),
                    'password.required'          => __('validation.required', ['attribute' => __('business.username')]),
                    'password.min'               => __('validation.min', ['attribute' => __('business.username')]),
                    'fy_start_month.required'    => __('validation.required', ['attribute' => __('business.fy_start_month')]),
                    'accounting_method.required' => __('validation.required', ['attribute' => __('business.accounting_method')]),
                ]
            );

            DB::beginTransaction();

            $package = Package::where('name', 'teste')
                ->orWhere('name', 'Teste')
                ->first();

            // echo $package;

            //Create owner.
            $owner_details = $request->only(['surname', 'first_name', 'last_name', 'username', 'email', 'password', 'language']);

            $owner_details['language'] = empty($owner_details['language']) ? config('app.locale') : $owner_details['language'];

            $user = User::create_user($owner_details);

            $business_details                   = $request->only(['name', 'start_date', 'currency_id', 'time_zone']);
            $business_details['fy_start_month'] = 1;

            // print_r($business_details);
            // die;
            $business_location = $request->only(['name', 'country', 'state', 'city', 'zip_code', 'landmark', 'website', 'mobile', 'alternate_number']);

            $business_location['country'] = 'Brasil';
            //Create the business
            $business_details['owner_id'] = $user->id;
            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = Carbon::createFromFormat(config('constants.default_date_format'), $business_details['start_date'])->toDateString();
            }

            //upload logo
            $logo_name = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            if (!empty($logo_name)) {
                $business_details['logo'] = $logo_name;
            }

            if ($package == null) {
                //default enabled modules
                $business_details['enabled_modules'] = ['purchases', 'add_sale', 'pos_sale', 'stock_transfers', 'stock_adjustment', 'expenses', 'cte', 'mdfe', 'revenues'];
            } else {
                $business_details['enabled_modules'] = json_decode($package->enabled_modules);
            }

            // $business_details['enabled_modules'] = ['purchases','add_sale','pos_sale','stock_transfers','stock_adjustment', 'expenses', 'cte', 'mdfe', 'revenues'];

            $business_details['stop_selling_before']    = 1;
            $business_details['weighing_scale_setting'] = 1;
            $business_details['certificado']            = '';

            $business_details['business_logo']    = '';
            $business_details['alternate_number'] = '';
            $business_details['website']          = '';

            $business = $this->businessUtil->createNewBusiness($business_details);

            if ($package != null) {
                // seta plano para empresa

                $dates        = $this->_get_package_dates($business->id, $package);
                $subscription = [
                    'business_id'            => $business->id,
                    'package_id'             => $package->id,
                    'paid_via'               => '',
                    'payment_transaction_id' => '',
                    'start_date'             => $dates['start'],
                    'end_date'               => $dates['end'],
                    'trial_end_date'         => $dates['trial'],
                    'status'                 => 'approved',
                ];

                $subscription['package_price']   = $package->price;
                $subscription['package_details'] = [
                    'location_count' => $package->location_count,
                    'user_count'     => $package->user_count,
                    'product_count'  => $package->product_count,
                    'invoice_count'  => $package->invoice_count,
                    'name'           => $package->name
                ];
                Subscription::create($subscription);

            }
            //Update user with business id

            $b              = Business::find($business->id);
            $b->date_format = 'd/m/Y';
            $b->start_date  = date('Y-m-d');
            $b->save();

            $user->business_id = $business->id;
            $user->save();


            $this->businessUtil->newBusinessDefaultResources($business->id, $user->id);
            $new_location = $this->businessUtil->addLocation($business->id, $business_location);

            //create new permission with the new location
            Permission::create(['name' => 'location.' . $new_location->id]);

            DB::commit();

            //Module function to be called after after business is created
            if (config('app.env') != 'demo') {
                $this->moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            }

            //Process payment information if superadmin is installed & package information is present
            $is_installed_superadmin = $this->moduleUtil->isSuperadminInstalled();
            $package_id              = $request->get('package_id', null);
            if ($is_installed_superadmin && !empty($package_id) && (config('app.env') != 'demo')) {
                $package = \Modules\Superadmin\Entities\Package::find($package_id);
                if (!empty($package)) {
                    Auth::login($user);
                    return redirect()->route('register-pay', ['package_id' => $package_id]);
                }
            }

            $output = [
                'success' => 1,
                'msg'     => 'Empresa criada com sucesso!!'
            ];

            return redirect('login')->with('status', $output);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg'     => __('messages.something_went_wrong')
            ];

            echo $e->getMessage();
            echo $e->getLine();
            echo $e->getFile();
            die;
            // return back()->with('status', $output)->withInput();
        }
    }

    protected function _get_package_dates($business_id, $package)
    {
        $output = ['start' => '', 'end' => '', 'trial' => ''];

        //calculate start date
        $start_date      = Subscription::end_date($business_id);
        $output['start'] = $start_date->toDateString();

        //Calculate end date
        if ($package->interval == 'days') {
            $output['end'] = $start_date->addDays($package->interval_count)->toDateString();
        } elseif ($package->interval == 'months') {
            $output['end'] = $start_date->addMonths($package->interval_count)->toDateString();
        } elseif ($package->interval == 'years') {
            $output['end'] = $start_date->addYears($package->interval_count)->toDateString();
        }

        $output['trial'] = $start_date->addDays($package->trial_days);

        return $output;
    }

    /**
     * Handles the validation username
     *
     * @return \Illuminate\Http\Response
     */
    public function postCheckUsername(Request $request)
    {
        $username = $request->input('username');

        if (!empty($request->input('username_ext'))) {
            $username .= $request->input('username_ext');
        }

        $count = User::where('username', $username)->count();

        if ($count == 0) {
            echo "true";
            exit;
        } else {
            echo "false";
            exit;
        }
    }

    /**
     * Shows business settings form
     *
     */
    public function getBusinessSettings()
    {
        if (!auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        $timezones     = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezone_list = [];
        foreach ($timezones as $timezone) {
            $timezone_list[$timezone] = $timezone;
        }

        $business_id = request()->session()->get('user.business_id');
        $business    = Business::where('id', $business_id)->first();

        $integrations_raw = Integration::where('business_id', $business_id)->get()->toArray();

        $integrations = [];
        foreach ($integrations_raw as $item) {
            $integrations[$item['integration']] = $item;
        }

        $currencies  = $this->businessUtil->allCurrencies();
        $tax_details = TaxRate::forBusinessDropdown($business_id);
        $tax_rates   = $tax_details['tax_rates'];

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = $this->getMeses($i);
        }

        $accounting_methods        = [
            'fifo' => __('business.fifo'),
            'lifo' => __('business.lifo')
        ];
        $commission_agent_dropdown = [
            ''               => __('lang_v1.disable'),
            'logged_in_user' => __('lang_v1.logged_in_user'),
            'user'           => __('lang_v1.select_from_users_list'),
            'cmsn_agnt'      => __('lang_v1.select_from_commisssion_agents_list')
        ];

        $units_dropdown = Unit::forDropdown($business_id, true);

        $date_formats = Business::date_formats();

        $shortcuts = json_decode($business->keyboard_shortcuts, true);

        $pos_settings = empty($business->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business->pos_settings, true);

        $email_settings = empty($business->email_settings) ? $this->businessUtil->defaultEmailSettings() : $business->email_settings;

        $sms_settings = empty($business->sms_settings) ? $this->businessUtil->defaultSmsSettings() : $business->sms_settings;

        $modules = $this->moduleUtil->availableModules();

        $theme_colors = $this->theme_colors;

        $mail_drivers = $this->mailDrivers;

        $allow_superadmin_email_settings = System::getProperty('allow_email_settings_to_businesses');

        $custom_labels = !empty($business->custom_labels) ? json_decode($business->custom_labels, true) : [];

        $common_settings = !empty($business->common_settings) ? $business->common_settings : [];

        $weighing_scale_setting = !empty($business->weighing_scale_setting) ? $business->weighing_scale_setting : [];

        $listaCSTCSOSN       = Product::listaCSTCSOSN();
        $listaCST_PIS_COFINS = Product::listaCST_PIS_COFINS();
        $listaCST_IPI        = Product::listaCST_IPI();
        $unidadesDeMedida    = Product::unidadesMedida();

        $pessoa = "";

        $cnpjTemp = str_replace(".", "", $business->cnpj);
        $cnpjTemp = str_replace("/", "", $cnpjTemp);
        $cnpjTemp = str_replace("-", "", $cnpjTemp);
        $cnpjTemp = str_replace(" ", "", $cnpjTemp);

        if (strlen($cnpjTemp) == 11) {
            $pessoa = "f";
        } else if (strlen($cnpjTemp) == 14) {
            $pessoa = "j";
        }


        $package        = !empty($business->subscriptions[0]) ? optional($business->subscriptions[0])->package : '';
        $not_in_package = [];

        if ($package) {
            $enabled_modules = json_decode($package->enabled_modules);

            $avaible_in_package = [];

            foreach ($modules as $key => $m) {
                if (!in_array($key, $enabled_modules)) {
                    array_push($not_in_package, $m['name']);
                }
            }
        }

        return view(
            'business.settings',
            compact(
                'business',
                'integrations',
                'currencies',
                'tax_rates',
                'timezone_list',
                'months',
                'accounting_methods',
                'commission_agent_dropdown',
                'units_dropdown',
                'date_formats',
                'shortcuts',
                'pos_settings',
                'modules',
                'theme_colors',
                'email_settings',
                'sms_settings',
                'mail_drivers',
                'allow_superadmin_email_settings',
                'custom_labels',
                'common_settings',
                'weighing_scale_setting',
                'not_in_package'
            )
        )
            ->with('infoCertificado', $this->getInfoCertificado($business))
            ->with('listaCSTCSOSN', $listaCSTCSOSN)
            ->with('listaCST_PIS_COFINS', $listaCST_PIS_COFINS)
            ->with('listaCST_IPI', $listaCST_IPI)
            ->with('pessoa', $pessoa)
            ->with('cities', $this->prepareCities());
    }

    public function downloadCertificado()
    {
        // if (!auth()->user()->can('business_settings.access')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // try{
        //     $business_id = request()->session()->get('user.business_id');
        //     $business = Business::where('id', $business_id)->first();

        //     $headers = [
        //         'Content-Type' => 'bin',
        //     ];
        //     if(!is_dir(public_path('certificados'))){
        //         mkdir(public_path('certificados'), 0777, true);
        //     }
        //     $cnpj = preg_replace('/[^0-9]/', '', $business->cnpj);

        //     file_put_contents("certificados/$cnpj.bin", $business->certificado);

        //     return response()->download("certificados/$cnpj.bin");
        // }catch(\Exception $e){

        // }

        $business_id = request()->session()->get('user.business_id');

        $file = public_path("/uploads/business_certificados/") . Business::find($business_id)->certificado_urn;
        // echo $file;
        // die;
        if (!file_exists($file)) {
            $HOSTNAME_FILES = getenv('HOSTNAME_FILES');
            $file           = $HOSTNAME_FILES . "/uploads/business_certificados/" . Business::find($business_id)->certificado_urn;
            return redirect($file);
        }

        return response()->download($file);
    }

    private function getMeses($i)
    {
        $arr = [
            '',
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        ];
        return $arr[$i];
    }

    private function getInfoCertificado($business)
    {
        if ($business->certificado == null)
            return null;

        try {
            $infoCertificado = Certificate::readPfx($business->certificado, base64_decode($business->senha_certificado));

            $publicKey = $infoCertificado->publicKey;

            $inicio    = $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao = $publicKey->validTo->format('Y-m-d H:i:s');

            return [
                'serial'    => $publicKey->serialNumber,
                'inicio'    => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id'        => $publicKey->commonName
            ];
        } catch (\Exception $e) {

            return -1;
        }

    }

    private function prepareCities()
    {
        $cities = City::all();
        $temp   = [];
        foreach ($cities as $c) {
            // array_push($temp, $c->id => $c->nome);
            $temp[$c->id] = $c->nome . " (" . $c->uf . ")";
        }
        return $temp;
    }

    /**
     * Updates business settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postBusinessSettings(Request $request)
    {
        $this->_validate($request);

        if (!auth()->user()->can('business_settings.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->businessUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }

            $cidade_id = $request->cidade_id;
            $cidade    = City::find($cidade_id);

            $request->merge(['city' => $cidade->nome]);


            $business_details = $request->only([
                'name',
                'start_date',
                'currency_id',
                'tax_label_1',
                'tax_number_1',
                'tax_label_2',
                'tax_number_2',
                'default_profit_percent',
                'default_sales_tax',
                'default_sales_discount',
                'sell_price_tax',
                'sku_prefix',
                'time_zone',
                'fy_start_month',
                'accounting_method',
                'transaction_edit_days',
                'sales_cmsn_agnt',
                'item_addition_method',
                'currency_symbol_placement',
                'on_product_expiry',
                'stop_selling_before',
                'default_unit',
                'expiry_type',
                'date_format',
                'time_format',
                'ref_no_prefixes',
                'theme_color',
                'email_settings',
                'sms_settings',
                'rp_name',
                'amount_for_unit_rp',
                'min_order_total_for_rp',
                'max_rp_per_order',
                'redeem_amount_per_unit_rp',
                'min_order_total_for_redeem',
                'min_redeem_point',
                'max_redeem_point',
                'rp_expiry_period',
                'rp_expiry_type',
                'custom_labels',
                'weighing_scale_setting',
                'razao_social',
                'cnpj',
                'ie',
                'cidade_id',
                'rua',
                'numero',
                'bairro',
                'cep',
                'telefone',
                'ultimo_numero_nfe',
                'ultimo_numero_nfce',
                'ultimo_numero_cte',
                'ultimo_numero_mdfe',
                'inscricao_municipal',
                'numero_serie_nfe',
                'numero_serie_nfce',
                'ambiente',
                'regime',
                'cst_csosn_padrao',
                'cst_pis_padrao',
                'cst_cofins_padrao',
                'cst_ipi_padrao',
                'csc',
                'csc_id',
                'perc_icms_padrao',
                'perc_pis_padrao',
                'perc_cofins_padrao',
                'perc_ipi_padrao',
                'ncm_padrao',
                'cfop_saida_estadual_padrao',
                'cfop_saida_inter_estadual_padrao',
                'aut_xml'
            ]);

            $certificado = $request->file('certificado');


            if ($certificado) {
                $ctx                             = file_get_contents($certificado);
                $business_details['certificado'] = $ctx;

            }

            if ($request->senha_certificado != '') {
                $business_details['senha_certificado'] = base64_encode($request->senha_certificado);
            }
            if (!empty($request->input('enable_rp')) && $request->input('enable_rp') == 1) {
                $business_details['enable_rp'] = 1;
            } else {
                $business_details['enable_rp'] = 0;
            }

            $business_details['amount_for_unit_rp']         = !empty($business_details['amount_for_unit_rp']) ? $this->businessUtil->num_uf($business_details['amount_for_unit_rp']) : 1;
            $business_details['min_order_total_for_rp']     = !empty($business_details['min_order_total_for_rp']) ? $this->businessUtil->num_uf($business_details['min_order_total_for_rp']) : 1;
            $business_details['redeem_amount_per_unit_rp']  = !empty($business_details['redeem_amount_per_unit_rp']) ? $this->businessUtil->num_uf($business_details['redeem_amount_per_unit_rp']) : 1;
            $business_details['min_order_total_for_redeem'] = !empty($business_details['min_order_total_for_redeem']) ? $this->businessUtil->num_uf($business_details['min_order_total_for_redeem']) : 1;

            $business_details['default_profit_percent'] = !empty($business_details['default_profit_percent']) ? $this->businessUtil->num_uf($business_details['default_profit_percent']) : 0;

            $business_details['default_sales_discount'] = !empty($business_details['default_sales_discount']) ? $this->businessUtil->num_uf($business_details['default_sales_discount']) : 0;

            if (!empty($business_details['start_date'])) {
                $business_details['start_date'] = $this->businessUtil->uf_date($business_details['start_date']);
            }

            if (!empty($request->input('enable_tooltip')) && $request->input('enable_tooltip') == 1) {
                $business_details['enable_tooltip'] = 1;
            } else {
                $business_details['enable_tooltip'] = 0;
            }

            $business_details['enable_product_expiry'] = !empty($request->input('enable_product_expiry')) && $request->input('enable_product_expiry') == 1 ? 1 : 0;
            if ($business_details['on_product_expiry'] == 'keep_selling') {
                $business_details['stop_selling_before'] = null;
            }

            $business_details['stock_expiry_alert_days'] = !empty($request->input('stock_expiry_alert_days')) ? $request->input('stock_expiry_alert_days') : 30;

            //Check for Purchase currency
            if (!empty($request->input('purchase_in_diff_currency')) && $request->input('purchase_in_diff_currency') == 1) {
                $business_details['purchase_in_diff_currency'] = 1;
                $business_details['purchase_currency_id']      = $request->input('purchase_currency_id');
                $business_details['p_exchange_rate']           = $request->input('p_exchange_rate');
            } else {
                $business_details['purchase_in_diff_currency'] = 0;
                $business_details['purchase_currency_id']      = null;
                $business_details['p_exchange_rate']           = 1;
            }


            //upload logo
            $logo_name = $this->businessUtil->uploadFile($request, 'business_logo', 'business_logos', 'image');
            if (!empty($logo_name)) {
                $business_details['logo'] = $logo_name;
            }

            $checkboxes = [
                'enable_editing_product_from_purchase',
                'enable_inline_tax',
                'enable_brand',
                'enable_category',
                'enable_sub_category',
                'enable_price_tax',
                'enable_purchase_status',
                'enable_lot_number',
                'enable_racks',
                'enable_row',
                'enable_position',
                'enable_sub_units'
            ];
            foreach ($checkboxes as $integration) {
                $business_details[$integration] = !empty($request->input($integration)) && $request->input($integration) == 1 ? 1 : 0;
            }

            $business_id = request()->session()->get('user.business_id');
            $business    = Business::where('id', $business_id)->first();

            //Update business settings
            if (!empty($business_details['logo'])) {
                $business->logo = $business_details['logo'];
            } else {
                unset($business_details['logo']);
            }

            //System settings
            $shortcuts                              = $request->input('shortcuts');
            $business_details['keyboard_shortcuts'] = json_encode($shortcuts);

            //pos_settings
            $pos_settings         = $request->input('pos_settings');
            $default_pos_settings = $this->businessUtil->defaultPosSettings();
            foreach ($default_pos_settings as $key => $integration) {
                if (!isset($pos_settings[$key])) {
                    $pos_settings[$key] = $integration;
                }
            }

            // \Log::debug($pos_settings);

            $business_details['pos_settings'] = json_encode($pos_settings);

            $business_details['custom_labels'] = json_encode($business_details['custom_labels']);

            $business_details['common_settings'] = !empty($request->input('common_settings')) ? $request->input('common_settings') : [];

            //Enabled modules
            $enabled_modules                     = $request->input('enabled_modules');
            $business_details['enabled_modules'] = !empty($enabled_modules) ? $enabled_modules : null;

            $business->fill($business_details);
            $business->save();


            $integrations = $request->input('integrations');

            foreach ($integrations as $key => $integration) {
                $data = [
                    'integration'       => $key,
                    'business_id'       => $business_id,
                    'payee_code'        => $integration['payee_code'],
                    'key_client_id'     => $integration['key_client_id'],
                    'key_client_secret' => $integration['key_client_secret'],
                ];

                if ($file = $request->file("integrations.{$key}.certificate")) {
                    $filename = time() . '_' . $key . '_certificate.' . $file->getClientOriginalExtension();

                    if (!$certificate_path = $file->storeAs($business_id, $filename, 'certificates')) {
                        return redirect('business/settings')->with('status', [
                            'success' => 0,
                            'msg'     => __('messages.something_went_wrong')
                        ]);
                    }

                    $data['certificate'] = $certificate_path;
                }

                $new = Integration::firstOrCreate([
                    'integration' => $key,
                    'business_id' => $business_id
                ], $data);

                $integration = $new->fill($data);
                $new->save();

                if (
                    !empty($integration['certificate'])
                    and !empty($integration['payee_code'])
                    and !empty($integration['key_client_id'])
                    and !empty($integration['key_client_secret'])
                ) {
                    if ($key == 'efi') {
                        try {
                            $pix = new PixHelper($business_id);

                            $split_plan = $pix->splitConfig($business->pix_split, $integration['pix_split_plan']);

                            $data['pix_split_plan'] = $split_plan['id'] ?? null;

                            $new->fill($data);
                            $new->save();

                        } catch (\Exception $e) {
                            throw new \Exception($e->getMessage() ?: 'Erro ao configurar conta do EFI. Por favor, corrija suas credenciais e tente novamente.');
                        }
                    }
                }
            }

            //update session data
            $request->session()->put('business', $business);

            //Update Currency details
            $currency = Currency::find($business->currency_id);
            $request->session()->put('currency', [
                'id'                 => $currency->id,
                'code'               => $currency->code,
                'symbol'             => $currency->symbol,
                'thousand_separator' => $currency->thousand_separator,
                'decimal_separator'  => $currency->decimal_separator,
            ]);

            //update current financial year to session
            $financial_year = $this->businessUtil->getCurrentFinancialYear($business->id);
            $request->session()->put('financial_year', $financial_year);

            \Artisan::call('cache:clear');
            \Artisan::call('view:clear');

            $output = [
                'success' => 1,
                'msg'     => __('business.settings_updated_success')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File: " . $e->getFile() . ":" . $e->getLine() . " Message: " . $e->getMessage());

            $output = [
                'success' => 0,
                'msg'     => $e->getMessage() ?: __('messages.something_went_wrong')
            ];
        }

        return redirect('business/settings')->with('status', $output);
    }


    private function _validate(Request $request)
    {
        $rules = [
            'razao_social' => 'required|min:10',
        ];

        $messages = [
            'razao_social.required' => 'Campo obrigatório.',
            'razao_social.min'      => 'digite no minimo 10 caractes.'
        ];

        $this->validate($request, $rules, $messages);
    }

    /**
     * Handles the validation email
     *
     * @return \Illuminate\Http\Response
     */
    public function postCheckEmail(Request $request)
    {
        $email = $request->input('email');

        $query = User::where('email', $email);

        if (!empty($request->input('user_id'))) {
            $user_id = $request->input('user_id');
            $query->where('id', '!=', $user_id);
        }

        $exists = $query->exists();
        if (!$exists) {
            echo "true";
            exit;
        } else {
            echo "false";
            exit;
        }
    }

    public function getEcomSettings()
    {
        try {
            $api_token    = request()->header('API-TOKEN');
            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $settings = Business::where('id', $api_settings->business_id)
                ->value('ecom_settings');

            $settings_array = !empty($settings) ? json_decode($settings, true) : [];

            if (!empty($settings_array['slides'])) {
                foreach ($settings_array['slides'] as $key => $value) {
                    $settings_array['slides'][$key]['image_url'] = !empty($value['image']) ? url('uploads/img/' . $value['image']) : '';
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($settings_array);
    }

    /**
     * Handles the testing of email configuration
     *
     * @return \Illuminate\Http\Response
     */
    public function testEmailConfiguration(Request $request)
    {
        try {
            $email_settings = $request->input();


            $data['email_settings'] = $email_settings;
            \Notification::route('mail', $email_settings['mail_from_address'])
                ->notify(new TestEmailNotification($data));

            $output = [
                'success' => 1,
                'msg'     => __('lang_v1.email_tested_successfully')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg'     => $e->getMessage()
            ];
        }

        return $output;
    }

    /**
     * Handles the testing of sms configuration
     *
     * @return \Illuminate\Http\Response
     */
    public function testSmsConfiguration(Request $request)
    {
        try {
            $sms_settings = $request->input();

            $data = [
                'sms_settings'  => $sms_settings,
                'mobile_number' => $sms_settings['test_number'],
                'sms_body'      => 'This is a test SMS',
            ];
            if (!empty($sms_settings['test_number'])) {
                $response = $this->businessUtil->sendSms($data);
            } else {
                $response = __('lang_v1.test_number_is_required');
            }

            $output = [
                'success' => 1,
                'msg'     => $response
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => 0,
                'msg'     => $e->getMessage()
            ];
        }

        return $output;
    }
}
