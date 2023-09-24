<?php

namespace Modules\Repair\Http\Controllers;

use App\Models\Transaction;
use App\Models\Variation;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Models\Contact;
use App\Models\Brands;
use App\Models\BusinessLocation;
use App\Models\Business;
use App\Models\Category;
use App\Models\City;
use App\Models\Pais;
use Modules\Repair\Entities\DeviceModel;
use Modules\Repair\Entities\JobSheetLines;
use Modules\Repair\Entities\RepairStatus;
use Modules\Repair\Utils\RepairUtil;
use App\Utils\Util;
use Modules\Repair\Entities\JobSheet;
use App\Utils\CashRegisterUtil;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Utils\ModuleUtil;
use App\Models\CustomerGroup;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Models\Media;
use Spatie\Activitylog\Models\Activity;

class JobSheetController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $repairUtil;
    protected $commonUtil;
    protected $cashRegisterUtil;
    protected $transactionUtil;
    protected $moduleUtil;
    protected $contactUtil;
    protected $productUtil;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct(
        RepairUtil $repairUtil,
        Util $commonUtil,
        CashRegisterUtil $cashRegisterUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        ContactUtil $contactUtil,
        ProductUtil $productUtil
    ) {
        $this->repairUtil       = $repairUtil;
        $this->commonUtil       = $commonUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->transactionUtil  = $transactionUtil;
        $this->moduleUtil       = $moduleUtil;
        $this->contactUtil      = $contactUtil;
        $this->productUtil      = $productUtil;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $is_user_admin = $this->commonUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            $job_sheets = JobSheet::with('invoices')
                ->leftJoin('contacts', 'repair_job_sheets.contact_id', '=', 'contacts.id')
                ->leftJoin(
                    'repair_statuses AS rs',
                    'repair_job_sheets.status_id',
                    '=',
                    'rs.id'
                )
                ->leftJoin('users as technecian', 'repair_job_sheets.service_staff', '=', 'technecian.id')
                ->leftJoin(
                    'repair_device_models as rdm',
                    'rdm.id',
                    '=',
                    'repair_job_sheets.device_model_id'
                )
                ->leftJoin(
                    'brands AS b',
                    'repair_job_sheets.brand_id',
                    '=',
                    'b.id'
                )
                ->leftJoin(
                    'business_locations AS bl',
                    'repair_job_sheets.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'categories as device',
                    'device.id',
                    '=',
                    'repair_job_sheets.device_id'
                )
                ->leftJoin('users', 'repair_job_sheets.created_by', '=', 'users.id')
                ->where('repair_job_sheets.business_id', $business_id)
                ->select(
                    'delivery_date',
                    'job_sheet_no',
                    DB::raw("CONCAT(COALESCE(technecian.surname, ''),' ',COALESCE(technecian.first_name, ''),' ',COALESCE(technecian.last_name,'')) as technecian"),
                    DB::raw("CONCAT(COALESCE(users.surname, ''),' ',COALESCE(users.first_name, ''),' ',COALESCE(users.last_name,'')) as added_by"),
                    'contacts.name as customer',
                    'b.name as brand',
                    'rdm.name as device_model',
                    'serial_no',
                    'estimated_cost',
                    'total_costs',
                    'service_costs',
                    'rs.name as status',
                    'repair_job_sheets.id as id',
                    'repair_job_sheets.created_at as created_at',
                    'service_type',
                    'rs.color as status_color',
                    'bl.name as location',
                    'rs.is_completed_status',
                    'device.name as device',
                    'repair_job_sheets.custom_field_1',
                    'repair_job_sheets.custom_field_2',
                    'repair_job_sheets.custom_field_3',
                    'repair_job_sheets.custom_field_4',
                    'repair_job_sheets.custom_field_5'
                );

            //if user is not admin get only assgined/created_by job sheet
            if (!auth()->user()->can('job_sheet.view_all')) {
                if (!$is_user_admin) {
                    $user_id = auth()->user()->id;
                    $job_sheets->where(function ($query) use ($user_id) {
                        $query->where('repair_job_sheets.service_staff', $user_id)
                            ->orWhere('repair_job_sheets.created_by', $user_id);
                    });
                }
            }

            //if location is not all get only assgined location job sheet
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $job_sheets->whereIn('repair_job_sheets.location_id', $permitted_locations);
            }

            //filter location
            if (!empty(request()->get('location_id'))) {
                $job_sheets->where('repair_job_sheets.location_id', request()->get('location_id'));
            }

            //filter by customer
            if (!empty(request()->contact_id)) {
                $job_sheets->where('repair_job_sheets.contact_id', request()->contact_id);
            }

            //filter by technecian
            if (!empty(request()->technician)) {
                $job_sheets->where('repair_job_sheets.service_staff', request()->technician);
            }

            //filter by status
            if (!empty(request()->status_id)) {
                $job_sheets->where('repair_job_sheets.status_id', request()->status_id);
            }

            //filter out mark as completed status
            if (request()->get('is_completed_status') === '1') {
                $job_sheets->where('rs.is_completed_status', 1);
            } else {
                $job_sheets->where(function ($q) {
                    $q->where('rs.is_completed_status', 0)
                        ->orWhereNull('rs.is_completed_status');
                });
            }

            return DataTables::of($job_sheets)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                <button class="btn btn-info dropdown-toggle btn-xs" type="button"  data-toggle="dropdown" aria-expanded="false">
                ' . __("messages.action") . '
                <span class="caret"></span>
                <span class="sr-only">
                ' . __("messages.action") . '
                </span>
                </button>';

                    $html .= '<ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can("job_sheet.view_assigned") || auth()->user()->can("job_sheet.view_all") || auth()->user()->can("job_sheet.create")) {
                        $html .= '<li>
                    <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@show', [$row->id]) . '" class="cursor-pointer"><i class="fa fa-eye"></i>' . __("messages.view") . '
                    </a>
                    </li>';
                    }

                    if (auth()->user()->can("sell.create") && request()->get('is_completed_status') == 1) {
                        // $html .= '<li>
                        //             <a href="' . action('SellPosController@create') . '?sub_type=repair&job_sheet_id=' . $row->id . '" class="cursor-pointer">
                        //                 <i class="fa fa-dollar-sign"></i>' . __('repair::lang.add_invoice') . '
                        //             </a>
                        //         </li>';

                        $html .= '<li>
                                    <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@convertToSell', [$row->id]) . '" class="cursor-pointer">
                                        <i class="fa fa-dollar-sign"></i>' . __('repair::lang.add_invoice') . '
                                    </a>
                                </li>';
                    }

                    if (auth()->user()->can("job_sheet.edit")) {
                        $html .= '<li>
                    <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@edit', [$row->id]) . '" class="cursor-pointer edit_job_sheet"><i class="fa fa-edit"></i>' . __("messages.edit") . '
                    </a>
                    </li>';

                        $html .= '<li>
                    <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@addParts', [$row->id]) . '" class="cursor-pointer">
                    <i class="fas fa-toolbox"></i>' . __("repair::lang.add_parts") . '
                    </a>
                    </li>';

                        $html .= '<li>
                    <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@getUploadDocs', [$row->id]) . '" class="cursor-pointer">
                    <i class="fas fa-file-alt"></i>' . __("repair::lang.upload_docs") . '
                    </a>
                    </li>';
                    }

                    $html .= '<li>
                <a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@print', [$row->id]) . '" target="_blank"><i class="fa fa-print"></i>' . __("messages.print") . '
                </a>
                </li>';

                    if (auth()->user()->can("job_sheet.create") || auth()->user()->can("job_sheet.edit")) {
                        $html .= '<li>
                    <a data-href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@editStatus', [$row->id]) . '" class="cursor-pointer edit_job_sheet_status">
                    <i class="fa fa-edit"></i>' . __("repair::lang.change_status") . '
                    </a>
                    </li>';
                    }

                    if (auth()->user()->can("job_sheet.delete")) {
                        $html .= '<li>
                    <a data-href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@destroy', [$row->id]) . '"  id="delete_job_sheet" class="cursor-pointer">
                    <i class="fas fa-trash"></i>' . __("messages.delete") . '
                    </a>
                    </li>';
                    }

                    $html .= '</ul>
                </div>';
                    return $html;
                })
                ->editColumn(
                    'delivery_date',
                    '
                @if($delivery_date)
                {{@format_datetime($delivery_date)}}
                @endif
                '
                )
                ->editColumn(
                    'created_at',
                    '{{@format_datetime($created_at)}}'
                )
                ->editColumn('service_type', function ($row) {
                    return __('repair::lang.' . $row->service_type);
                })
                ->editColumn('estimated_cost', function ($row) {
                    $cost = '<span class="display_currency total-discount" data-currency_symbol="true" data-orig-value="' . $row->estimated_cost . '">' . $row->estimated_cost . '</span>';

                    return $cost;
                })
                ->editColumn('service_costs', function ($row) {
                    $cost = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->service_costs . '">' . $row->service_costs . '</span>';

                    return $cost;
                })
                ->editColumn('total_costs', function ($row) {
                    $cost = '<span class="display_currency" data-currency_symbol="true" data-orig-value="' . $row->total_costs . '">' . $row->total_costs . '</span>';

                    return $cost;
                })
                ->editColumn('repair_no', function ($row) {
                    $invoice_no = [];
                    // dd($row->invoices);
                    if ($row->invoices->count() > 0) {
                        foreach ($row->invoices as $key => $invoice) {
                            $invoice_no[] = $invoice->invoice_no;
                        }
                    }

                    $add_invoice = '';
                    if (auth()->user()->can("repair.create") && request()->get('is_completed_status') == 1) {
                        $add_invoice = '<a href="' . action('\Modules\Repair\Http\Controllers\JobSheetController@convertToSell', [$row->id]) . '" class="cursor-pointer" style="margin-right: 5px;" data-toggle="tooltip" title="' . __('repair::lang.add_invoice') . '">
                    <i class="fas fa-plus-circle"></i></a>';
                    }

                    return $add_invoice . implode(', ', $invoice_no);
                })
                ->editColumn('status', function ($row) {
                    $html = '<a data-href="' . action("\Modules\Repair\Http\Controllers\JobSheetController@editStatus", [$row->id]) . '" class="cursor-pointer edit_job_sheet_status" data-orig-value="' . $row->status . '" data-status-name="' . $row->status . '">
                <span class="label " style="background-color:' . $row->status_color . ';" >
                ' . $row->status . '
                </span>
                </a>
                ';
                    return $html;
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'service_type', 'delivery_date', 'repair_no', 'status', 'estimated_cost', 'total_costs', 'service_costs', 'created_at'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers          = Contact::customersDropdown($business_id, false);
        $status_dropdown    = RepairStatus::forDropdown($business_id);
        $service_staffs     = $this->commonUtil->serviceStaffDropdown($business_id);

        $user_role_as_service_staff = auth()->user()->roles()
            ->where('is_service_staff', 1)
            ->get()
            ->toArray();
        $is_user_service_staff      = false;
        if (!empty($user_role_as_service_staff) && !$is_user_admin) {
            $is_user_service_staff = true;
        }

        $repair_settings = $this->repairUtil->getRepairSettings($business_id);

        return view('repair::job_sheet.index')
            ->with(
                compact(
                    'business_locations',
                    'customers',
                    'status_dropdown',
                    'service_staffs',
                    'is_user_service_staff',
                    'repair_settings'
                )
            );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.create')))) {
            abort(403, 'Unauthorized action.');
        }

        $repair_statuses    = RepairStatus::getRepairSatuses($business_id);
        $device_models      = DeviceModel::forDropdown($business_id);
        $brands             = Brands::forDropdown($business_id, false, true);
        $devices            = Category::forDropdown($business_id, 'device');
        $repair_settings    = $this->repairUtil->getRepairSettings($business_id);
        $business_locations = BusinessLocation::forDropdown($business_id);
        $types              = Contact::getContactTypes();
        $customer_groups    = CustomerGroup::forDropdown($business_id);
        $walk_in_customer   = $this->contactUtil->getWalkInCustomer($business_id);
        $default_status     = '';
        if (!empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        //get service staff(technecians)
        $technecians = [];
        if ($this->commonUtil->isModuleEnabled('service_staff')) {
            $technecians = $this->commonUtil->serviceStaffDropdown($business_id);
        }
        $tipo = 'customer';

        return view('repair::job_sheet.create')
            ->with(
                compact(
                    'repair_statuses',
                    'device_models',
                    'brands',
                    'devices',
                    'default_status',
                    'technecians',
                    'business_locations',
                    'types',
                    'customer_groups',
                    'walk_in_customer',
                    'repair_settings',
                    'tipo'
                )
            )
            ->with('cities', $this->prepareCities())
            ->with('estados', $this->prepareEstados())
            ->with('paises', $this->preparePaises());
    }

    private function prepareCities()
    {
        $cities = City::all();
        $temp   = [];
        foreach ($cities as $c) {
            // array_push($temp, $c->id => $c->nome);
            $temp[$c->id] = $c->nome . " ($c->uf)";
        }
        return $temp;
    }

    private function preparePaises()
    {
        $paises = Pais::all();
        $temp   = [];
        foreach ($paises as $p) {
            // array_push($temp, $c->id => $c->nome);
            $temp[$p->codigo] = "$p->codigo - $p->nome";
        }
        return $temp;
    }

    private function prepareEstados()
    {

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
     * @param Request $request
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.create')))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(
                'contact_id',
                'service_type',
                'brand_id',
                'device_id',
                'device_model_id',
                'security_pwd',
                'security_pattern',
                'serial_no',
                'status_id',
                'delivery_date',
                'estimated_cost',
                'product_configuration',
                'defects',
                'product_condition',
                'service_staff',
                'location_id',
                'service_costs',
                'pick_up_on_site_addr',
                'comment_by_ss',
                'custom_field_1',
                'custom_field_2',
                'custom_field_3',
                'custom_field_4',
                'custom_field_5'
            );

            if (!empty($input['delivery_date'])) {
                $input['delivery_date'] = $this->commonUtil->uf_date($input['delivery_date'], true);
            }

            if (!empty($input['estimated_cost'])) {
                $input['estimated_cost'] = $this->commonUtil->num_uf($input['estimated_cost']);
            }

            if (!empty($request->input('repair_checklist'))) {
                $input['checklist'] = $request->input('repair_checklist');
            }

            DB::beginTransaction();

            if (!empty($input['brand_id']) and !is_numeric($input['brand_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'        => $input['brand_id'],
                    'description' => '',
                    'business_id' => $business_id,
                    'created_by'  => $user_id
                ];

                $input['brand_id'] = Brands::create($data)->id;
            }

            if (!empty($input['device_id']) and !is_numeric($input['device_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'          => $input['device_id'],
                    'short_code'    => null,
                    'image'         => '',
                    'category_type' => 'device',
                    'business_id'   => $business_id,
                    'created_by'    => $user_id
                ];

                $input['device_id'] = Category::create($data)->id;
            }

            if (!empty($input['device_model_id']) and !is_numeric($input['device_model_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'             => $input['device_model_id'],
                    'brand_id'         => $input['brand_id'],
                    'device_id'        => $input['device_id'],
                    'repair_checklist' => '',
                    'business_id'      => $business_id,
                    'created_by'       => $user_id
                ];

                $input['device_model_id'] = DeviceModel::create($data)->id;
            }

            //Generate reference number
            $ref_count       = $this->commonUtil->setAndGetReferenceCount('job_sheet', $business_id);
            $business        = Business::find($business_id);
            $repair_settings = json_decode($business->repair_settings, true);

            $job_sheet_prefix = '';
            if (isset($repair_settings['job_sheet_prefix'])) {
                $job_sheet_prefix = $repair_settings['job_sheet_prefix'];
            }

            $input['job_sheet_no'] = $this->commonUtil->generateReferenceNumber('job_sheet', $ref_count, null, $job_sheet_prefix);

            $input['created_by']  = $request->user()->id;
            $input['business_id'] = $business_id;

            $job_sheet = JobSheet::create($input);

            //upload media
            Media::uploadMedia($business_id, $job_sheet, $request, 'images');

            if (!empty($request->input('send_notification')) && in_array('sms', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                if (!empty($status->sms_template))
                    $this->repairUtil->sendJobSheetUpdateSmsNotification($status->sms_template, $job_sheet);
            }

            if (!empty($request->input('send_notification')) && in_array('email', $request->input('send_notification'))) {
                $status       = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                $notification = [
                    'subject' => $status->email_subject,
                    'body'    => $status->email_body
                ];
                if (!empty($status->email_subject) && !empty($status->email_body))
                    $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
            }

            DB::commit();

            if (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action('\Modules\Repair\Http\Controllers\JobSheetController@addParts', [$job_sheet->id])
                    ->with('status', [
                        'success' => true,
                        'msg'     => __("lang_v1.success")
                    ]);
            } elseif (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_upload_docs') {
                return redirect()
                    ->action('\Modules\Repair\Http\Controllers\JobSheetController@getUploadDocs', [$job_sheet->id])
                    ->with(
                        'status',
                        [
                            'success' => true,
                            'msg'     => __("lang_v1.success")
                        ]
                    );
            }

            return redirect()
                ->action('\Modules\Repair\Http\Controllers\JobSheetController@show', [$job_sheet->id])
                ->with('status', [
                    'success' => true,
                    'msg'     => __("lang_v1.success")
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg'     => __('messages.something_went_wrong')
                ]);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $query = JobSheet::with(
            [
                'sheet_lines',
                'sheet_lines.product' => function ($query) {
                    $query->select('id', 'unit_id', 'name')->with('unit');
                },
                'customer',
                'customer.business',
                'technician',
                'status',
                'Brand',
                'Device',
                'deviceModel',
                'businessLocation',
                'invoices',
                'media'
            ]
        )->where('business_id', $business_id);

        //if user is not admin or didn't have permission `job_sheet.view_all` get only assgined/created_by job sheet
        if (!($this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('job_sheet.view_all'))) {
            $user_id = auth()->user()->id;
            $query->where(function ($q) use ($user_id) {
                $q->where('repair_job_sheets.service_staff', $user_id)
                    ->orWhere('repair_job_sheets.created_by', $user_id);
            });
        }

        $job_sheet = $query->findOrFail($id);

        // dd($job_sheet->toArray());

        $business        = Business::find($business_id);
        $repair_settings = json_decode($business->repair_settings, true);

        $activities = Activity::forSubject($job_sheet)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        // dd($job_sheet->toArray());

        return view('repair::job_sheet.show')
            ->with(
                compact(
                    'job_sheet',
                    'repair_settings',
                    'activities'
                )
            );
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.edit')))) {
            abort(403, 'Unauthorized action.');
        }

        $job_sheet = JobSheet::where('business_id', $business_id)
            ->findOrFail($id);

        $repair_statuses = RepairStatus::getRepairSatuses($business_id);
        $device_models   = DeviceModel::forDropdown($business_id);
        $brands          = Brands::forDropdown($business_id, false, true);
        $devices         = Category::forDropdown($business_id, 'device');
        $repair_settings = $this->repairUtil->getRepairSettings($business_id);
        $types           = Contact::getContactTypes();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $default_status  = '';
        if (!empty($repair_settings['default_status'])) {
            $default_status = $repair_settings['default_status'];
        }

        //get service staff(technecians)
        $technecians = [];
        if ($this->commonUtil->isModuleEnabled('service_staff')) {
            $technecians = $this->commonUtil->serviceStaffDropdown($business_id);
        }

        // dd($job_sheet);

        $tipo = 'customer';
        return view('repair::job_sheet.edit')
            ->with(
                compact(
                    'job_sheet',
                    'repair_statuses',
                    'device_models',
                    'brands',
                    'devices',
                    'default_status',
                    'technecians',
                    'types',
                    'customer_groups',
                    'repair_settings',
                    'tipo'
                )
            )
            ->with('cities', $this->prepareCities())
            ->with('estados', $this->prepareEstados())
            ->with('paises', $this->preparePaises());
        ;
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.edit')))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(
                'contact_id',
                'service_type',
                'brand_id',
                'device_id',
                'device_model_id',
                'security_pwd',
                'security_pattern',
                'serial_no',
                'status_id',
                'delivery_date',
                'estimated_cost',
                'product_configuration',
                'defects',
                'product_condition',
                'service_staff',
                'pick_up_on_site_addr',
                'service_costs',
                'comment_by_ss',
                'custom_field_1',
                'custom_field_2',
                'custom_field_3',
                'custom_field_4',
                'custom_field_5'
            );

            if (!empty($input['delivery_date'])) {
                $input['delivery_date'] = $this->commonUtil->uf_date($input['delivery_date'], true);
            }

            if (!empty($input['estimated_cost'])) {
                $input['estimated_cost'] = $this->commonUtil->num_uf($input['estimated_cost']);
            }

            if (!empty($request->input('repair_checklist'))) {
                $input['checklist'] = $request->input('repair_checklist');
            }

            DB::beginTransaction();

            if (!empty($input['brand_id']) and !is_numeric($input['brand_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'        => $input['brand_id'],
                    'description' => '',
                    'business_id' => $business_id,
                    'created_by'  => $user_id
                ];

                $input['brand_id'] = Brands::create($data)->id;
            }

            if (!empty($input['device_id']) and !is_numeric($input['device_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'          => $input['device_id'],
                    'short_code'    => null,
                    'image'         => '',
                    'category_type' => 'device',
                    'business_id'   => $business_id,
                    'created_by'    => $user_id
                ];

                $input['device_id'] = Category::create($data)->id;
            }

            if (!empty($input['device_model_id']) and !is_numeric($input['device_model_id'])) {
                $business_id = $request->session()->get('user.business_id');
                $user_id     = $request->session()->get('user.id');

                $data = [
                    'name'             => $input['device_model_id'],
                    'brand_id'         => $input['brand_id'],
                    'device_id'        => $input['device_id'],
                    'repair_checklist' => '',
                    'business_id'      => $business_id,
                    'created_by'       => $user_id
                ];

                $input['device_model_id'] = DeviceModel::create($data)->id;
            }

            $job_sheet = JobSheet::where('business_id', $business_id)
                ->findOrFail($id);

            $job_sheet->update($input);

            $this->updateTotalCost($id);

            //upload media
            Media::uploadMedia($business_id, $job_sheet, $request, 'images');

            if (!empty($request->input('send_notification')) && in_array('sms', $request->input('send_notification'))) {
                $status = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                if (!empty($status->sms_template))
                    $this->repairUtil->sendJobSheetUpdateSmsNotification($status->sms_template, $job_sheet);
            }

            if (!empty($request->input('send_notification')) && in_array('email', $request->input('send_notification'))) {
                $status       = RepairStatus::where('business_id', $business_id)
                    ->find($job_sheet->status_id);
                $notification = [
                    'subject' => $status->email_subject,
                    'body'    => $status->email_body
                ];
                if (!empty($status->email_subject) && !empty($status->email_body))
                    $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
            }

            DB::commit();

            if (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_add_parts') {
                return redirect()
                    ->action('\Modules\Repair\Http\Controllers\JobSheetController@addParts', [$job_sheet->id])
                    ->with('status', [
                        'success' => true,
                        'msg'     => __("lang_v1.success")
                    ]);
            } elseif (!empty($request->input('submit_type')) && $request->input('submit_type') == 'save_and_upload_docs') {
                return redirect()
                    ->action('\Modules\Repair\Http\Controllers\JobSheetController@getUploadDocs', [$job_sheet->id])
                    ->with('status', [
                        'success' => true,
                        'msg'     => __("lang_v1.success")
                    ]);
            }

            return redirect()
                ->action('\Modules\Repair\Http\Controllers\JobSheetController@show', [$job_sheet->id])
                ->with('status', [
                    'success' => true,
                    'msg'     => __("lang_v1.success")
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile() . ":" . $e->getLine() . "Message:" . $e->getMessage());

            return redirect()->back()
                ->with('status', [
                    'success' => false,
                    'msg'     => __('messages.something_went_wrong')
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && auth()->user()->can('job_sheet.delete')))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $job_sheet = JobSheet::where('business_id', $business_id)
                    ->findOrFail($id);

                $job_sheet->delete();
                $job_sheet->media()->delete();

                $output = [
                    'success' => true,
                    'msg'     => __("lang_v1.success")
                ];
            } catch (\Exception $e) {
                $output = [
                    'success' => false,
                    'msg'     => __('messages.something_went_wrong')
                ];
            }

            return $output;
        }
    }

    /**
     * Show the form for editing the status
     * @param int $id
     */
    public function editStatus($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $job_sheet = JobSheet::where('business_id', $business_id)->with(['status'])->findOrFail($id);

            $status_dropdown      = RepairStatus::forDropdown($business_id, true);
            $status_template_tags = $this->repairUtil->getRepairStatusTemplateTags();
            return view('repair::job_sheet.partials.edit_status')
                ->with(
                    compact(
                        'job_sheet',
                        'status_dropdown',
                        'status_template_tags'
                    )
                );
        }
    }

    private function updateJobsheetStatus($input, $jobsheet_id)
    {
        $job_sheet            = JobSheet::where('business_id', $input['business_id'])->findOrFail($jobsheet_id);
        $job_sheet->status_id = $input['status_id'];
        $job_sheet->save();

        $status = RepairStatus::where('business_id', $input['business_id'])->findOrFail($input['status_id']);

        //send job sheet updates
        if (!empty($input['send_sms'])) {
            $sms_body = $input['sms_body'];
            $response = $this->repairUtil->sendJobSheetUpdateSmsNotification($sms_body, $job_sheet);
        }

        if (!empty($input['send_email'])) {
            $subject      = $input['email_subject'];
            $body         = $input['email_body'];
            $notification = [
                'subject' => $subject,
                'body'    => $body
            ];
            if (!empty($subject) && !empty($body))
                $this->repairUtil->sendJobSheetUpdateEmailNotification($notification, $job_sheet);
        }

        activity()
            ->performedOn($job_sheet)
            ->withProperties(['update_note' => $input['update_note'], 'updated_status' => $status->name])
            ->log('status_changed');
    }

    public function updateStatus(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            try {
                $input = $request->only([
                    'status_id',
                    'update_note'
                ]);

                $input['business_id'] = $business_id;

                if (!empty($request->input('send_sms'))) {
                    $input['send_sms'] = true;
                    $input['sms_body'] = $request->input('sms_body');
                }
                if (!empty($request->input('send_email'))) {
                    $input['send_email']    = true;
                    $input['email_body']    = $request->input('email_body');
                    $input['email_subject'] = $request->input('email_subject');
                }
                $status_id = $request->input('status_id');

                $status = RepairStatus::find($status_id);

                if ($status->is_completed_status == 1) {
                    $input['job_sheet_id'] = $id;
                    $request->session()->put('repair_status_update_data', $input);
                    return $output = ['success' => true];
                }

                $this->updateJobsheetStatus($input, $id);

                $output = [
                    'success' => true,
                    'msg'     => __("lang_v1.success")
                ];
            } catch (Exception $e) {
                $output = [
                    'success' => false,
                    'msg'     => __('messages.something_went_wrong')
                ];
            }

            return $output;
        }
    }

    public function deleteJobSheetImage(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {

                Media::deleteMedia($business_id, $id);

                $output = [
                    'success' => true,
                    'msg'     => __("lang_v1.success")
                ];
            } catch (\Exception $e) {
                $output = [
                    'success' => false,
                    'msg'     => __('messages.something_went_wrong')
                ];
            }

            return $output;
        }
    }

    public function addParts($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        $status_update_data = request()->session()->get('repair_status_update_data');

        $job_sheet = JobSheet::where('business_id', $business_id)->with([
            'sheet_lines',
            'sheet_lines.product' => function ($query) {
                $query->select('id', 'unit_id', 'name')->with('unit');
            }
        ])->findOrFail($id);

        // dd($job_sheet->toArray());

        $status_dropdown      = RepairStatus::forDropdown($business_id, true);
        $status_template_tags = $this->repairUtil->getRepairStatusTemplateTags();

        return view('repair::job_sheet.add_parts')
            ->with(
                compact(
                    'job_sheet',
                    'status_update_data',
                    'status_dropdown',
                    'status_template_tags'
                )
            );
    }

    function updateTotalCost(int $jobSheetsId)
    {
        DB::statement("CALL update_total_costs(?)", [
            $jobSheetsId
        ]);
    }

    public function saveParts(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $parts = $request->input('parts');

            // dd('saveParts', $parts);

            JobSheetLines::where('job_sheets_id', $id)->delete();

            foreach ($parts as $variation_id => $part) {
                $data = [
                    'job_sheets_id'      => $id,
                    'product_id'         => $part['product_id'],
                    'variation_id'       => $variation_id,
                    'quantity'           => $part['quantity'],
                    'unit_price'         => $part['unit_price'],
                    'unit_price_inc_tax' => $part['unit_price'],
                    'item_tax'           => 0,
                ];

                JobSheetLines::create($data);
            }

            $this->updateTotalCost($id);

            if (!empty($request->session()->get('repair_status_update_data')) && !empty($request->input('status_id'))) {
                $input = $request->only([
                    'status_id',
                    'update_note'
                ]);

                $input['business_id'] = $business_id;

                if (!empty($request->input('send_sms'))) {
                    $input['send_sms'] = true;
                    $input['sms_body'] = $request->input('sms_body');
                }
                if (!empty($request->input('send_email'))) {
                    $input['send_email']    = true;
                    $input['email_body']    = $request->input('email_body');
                    $input['email_subject'] = $request->input('email_subject');
                }

                $this->updateJobsheetStatus($input, $id);

                $request->session()->forget('repair_status_update_data');
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . ":" . $e->getLine() . "Message:" . $e->getMessage());
        }

        return redirect()
            ->action('\Modules\Repair\Http\Controllers\JobSheetController@show', [$id])
            ->with('status', [
                'success' => true,
                'msg'     => __("lang_v1.success")
            ]);
    }

    public function jobsheetPartRow(Request $request)
    {
        if (request()->ajax()) {
            $business_id  = $request->session()->get('user.business_id');
            $variation_id = $request->input('variation_id');

            $variation = Variation::with([
                'product' => function ($query) use ($business_id) {
                    $query->select('id', 'unit_id', 'name')
                        ->where('business_id', $business_id)
                        ->with('unit');
                }
            ])->findOrFail($variation_id);

            // dd($variation->toArray());

            // $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id);

            // dd($product);

            // $variation_name = $product->product_name . ' - ' . $product->sub_sku;
            // $variation_id   = $product->variation_id;
            // $quantity       = 1;
            // $unit           = $product->unit;

            return view('repair::job_sheet.partials.job_sheet_part_row')
                ->with([
                    'product'      => $variation->product,
                    'unit_price'   => $variation->sell_price_inc_tax,
                    'variation_id' => $variation->id,
                    'quantity'     => 1,
                ]);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     */
    public function print($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
            abort(403, 'Unauthorized action.');
        }

        $query = JobSheet::with([
            'sheet_lines',
            'sheet_lines.product' => function ($query) {
                $query->select('id', 'unit_id', 'name')->with('unit');
            },
            'customer',
            'customer.business',
            'technician',
            'status',
            'Brand',
            'Device',
            'deviceModel',
            'businessLocation',
            'invoices',
            'media'
        ])
            ->where('business_id', $business_id);

        //if user is not admin or didn't have permission `job_sheet.view_all` get only assgined/created_by job sheet
        if (!($this->commonUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('job_sheet.view_all'))) {
            $user_id = auth()->user()->id;
            $query->where(function ($q) use ($user_id) {
                $q->where('repair_job_sheets.service_staff', $user_id)
                    ->orWhere('repair_job_sheets.created_by', $user_id);
            });
        }

        $job_sheet = $query->findOrFail($id);

        $business        = Business::find($business_id);
        $repair_settings = json_decode($business->repair_settings, true);

        $html = view('repair::job_sheet.print_pdf')->with(
            compact(
                'job_sheet',
                'repair_settings'
            )
        )->render();

        // dd($job_sheet->toArray());

        $mpdf = new \Mpdf\Mpdf([
            'tempDir'          => public_path('uploads/temp'),
            'mode'             => 'utf-8',
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
            'autoVietnamese'   => true,
            'autoArabic'       => true,
            'margin_top'       => 8,
            'margin_bottom'    => 8
        ]);

        $mpdf->useSubstitutions = true;
        $mpdf->SetTitle(__('repair::lang.job_sheet') . ' | ' . $job_sheet->job_sheet_no);
        $mpdf->WriteHTML($html);
        $mpdf->Output('job_sheet.pdf', 'I');

        return view('repair::job_sheet.print_pdf')
            ->with(
                compact(
                    'job_sheet',
                    'repair_settings',
                )
            );
    }

    public function getUploadDocs($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        $job_sheet = JobSheet::with(['media'])
            ->where('business_id', $business_id)
            ->findOrFail($id);

        return view('repair::job_sheet.upload_doc', compact('job_sheet'));
    }

    public function postUploadDocs(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.edit'))))) {
            abort(403, 'Unauthorized action.');
        }

        try {

            $images = json_decode($request->input('images'), true);

            $job_sheet = JobSheet::where('business_id', $business_id)
                ->findOrFail($request->input('job_sheet_id'));

            if (!empty($images) && !empty($job_sheet)) {

                Media::attachMediaToModel($job_sheet, $business_id, $images);
            }

            $output = [
                'success' => true,
                'msg'     => __("lang_v1.success")
            ];

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . ":" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg'     => __('messages.something_went_wrong')
            ];
        }

        return redirect()
            ->action('\Modules\Repair\Http\Controllers\JobSheetController@show', [$job_sheet->id])
            ->with('status', [
                'success' => true,
                'msg'     => __("lang_v1.success")
            ]);
    }

    public function convertToSell($id)
    {
        try {
            $user_id     = request()->session()->get('user.id');
            $business_id = request()->session()->get('user.business_id');

            if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
                abort(403, 'Unauthorized action.');
            }

            if (!(auth()->user()->can('superadmin') || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') && (auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all') || auth()->user()->can('job_sheet.create'))))) {
                abort(403, 'Unauthorized action.');
            }

            $query = JobSheet::with(
                [
                    'sheet_lines.product' => function ($query) {
                        $query->with('unit');
                    },
                    'customer',
                    'customer.business',
                    'technician',
                    'status',
                    'Brand',
                    'Device',
                    'deviceModel',
                    'businessLocation',
                    'invoices',
                    'media'
                ]
            )->where('business_id', $business_id);

            $job_sheet = $query->findOrFail($id);

            // dd($job_sheet->toArray());

            $products = array_map(function ($item) {
                return [
                    'product_type'         => $item['product']['type'],
                    'unit_price'           => $item['unit_price'],
                    'line_discount_type'   => 'fixed',
                    'line_discount_amount' => '0',
                    'item_tax'             => '0',
                    'tax_id'               => null,
                    'sell_line_note'       => null,
                    'product_unit_id'      => $item['product']['unit_id'],
                    'sub_unit_id'          => $item['product']['sub_unit_ids'],
                    'product_id'           => $item['product_id'],
                    'variation_id'         => $item['variation_id'],
                    'enable_stock'         => '1',
                    'quantity'             => $item['quantity'],
                    'base_unit_multiplier' => '1',
                    'res_service_staff_id' => null,
                    'unit_price_inc_tax'   => $item['unit_price_inc_tax'],
                ];
            }, $job_sheet->sheet_lines->toArray());

            // dd($produtos);

            // if (empty($produtos)) {
            //     return back()->with('status', [
            //         'success' => 0,
            //         'msg'     => trans("messages.something_went_wrong")
            //     ]);
            // }

            $order_data = [
                'invoice_no'              => $this->transactionUtil->getInvoiceNumber($business_id, 'final', $job_sheet->location_id, null),
                'business_id'             => $business_id,
                'location_id'             => $job_sheet->location_id,
                'contact_id'              => $job_sheet->contact_id,
                'final_total'             => $job_sheet->contact_id,
                'created_by'              => $user_id,
                'status'                  => 'final',
                'type'                    => 'sell',
                'sub_type'                => 'repair',
                'payment'                 => [],
                'payment_status'          => 'due',
                'additional_notes'        => '',
                'transaction_date'        => \Carbon::now(),
                'customer_group_id'       => $job_sheet->customer->customer_group_id ?? null,
                'tax_rate_id'             => null,
                'sale_note'               => null,
                'commission_agent'        => null,
                'order_addresses'         => '',
                'products'                => $products,
                'is_created_from_api'     => 0,
                'discount_type'           => 'fixed',
                'discount_amount'         => 0,
                'repair_brand_id'         => $job_sheet->brand_id,
                'repair_status_id'        => $job_sheet->status->id ?? null,
                'repair_model_id'         => $job_sheet->device_model_id,
                'repair_job_sheet_id'     => $job_sheet->id,
                'repair_defects'          => $job_sheet->defects,
                'repair_serial_no'        => $job_sheet->serial_no,
                'repair_security_pwd'     => $job_sheet->security_pwd,
                'repair_security_pattern' => $job_sheet->security_pattern,
                'repair_due_date'         => $job_sheet->nada,
                'repair_device_id'        => $job_sheet->device_id,
            ];

            $invoice_total = [
                'total_before_tax' => $job_sheet->total_costs ?? 0,
                'tax'              => 0,
            ];

            // Check if subscribed or not, then check for users quota
            if (!$this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
            }

            // dd($order_data);

            DB::beginTransaction();

            $transaction = Transaction::create($order_data);

            $this->transactionUtil->createOrUpdateSellLines((object) $transaction, $products, $order_data['location_id']);

            // dd($transaction->res_table_id);

            DB::commit();

            // $receipt = $this->receiptContent($business_id, $order_data['location_id'], $transaction->id, null, false, true, null);

            return redirect(action('SellPosController@edit', [$transaction->id]) . '?sub_type=repair')
                ->with('status', [
                    'success' => 1,
                    'msg'     => trans("sale.pos_sale_added")
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile() . ":" . $e->getLine() . "Message:" . $e->getMessage());

            $msg = trans("messages.something_went_wrong");

            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            $output = [
                'success' => 0,
                'msg'     => $e->getMessage() . " - " . $e->getFile()
            ];
        }

    }
}
