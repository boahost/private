<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Contact;
use App\Models\City;
use App\Models\CustomerGroup;
use App\Notifications\CustomerNotification;
use App\Models\PurchaseLine;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Pais;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\TransactionUtil;
use App\Utils\Util;
use DB;
use Excel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ContactController extends Controller
{
    protected $commonUtil;
    protected $contactUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(
        Util $commonUtil,
        ModuleUtil $moduleUtil,
        TransactionUtil $transactionUtil,
        NotificationUtil $notificationUtil,
        ContactUtil $contactUtil
    ) {

        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
        $this->notificationUtil = $notificationUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $type = request()->get('type');

        $types = ['supplier', 'customer'];

        if (empty($type) || !in_array($type, $types)) {
            return redirect()->back();
        }

        if (request()->ajax()) {
            if ($type == 'supplier') {
                return $this->indexSupplier();
            } elseif ($type == 'customer') {
                return $this->indexCustomer();
            } else {
                die("Not Found");
            }
        }

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($type, ['customer'])) ? true : false;

        return view('contact.index')
        ->with(compact('type', 'reward_enabled'));
    }

    /**
     * Returns the database object for supplier
     *
     * @return \Illuminate\Http\Response
     */
    private function indexSupplier()
    {
        if (!auth()->user()->can('supplier.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $contact = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
        ->where('contacts.business_id', $business_id)
        ->onlySuppliers()
        ->select(['contacts.contact_id', 'supplier_business_name', 'name', 'contacts.created_at', 'mobile',
            'contacts.type', 'contacts.id',
            DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
            DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_paid"),
            DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
            DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as purchase_return_paid"),
            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
            'email', 'tax_number', 'contacts.pay_term_number', 'contacts.pay_term_type', 'contacts.custom_field1', 'contacts.custom_field2', 'contacts.custom_field3', 'contacts.custom_field4',
            'contacts.contact_status'
        ])
        ->groupBy('contacts.id');

        return Datatables::of($contact)
        ->addColumn(
            'due',
            '<span class="display_currency contact_due" data-orig-value="{{$total_purchase - $purchase_paid}}" data-currency_symbol=true data-highlight=false>{{$total_purchase - $purchase_paid }}</span>'
        )
        ->addColumn(
            'return_due',
            '<span class="display_currency return_due" data-orig-value="{{$total_purchase_return - $purchase_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_purchase_return - $purchase_return_paid }}</span>'
        )
        ->addColumn(
            'action',
            function ($row) {
                $html = '<div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                data-toggle="dropdown" aria-expanded="false">' .
                __("messages.actions") .
                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                $due_amount = $row->total_purchase + $row->opening_balance - $row->purchase_paid - $row->opening_balance_paid;

                if ($due_amount > 0) {
                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=purchase" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("contact.pay_due_amount") . '</a></li>';
                }

                $return_due = $row->total_purchase_return - $row->purchase_return_paid;
                if ($return_due > 0) {
                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=purchase_return" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.receive_purchase_return_due") . '</a></li>';
                }
                if (auth()->user()->can('supplier.view')) {
                    $html .= '<li><a href="' . action('ContactController@show', [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                }
                if (auth()->user()->can('supplier.update')) {
                    $html .= '<li><a href="' . action('ContactController@edit', [$row->id]) . '" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i>' .  __("messages.edit") . '</a></li>';
                }
                if (auth()->user()->can('supplier.delete')) {
                    $html .= '<li><a href="' . action('ContactController@destroy', [$row->id]) . '" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</a></li>';
                }

                if (auth()->user()->can('customer.update')) {
                    $html .= '<li><a href="' . action('ContactController@updateStatus', [$row->id]) . '"class="update_contact_status"><i class="fas fa-power-off"></i>';

                    if ($row->contact_status == "active") {
                        $html .= __("messages.deactivate");
                    } else {
                        $html .= __("messages.activate");
                    }

                    $html .= "</a></li>";
                }

                $html .= '<li class="divider"></li>';
                if (auth()->user()->can('supplier.view')) {
                    $html .= '
                    <li>
                    <a href="' . action('ContactController@show', [$row->id]). '?view=ledger">
                    <i class="fas fa-scroll" aria-hidden="true"></i>
                    ' . __("lang_v1.ledger") . '
                    </a>
                    </li>';

                    if (in_array($row->type, ["both", "supplier"])) {
                        $html .= '<li>
                        <a href="' . action('ContactController@show', [$row->id]) . '?view=purchase">
                        <i class="fas fa-arrow-circle-down" aria-hidden="true"></i>
                        ' . __("purchase.purchases") . '
                        </a>
                        </li>
                        <li>
                        <a href="' . action('ContactController@show', [$row->id]) . '?view=stock_report">
                        <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                        ' . __("report.stock_report") . '
                        </a>
                        </li>';
                    }

                    if (in_array($row->type, ["both", "customer"])) {
                        $html .=  '<li>
                        <a href="' . action('ContactController@show', [$row->id]). '?view=sales">
                        <i class="fas fa-arrow-circle-up" aria-hidden="true"></i>
                        ' . __("sale.sells") . '
                        </a>
                        </li>';
                    }

                    $html .= '<li>
                    <a href="' . action('ContactController@show', [$row->id]) . '?view=documents_and_notes">
                    <i class="fas fa-paperclip" aria-hidden="true"></i>
                    ' . __("lang_v1.documents_and_notes") . '
                    </a>
                    </li>';
                }
                $html .= '</ul></div>';

                return $html;
            }
        )
->editColumn('opening_balance', function ($row) {
    $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->opening_balance . '">' . $row->opening_balance . '</span>';

    return $html;
})
->editColumn('pay_term', '
    @if(!empty($pay_term_type) && !empty($pay_term_number))
    {{$pay_term_number}}
    @lang("lang_v1.".$pay_term_type)
    @endif
    ')
->editColumn('name', function ($row) {
    if ($row->contact_status == 'inactive') {
        return $row->name . ' <small class="label pull-right bg-red no-print">' . __("lang_v1.inactive") . '</small>';
    } else {
        return $row->name;
    }
})
->editColumn('created_at', '{{@format_date($created_at)}}')
->removeColumn('opening_balance_paid')
->removeColumn('type')
->removeColumn('id')
->removeColumn('total_purchase')
->removeColumn('purchase_paid')
->removeColumn('total_purchase_return')
->removeColumn('purchase_return_paid')
->rawColumns(['action', 'opening_balance', 'pay_term', 'due', 'return_due', 'name'])
->make(true);
}

    /**
     * Returns the database object for customer
     *
     * @return \Illuminate\Http\Response
     */
    private function indexCustomer()
    {
        if (!auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $query = Contact::leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id')
        ->leftjoin('customer_groups AS cg', 'contacts.customer_group_id', '=', 'cg.id')
        ->where('contacts.business_id', $business_id)
        ->onlyCustomers()
        ->select(['contacts.contact_id', 'contacts.name', 'contacts.created_at', 'total_rp', 'cg.name as customer_group', 'city', 'state', 'country', 'landmark', 'mobile', 'contacts.id', 'is_default',
            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
            DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as invoice_received"),
            DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
            DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as sell_return_paid"),
            DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
            DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid"),
            'email', 'tax_number', 'contacts.pay_term_number', 'contacts.pay_term_type', 'contacts.credit_limit', 'contacts.custom_field1', 'contacts.custom_field2', 'contacts.custom_field3', 'contacts.custom_field4', 'contacts.type',
            'contacts.contact_status'
        ])
        ->groupBy('contacts.id');

        $contacts = Datatables::of($query)
        ->addColumn('address', '{{implode(", ", array_filter([$landmark, $city, $state, $country]))}}')
        ->addColumn(
            'due',
            '<span class="display_currency contact_due" data-orig-value="{{$total_invoice - $invoice_received}}" data-currency_symbol=true data-highlight=true>{{($total_invoice - $invoice_received)}}</span>'
        )
        ->addColumn(
            'return_due',
            '<span class="display_currency return_due" data-orig-value="{{$total_sell_return - $sell_return_paid}}" data-currency_symbol=true data-highlight=false>{{$total_sell_return - $sell_return_paid }}</span>'
        )
        ->addColumn(
            'action',
            function ($row) {
                $html = '<div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle btn-xs"
                data-toggle="dropdown" aria-expanded="false">' .
                __("messages.actions") .
                '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                $due_amount = $row->total_invoice + $row->opening_balance - $row->invoice_received - $row->opening_balance_paid;

                if ($due_amount > 0) {
                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=sell" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("contact.pay_due_amount") . '</a></li>';
                }

                $return_due = $row->total_sell_return - $row->sell_return_paid;
                if ($return_due > 0) {
                    $html .= '<li><a href="' . action('TransactionPaymentController@getPayContactDue', [$row->id]) . '?type=sell_return" class="pay_purchase_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __("lang_v1.pay_sell_return_due") . '</a></li>';
                }
                if (auth()->user()->can('customer.view')) {
                    $html .= '<li><a href="' . action('ContactController@show', [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>' . __("messages.view") . '</a></li>';
                }
                if (auth()->user()->can('customer.update')) {
                    $html .= '<li><a href="' . action('ContactController@edit', [$row->id]) . '" class="edit_contact_button"><i class="glyphicon glyphicon-edit"></i>' .  __("messages.edit") . '</a></li>';
                }
                if (!$row->is_default && auth()->user()->can('customer.delete')) {
                    $html .= '<li><a href="' . action('ContactController@destroy', [$row->id]) . '" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i>' . __("messages.delete") . '</a></li>';
                }

                if (auth()->user()->can('customer.update')) {
                    $html .= '<li><a href="' . action('ContactController@updateStatus', [$row->id]) . '"class="update_contact_status"><i class="fas fa-power-off"></i>';

                    if ($row->contact_status == "active") {
                        $html .= 'Desativar';
                    } else {
                        $html .= 'Ativar';
                    }

                    $html .= "</a></li>";
                }

                $html .= '<li class="divider"></li>';
                if (auth()->user()->can('customer.view')) {
                    $html .= '
                    <li>
                    <a href="' . action('ContactController@show', [$row->id]). '?view=ledger">
                    <i class="fas fa-scroll" aria-hidden="true"></i>
                    ' . 'Registros' . '
                    </a>
                    </li>';

                    if (in_array($row->type, ["both", "supplier"])) {
                        $html .= '<li>
                        <a href="' . action('ContactController@show', [$row->id]) . '?view=purchase">
                        <i class="fas fa-arrow-circle-down" aria-hidden="true"></i>
                        ' . __("purchase.purchases") . '
                        </a>
                        </li>
                        <li>
                        <a href="' . action('ContactController@show', [$row->id]) . '?view=stock_report">
                        <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                        ' . __("report.stock_report") . '
                        </a>
                        </li>';
                    }

                    if (in_array($row->type, ["both", "customer"])) {
                        $html .=  '<li>
                        <a href="' . action('ContactController@show', [$row->id]). '?view=sales">
                        <i class="fas fa-arrow-circle-up" aria-hidden="true"></i>
                        ' . __("sale.sells") . '
                        </a>
                        </li>';
                    }

                    $html .= '<li>
                    <a href="' . action('ContactController@show', [$row->id]) . '?view=documents_and_notes">
                    <i class="fas fa-paperclip" aria-hidden="true"></i>
                    ' . 'Documentos' . '
                    </a>
                    </li>';
                }
                $html .= '</ul></div>';

                return $html;
            }
        )
->editColumn('opening_balance', function ($row) {
    $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->opening_balance . '">' . $row->opening_balance . '</span>';

    return $html;
})
->editColumn('credit_limit', function ($row) {
    $html = __('lang_v1.no_limit');
    if (!is_null($row->credit_limit)) {
        $html = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->credit_limit . '">' . $row->credit_limit . '</span>';
    }

    return $html;
})
->editColumn('pay_term', '
    @if(!empty($pay_term_type) && !empty($pay_term_number))
    {{$pay_term_number}}
    @lang("lang_v1.".$pay_term_type)
    @endif
    ')
->editColumn('name', function ($row) {
    if ($row->contact_status == 'inactive') {
        return $row->name . ' <small class="label pull-right bg-red no-print">' . __("lang_v1.inactive") . '</small>';
    } else {
        return $row->name;
    }
})
->editColumn('total_rp', '{{$total_rp ?? 0}}')
->editColumn('created_at', '{{@format_date($created_at)}}')
->removeColumn('total_invoice')
->removeColumn('opening_balance_paid')
->removeColumn('invoice_received')
->removeColumn('state')
->removeColumn('country')
->removeColumn('city')
->removeColumn('type')
->removeColumn('id')
->removeColumn('is_default')
->removeColumn('total_sell_return')
->removeColumn('sell_return_paid')
->filterColumn('address', function ($query, $keyword) {
    $query->whereRaw("CONCAT(COALESCE(landmark, ''), ', ', COALESCE(city, ''), ', ', COALESCE(state, ''), ', ', COALESCE(country, '') ) like ?", ["%{$keyword}%"]);
});
$reward_enabled = (request()->session()->get('business.enable_rp') == 1) ? true : false;
if (!$reward_enabled) {
    $contacts->removeColumn('total_rp');
}
return $contacts->rawColumns(['action', 'opening_balance', 'credit_limit', 'pay_term', 'due', 'return_due', 'name'])
->make(true);
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(request $request)
    {
        try{
            if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
                abort(403, 'Unauthorized action.');
            }

            $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
                $types['both'] = __('lang_v1.both_supplier_customer');
            }

            $customer_groups = CustomerGroup::forDropdown($business_id);

            return view('contact.create')
            ->with('cities', $this->prepareCities())
            ->with('estados', $this->prepareEstados())
            ->with('paises', $this->preparePaises())
            ->with('tipo', $request->type)
            ->with(compact('types', 'customer_groups'));
        }catch(\Exception $e){
            echo $e->getMessage();
            // return redirect()->back();
        }
    }

    private function prepareCities(){
        $cities = City::all();
        $temp = [];
        foreach($cities as $c){
            // array_push($temp, $c->id => $c->nome);
            $temp[$c->id] = $c->nome . " ($c->uf)";
        }
        return $temp;
    }

    private function preparePaises(){
        $paises = Pais::all();
        $temp = [];
        foreach($paises as $p){
            // array_push($temp, $c->id => $c->nome);
            $temp[$p->codigo] = "$p->codigo - $p->nome";
        }
        return $temp;
    }

    private function prepareEstados(){

        return [
            'AC' => 'AC',
            'AL' => 'AL',
            'AM' => 'AM',
            'AP' => 'AP',
            'BA' => 'BA',
            'CE' => 'CE',
            'DF' => 'DF',
            'ES' => 'ES',
            'GO' => 'GO',
            'MA' => 'MA',
            'MG' => 'MG',
            'MS' => 'MS',
            'MT' => 'MT',
            'PA' => 'PA',
            'PB' => 'PB',
            'PE' => 'PE',
            'PI' => 'PI',
            'PR' => 'PR',
            'RJ' => 'RJ',
            'RN' => 'RN',
            'RO' => 'RO',
            'RR' => 'RR',
            'RS' => 'RS',
            'SC' => 'SC',
            'SP' => 'SP',
            'SE' => 'SE',
            'TO' => 'TO'
        ];

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            $location = BusinessLocation::where('business_id', $business_id)->first();

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $request->merge([ 'supplier_business_name' => $cidade->supplier_business_name ?? '']);
            $request->merge([ 'id_estrangeiro' => $request->id_estrangeiro ?? '']);


            $input = $request->only(['type', 'supplier_business_name', 'cpf_cnpj', 'ie_rg', 'contribuinte',
                'consumidor_final', 'city_id', 'rua', 'numero', 'bairro', 'cep',
                'name', 'tax_number', 'pay_term_number', 'pay_term_type', 'mobile', 'landline',
                'alternate_number', 'city', 'state', 'country', 'landmark', 'customer_group_id',
                'contact_id', 'custom_field1', 'custom_field2', 'custom_field3', 'custom_field4', 'email',
                'shipping_address', 'position', 'city_id', 'cod_pais', 'id_estrangeiro', 'rua_entrega',
                'numero_entrega', 'bairro_entrega', 'cep_entrega', 'city_id_entrega']);
            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $input['mobile'] = $request->mobile ?? '';


            $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;


            //Check Contact id
            $count = 0;
            if (!empty($input['contact_id'])) {
                $count = Contact::where('business_id', $input['business_id'])
                ->where('contact_id', $input['contact_id'])
                ->count();
            }

            if ($count == 0) {
                //Update reference count
                $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts');

                if (empty($input['contact_id'])) {
                    //Generate reference number
                    $input['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
                }


                $contact = Contact::create($input);



                if($request->input('select_comanda') == "Sim"){
                    if($contact){
                        $dados = [

                            "business_id" => $business_id,
                            "location_id" => $location->id  ,
                            "type"  => "sell",
                            "status" => "final",
                            "is_quotation" => 0,
                            "payment_status" => "due",
                            "contact_id" => $contact->id,
                            "invoice_no" => str_replace('.', '', microtime(true)),
                            "total_before_tax" => 0,
                            "discount_type" => "percentage",
                            "discount_amount" => 0,
                            "rp_redeemed" => 0,
                            "rp_redeemed_amount" => 0,
                            "shipping_charges" => 0,
                            "additional_notes" => $request->input('numero_comanda'),
                            "round_off_amount" => 0,
                            "final_total" => 0,
                            "is_direct_sale" => 0,
                            "is_suspend" => 1,
                            "exchange_rate" => 1,
                            "created_by" => 35,
                            "mfg_production_cost_type" => "percentage",
                            "mfg_is_final" => 0,
                            "repair_updates_notif" => 0,
                            "packing_charge" => 0,
                            "is_created_from_api" => 0,
                            "rp_earned" => 0,
                            "is_recurring" => 0,
                            "recur_interval_type" => "days",
                            "recur_repetitions" => 0,
                            "selling_price_group_id" => 0,
                            "transaction_date" =>  date('Y-m-d H:i:s'),
                            "valor_frete" => 0,
                            "tipo" => 0,
                            "qtd_volumes" => 0,
                            "peso_liquido" =>0,
                            "peso_bruto" => 0,
                            "numero_nfe" => 0,
                            "numero_nfce" => 0,
                            "numer_nfe_entrada" => 0,
                            "sequencia_cce" => 0,
                            "cpf_nota" => "",
                            "troco" => 0,
                            "valor_recebido" => 0,
                            "estado" => "NOVO",
                            "pedido_ecommerce_id" => 0
                        ];

                        Transaction::create($dados);
                    }



                }





                //Add opening balance
                if (!empty($request->input('opening_balance'))) {
                    $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                }

                $output = [
                    'success' => true,
                    'data' => $contact,
                    'msg' => __("contact.added_success")
                ];
            } else {
                throw new \Exception("Error Processing Request", 1);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'Ocorreu um erro tente novamente mais tarde!'
            ];
        }

        return $output;
    }




    public function show($id)
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getContactInfo($business_id, $id);

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;

        $contact_dropdown = Contact::contactDropdown($business_id, false, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        //get contact view type : ledger, notes etc.
        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');
        $contact2 = Contact::find($id);


        return view('contact.show')
        ->with('contact2', $contact2)
        ->with(compact('contact', 'reward_enabled', 'contact_dropdown', 'business_locations', 'view_type', 'contact_view_tabs', 'id'));
    }


    public function edit($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);

            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $types = [];
            if (auth()->user()->can('supplier.create')) {
                $types['supplier'] = __('report.supplier');
            }
            if (auth()->user()->can('customer.create')) {
                $types['customer'] = __('report.customer');
            }
            if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
                $types['both'] = __('lang_v1.both_supplier_customer');
            }

            $customer_groups = CustomerGroup::forDropdown($business_id);

            $ob_transaction =  Transaction::where('contact_id', $id)
            ->where('type', 'opening_balance')
            ->first();
            $opening_balance = !empty($ob_transaction->final_total) ? $ob_transaction->final_total : 0;

            //Deduct paid amount from opening balance.
            if (!empty($opening_balance)) {
                $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                if (!empty($opening_balance_paid)) {
                    $opening_balance = $opening_balance - $opening_balance_paid;
                }

                $opening_balance = $this->commonUtil->num_f($opening_balance);
            }

            if(strlen($contact->cpf_cnpj) == 14){
                $type = 'f';
            }else{
                $type = 'j';
            }

            return view('contact.edit')
            ->with('cities', $this->prepareCities())
            ->with('paises', $this->preparePaises())

            ->with(compact('contact', 'types', 'customer_groups', 'opening_balance', 'type'));
        }
    }


    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['type', 'supplier_business_name', 'cpf_cnpj', 'ie_rg', 'contribuinte', 'consumidor_final', 'name', 'tax_number', 'pay_term_number', 'pay_term_type', 'mobile', 'landline', 'alternate_number', 'city', 'state', 'country', 'landmark',
                    'customer_group_id', 'contact_id', 'custom_field1', 'custom_field2', 'custom_field3',
                    'custom_field4', 'email', 'shipping_address', 'position', 'rua', 'numero', 'bairro',
                    'cep', 'city_id', 'cod_pais', 'id_estrangeiro', 'rua_entrega', 'numero_entrega',
                    'bairro_entrega', 'cep_entrega', 'city_id_entrega']);

                $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;

                $business_id = $request->session()->get('user.business_id');

                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                }

                $count = 0;

                //Check Contact id
                if (!empty($input['contact_id'])) {
                    $count = Contact::where('business_id', $business_id)
                    ->where('contact_id', $input['contact_id'])
                    ->where('id', '!=', $id)
                    ->count();
                }

                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    foreach ($input as $key => $value) {
                        $contact->$key = $value;
                    }
                    $contact->save();

                    //Get opening balance if exists
                    $ob_transaction =  Transaction::where('contact_id', $id)
                    ->where('type', 'opening_balance')
                    ->first();

                    if (!empty($ob_transaction)) {
                        $amount = $this->commonUtil->num_uf($request->input('opening_balance'));
                        $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                        if (!empty($opening_balance_paid)) {
                            $amount += $opening_balance_paid;
                        }

                        $ob_transaction->final_total = $amount;
                        $ob_transaction->save();
                        //Update opening balance payment status
                        $this->transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
                    } else {
                        //Add opening balance
                        if (!empty($request->input('opening_balance'))) {
                            $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                        }
                    }

                    $output = [
                        'success' => true,
                        'msg' => __("contact.updated_success")
                    ];
                } else {
                    throw new \Exception("Error Processing Request", 1);
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }


    public function destroy($id)
    {
        if (!auth()->user()->can('supplier.delete') && !auth()->user()->can('customer.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                //Check if any transaction related to this contact exists
                $count = Transaction::where('business_id', $business_id)
                ->where('contact_id', $id)
                ->count();
                if ($count == 0) {
                    $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                    if (!$contact->is_default) {
                        $contact->delete();
                    }
                    $output = [
                        'success' => true,
                        'msg' => __("contact.deleted_success")
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => __("lang_v1.you_cannot_delete_this_contact")
                    ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }

    public function getCustomers()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $contacts = Contact::where('business_id', $business_id)
            ->active();

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                ->where('uca.user_id', $user_id);
            }

            if (!empty($term)) {
                $contacts->where(function ($query) use ($term) {
                    $query->where('name', 'like', '%' . $term .'%')
                    ->orWhere('supplier_business_name', 'like', '%' . $term .'%')
                    ->orWhere('mobile', 'like', '%' . $term .'%')
                    ->orWhere('contacts.contact_id', 'like', '%' . $term .'%');
                });
            }

            $contacts->select(
                'contacts.id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', name, CONCAT(name, ' (', contacts.contact_id, ')')) AS text"),
                'mobile',
                'landmark',
                'city',
                'state',
                'pay_term_number',
                'pay_term_type',
                'cpf_cnpj'
            )
            ->onlyCustomers();

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('total_rp');
            }
            $contacts = $contacts->get();
            return json_encode($contacts);
        }
    }


    public function checkContactId(Request $request)
    {
        $contact_id = $request->input('contact_id');

        $valid = 'true';
        if (!empty($contact_id)) {
            $business_id = $request->session()->get('user.business_id');
            $hidden_id = $request->input('hidden_id');

            $query = Contact::where('business_id', $business_id)
            ->where('contact_id', $contact_id);
            if (!empty($hidden_id)) {
                $query->where('id', '!=', $hidden_id);
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false';
            }
        }
        echo $valid;
        exit;
    }


    public function getImportContacts()
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        $zip_loaded = extension_loaded('zip') ? true : false;

        //Check if zip extension it loaded or not.
        if ($zip_loaded === false) {
            $output = [
                'success' => 0,
                'msg' => 'Please install/enable PHP Zip archive for import'
            ];

            return view('contact.import')
            ->with('notification', $output);
        } else {
            return view('contact.import');
        }
    }


    public function postImportContacts(Request $request)
    {
        if (!auth()->user()->can('supplier.create') && !auth()->user()->can('customer.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (!empty($notAllowed)) {
                return $notAllowed;
            }

            //Set maximum php execution time
            ini_set('max_execution_time', 0);

            if ($request->hasFile('contacts_csv')) {
                $file = $request->file('contacts_csv');
                $parsed_array = Excel::toArray([], $file);
                //Remove header row
                $imported_data = array_splice($parsed_array[0], 1);

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');

                $formated_data = [];

                $is_valid = true;
                $error_msg = '';

                DB::beginTransaction();
                foreach ($imported_data as $key => $value) {
                    //Check if 21 no. of columns exists
                    if (count($value) != 30) {
                        $is_valid =  false;
                        $error_msg = "Número de colunas incompatíveis";
                        break;
                    }

                    $row_no = $key + 1;
                    $contact_array = [];

                    //Check contact type
                    $contact_type = '';
                    $contact_types = [
                        1 => 'customer',
                        2 => 'supplier',
                        3 => 'both'
                    ];
                    if (!empty($value[0])) {
                        $contact_type = strtolower(trim($value[0]));
                        if (in_array($contact_type, [1, 2, 3])) {
                            $contact_array['type'] = $contact_types[$contact_type];
                        } else {
                            $is_valid =  false;
                            $error_msg = "Tipo de contato inválido na linha $row_no";
                            break;
                        }
                    } else {
                        $is_valid =  false;
                        $error_msg = "O tipo de contato é obrigatório na linha $row_no";
                        break;
                    }

                    //Check contact name
                    if (!empty($value[1])) {
                        $contact_array['name'] = $value[1];
                    } else {
                        $is_valid =  false;
                        $error_msg = "O nome do contato é obrigatório na linha $row_no";
                        break;
                    }

                    //Check supplier fields
                    if (in_array($contact_type, ['supplier', 'both'])) {
                        //Check business name
                        if (!empty(trim($value[2]))) {
                            $contact_array['supplier_business_name'] = $value[2];
                        } else {
                            $is_valid =  false;
                            $error_msg = "O nome da empresa é obrigatório na linha $row_no";
                            break;
                        }

                        //Check pay term
                        if (trim($value[6]) != '') {
                            $contact_array['pay_term_number'] = trim($value[6]);
                        } else {
                            $is_valid =  false;
                            $error_msg = "O prazo de pagamento é exigido na linha $row_no";
                            break;
                        }

                        //Check pay period
                        $pay_term_type = strtolower(trim($value[7]));
                        if (in_array($pay_term_type, ['days', 'months'])) {
                            $contact_array['pay_term_type'] = $pay_term_type;
                        } else {
                            $is_valid =  false;
                            $error_msg = "O período de prazo de pagamento é exigido na linha $row_no";
                            break;
                        }
                    }

                    //Check contact ID
                    if (!empty(trim($value[3]))) {
                        $count = Contact::where('business_id', $business_id)
                        ->where('contact_id', $value[3])
                        ->count();


                        if ($count == 0) {
                            $contact_array['contact_id'] = $value[3];
                        } else {
                            $is_valid =  false;
                            $error_msg = "O ID do contato já existe na linha $row_no";
                            break;
                        }
                    }

                    //Tax number
                    if (!empty(trim($value[4]))) {
                        $contact_array['tax_number'] = $value[4];
                    }

                    //Check opening balance
                    if (!empty(trim($value[5])) && $value[5] != 0) {
                        $contact_array['opening_balance'] = trim($value[5]);
                    }

                    //Check credit limit
                    if (trim($value[8]) != '' && in_array($contact_type, ['customer', 'both'])) {
                        $contact_array['credit_limit'] = trim($value[8]);
                    }

                    //Check email
                    if (!empty(trim($value[9]))) {
                        if (filter_var(trim($value[9]), FILTER_VALIDATE_EMAIL)) {
                            $contact_array['email'] = $value[9];
                        } else {
                            $is_valid =  false;
                            $error_msg = "ID de e-mail inválido na linha $row_no";
                            break;
                        }
                    }

                    //Mobile number
                    if (!empty(trim($value[10]))) {
                        $contact_array['mobile'] = $value[10];
                    } else {
                        $contact_array['mobile'] = '';
                    }

                    if (!empty(trim($value[21]))) {
                        $contact_array['cpf_cnpj'] = $value[21];
                    } else {
                        $is_valid =  false;
                        $error_msg = "CPF/CNPJ é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[22]))) {
                        $contact_array['ie_rg'] = $value[22];
                    } else {
                        $contact_array['ie_rg'] = '';
                    }

                    if (!empty(trim($value[23]))) {
                        $contact_array['city_id'] = $value[23];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Cidade ID é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[24]))) {
                        $contact_array['consumidor_final'] = $value[24];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Consumidor final é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[25]))) {
                        $contact_array['contribuinte'] = $value[25];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Contribuinte é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[26]))) {
                        $contact_array['rua'] = $value[26];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Rua é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[27]))) {
                        $contact_array['numero'] = $value[27];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Número é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[28]))) {
                        $contact_array['bairro'] = $value[28];
                    } else {
                        $is_valid =  false;
                        $error_msg = "Bairro é obrigatório na linha $row_no";
                        break;
                    }

                    if (!empty(trim($value[29]))) {
                        $contact_array['cep'] = $value[29];
                    } else {
                        $is_valid =  false;
                        $error_msg = "CEP é obrigatório na linha $row_no";
                        break;
                    }


                    //Alt contact number
                    $contact_array['alternate_number'] = $value[11];

                    //Landline
                    $contact_array['landline'] = $value[12];

                    //City
                    $contact_array['city'] = $value[13];

                    //State
                    $contact_array['state'] = $value[14];

                    //Country
                    $contact_array['country'] = $value[15];

                    //Landmark
                    $contact_array['landmark'] = $value[16];

                    //Cust fields
                    $contact_array['custom_field1'] = $value[17];
                    $contact_array['custom_field2'] = $value[18];
                    $contact_array['custom_field3'] = $value[19];
                    $contact_array['custom_field4'] = $value[20];

                    $formated_data[] = $contact_array;
                }
                if (!$is_valid) {
                    throw new \Exception($error_msg);
                }

                if (!empty($formated_data)) {
                    foreach ($formated_data as $contact_data) {
                        $ref_count = $this->transactionUtil->setAndGetReferenceCount('contacts');
                        //Set contact id if empty
                        if (empty($contact_data['contact_id'])) {
                            $contact_data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count);
                        }

                        $opening_balance = 0;
                        if (isset($contact_data['opening_balance'])) {
                            $opening_balance = $contact_data['opening_balance'];
                            unset($contact_data['opening_balance']);
                        }

                        $contact_data['business_id'] = $business_id;
                        $contact_data['created_by'] = $user_id;

                        $contact = Contact::create($contact_data);

                        if (!empty($opening_balance)) {
                            $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $opening_balance);
                        }
                    }
                }

                $output = [
                    'success' => 1,
                    'msg' => __('product.file_imported_successfully')
                ];

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage()
            ];
            return redirect()->route('contacts.import')->with('notification', $output);
        }

        return redirect()->action('ContactController@index', ['type' => 'supplier'])->with('status', $output);
    }


    public function getLedger()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');

        $start_date = request()->start_date;
        $end_date =  request()->end_date;

        $contact = Contact::find($contact_id);

        $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date);

        if (request()->input('action') == 'pdf') {
            $for_pdf = true;
            $html = view('contact.ledger')
            ->with(compact('ledger_details', 'contact', 'for_pdf'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output();
        }

        return view('contact.ledger')
        ->with(compact('ledger_details', 'contact'));
    }

    public function postCustomersApi(Request $request)
    {
        try {
            $api_token = $request->header('API-TOKEN');

            $api_settings = $this->moduleUtil->getApiSettings($api_token);

            $business = Business::find($api_settings->business_id);

            $data = $request->only(['name', 'email']);

            $customer = Contact::where('business_id', $api_settings->business_id)
            ->where('email', $data['email'])
            ->whereIn('type', ['customer', 'both'])
            ->first();

            if (empty($customer)) {
                $data['type'] = 'customer';
                $data['business_id'] = $api_settings->business_id;
                $data['created_by'] = $business->owner_id;
                $data['mobile'] = 0;

                $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts', $business->id);

                $data['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count, $business->id);

                $customer = Contact::create($data);
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            return $this->respondWentWrong($e);
        }

        return $this->respond($customer);
    }


    public function sendLedger(Request $request)
    {
        $notAllowed = $this->notificationUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        try {
            $data = $request->only(['to_email', 'subject', 'email_body', 'cc', 'bcc']);
            $emails_array = array_map('trim', explode(',', $data['to_email']));

            $contact_id = $request->input('contact_id');
            $business_id = request()->session()->get('business.id');

            $start_date = request()->input('start_date');
            $end_date =  request()->input('end_date');

            $contact = Contact::find($contact_id);

            $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date);

            $orig_data = [
                'email_body' => $data['email_body'],
                'subject' => $data['subject']
            ];

            $tag_replaced_data = $this->notificationUtil->replaceTags($business_id, $orig_data, null, $contact);
            $data['email_body'] = $tag_replaced_data['email_body'];
            $data['subject'] = $tag_replaced_data['subject'];

            //replace balance_due
            $data['email_body'] = str_replace('{balance_due}', $this->notificationUtil->num_f($ledger_details['balance_due']), $data['email_body']);

            $data['email_settings'] = request()->session()->get('business.email_settings');


            $for_pdf = true;
            $html = view('contact.ledger')
            ->with(compact('ledger_details', 'contact', 'for_pdf'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);

            $file = config('constants.mpdf_temp_path') . '/' . time() . '_ledger.pdf';
            $mpdf->Output($file, 'F');

            $data['attachment'] =  $file;
            $data['attachment_name'] =  'ledger.pdf';
            \Notification::route('mail', $emails_array)
            ->notify(new CustomerNotification($data));

            if (file_exists($file)) {
                unlink($file);
            }

            $output = ['success' => 1, 'msg' => __('lang_v1.notification_sent_successfully')];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => "File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage()
            ];
        }

        return $output;
    }


    public function getSupplierStockReport($supplier_id)
    {
        $pl_query_string = $this->commonUtil->get_pl_quantity_sum_string();
        $query = PurchaseLine::join('transactions as t', 't.id', '=', 'purchase_lines.transaction_id')
        ->join('products as p', 'p.id', '=', 'purchase_lines.product_id')
        ->join('variations as v', 'v.id', '=', 'purchase_lines.variation_id')
        ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
        ->join('units as u', 'p.unit_id', '=', 'u.id')
        ->where('t.type', 'purchase')
        ->where('t.contact_id', $supplier_id)
        ->select(
            'p.name as product_name',
            'v.name as variation_name',
            'pv.name as product_variation_name',
            'p.type as product_type',
            'u.short_name as product_unit',
            'v.sub_sku',
            DB::raw('SUM(quantity) as purchase_quantity'),
            DB::raw('SUM(quantity_returned) as total_quantity_returned'),
            DB::raw('SUM(quantity_sold) as total_quantity_sold'),
            DB::raw("SUM( COALESCE(quantity - ($pl_query_string), 0) * purchase_price_inc_tax) as stock_price"),
            DB::raw("SUM( COALESCE(quantity - ($pl_query_string), 0)) as current_stock")
        )->groupBy('purchase_lines.variation_id');

        if (!empty(request()->location_id)) {
            $query->where('t.location_id', request()->location_id);
        }

        $product_stocks =  Datatables::of($query)
        ->editColumn('product_name', function ($row) {
            $name = $row->product_name;
            if ($row->product_type == 'variable') {
                $name .= ' - ' . $row->product_variation_name . '-' . $row->variation_name;
            }
            return $name . ' (' . $row->sub_sku . ')';
        })
        ->editColumn('purchase_quantity', function ($row) {
            $purchase_quantity = 0;
            if ($row->purchase_quantity) {
                $purchase_quantity =  (float)$row->purchase_quantity;
            }

            return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $purchase_quantity . '" data-unit="' . $row->product_unit . '" >' . $purchase_quantity . '</span> ' . $row->product_unit;
        })
        ->editColumn('total_quantity_sold', function ($row) {
            $total_quantity_sold = 0;
            if ($row->total_quantity_sold) {
                $total_quantity_sold =  (float)$row->total_quantity_sold;
            }

            return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $total_quantity_sold . '" data-unit="' . $row->product_unit . '" >' . $total_quantity_sold . '</span> ' . $row->product_unit;
        })
        ->editColumn('stock_price', function ($row) {
            $stock_price = 0;
            if ($row->stock_price) {
                $stock_price =  (float)$row->stock_price;
            }

            return '<span class="display_currency" data-currency_symbol=true >' . $stock_price . '</span> ';
        })
        ->editColumn('current_stock', function ($row) {
            $current_stock = 0;
            if ($row->current_stock) {
                $current_stock =  (float)$row->current_stock;
            }

            return '<span data-is_quantity="true" class="display_currency" data-currency_symbol=false  data-orig-value="' . $current_stock . '" data-unit="' . $row->product_unit . '" >' . $current_stock . '</span> ' . $row->product_unit;
        });

        return $product_stocks->rawColumns(['current_stock', 'stock_price', 'total_quantity_sold', 'purchase_quantity'])->make(true);
    }

    public function updateStatus($id)
    {
        if (!auth()->user()->can('supplier.update') && !auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);
            $contact->contact_status = $contact->contact_status == 'active' ? 'inactive' : 'active';
            $contact->save();

            $output = [
                'success' => true,
                'msg' => __("contact.updated_success")
            ];
            return $output;
        }
    }


    public function contactMap()
    {
        if (!auth()->user()->can('supplier.view') && !auth()->user()->can('customer.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $contacts = Contact::where('business_id', $business_id)
        ->active()
        ->whereNotNull('position')
        ->get();

        return view('contact.contact_map')
        ->with(compact('contacts'));
    }
}
