<?php

namespace Modules\Repair\Http\Controllers;

use App\Models\Brands;
use App\Models\Category;
use App\Models\Transaction;
use App\Utils\ModuleUtil;
use App\Models\Warranty;
use DB;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Menu;
use Modules\Repair\Entities\DeviceModel;
use Modules\Repair\Entities\RepairStatus;
use Modules\Repair\Utils\RepairUtil;
use Modules\Repair\Entities\JobSheet;

class DataController extends Controller
{

    /**
     * Sets sell fields from module
     * An input field with name "has_module_data" is required in the request to call this function
     * @param array $data
     * @return obj
     */
    public function after_sale_saved($data)
    {
        $transaction = $data['transaction'];
        $input       = $data['input'];

        $repairUtil = new RepairUtil();

        $repair_module_fields = [
            'repair_brand_id',
            'repair_device_id',
            'repair_model_id',
            'repair_serial_no',
            'repair_security_pwd',
            'repair_security_pattern',
            'repair_defects',
            'repair_status_id',
            'repair_warranty_id',
            'repair_job_sheet_id'
        ];

        foreach ($repair_module_fields as $field) {
            if (isset($input[$field])) {
                $transaction->$field = $input[$field];
            }
        }
        if (isset($input['repair_checklist'])) {
            $transaction->repair_checklist = json_encode($input['repair_checklist']);
        }

        $transaction->repair_completed_on = !empty($input['repair_completed_on']) ? $repairUtil->uf_date($input['repair_completed_on'], true) : null;
        $transaction->repair_due_date     = !empty($input['repair_due_date']) ? $repairUtil->uf_date($input['repair_due_date'], true) : null;

        $transaction->save();

        return $transaction;
    }

    /**
     * Defines user permissions for the module.
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value'   => 'repair.create',
                'label'   => __('repair::lang.add_invoice'),
                'default' => false
            ],
            [
                'value'   => 'repair.update',
                'label'   => __('repair::lang.edit_invoice'),
                'default' => false
            ],
            [
                'value'            => 'repair.view',
                'label'            => __('repair::lang.view_all_invoice'),
                'default'          => false,
                'is_radio'         => true,
                'radio_input_name' => 'repair_invoice_view'
            ],
            [
                'value'            => 'repair.view_own',
                'label'            => __('repair::lang.view_own_invoice'),
                'default'          => false,
                'is_radio'         => true,
                'radio_input_name' => 'repair_invoice_view'
            ],
            [
                'value'   => 'repair.delete',
                'label'   => __('repair::lang.delete_invoice'),
                'default' => false
            ],
            [
                'value'   => 'repair_status.update',
                'label'   => __('repair::lang.change_invoice_status'),
                'default' => false
            ],
            [
                'value'   => 'repair_status.access',
                'label'   => __('repair::lang.access_job_sheet_status'),
                'default' => false
            ],
            [
                'value'   => 'job_sheet.create',
                'label'   => __('repair::lang.add_job_sheet'),
                'default' => false
            ],
            [
                'value'   => 'job_sheet.edit',
                'label'   => __('repair::lang.edit_job_sheet'),
                'default' => false
            ],
            [
                'value'   => 'job_sheet.delete',
                'label'   => __('repair::lang.delete_job_sheet'),
                'default' => false
            ],
            [
                'value'            => 'job_sheet.view_assigned',
                'label'            => __('repair::lang.view_assigned_job_sheet'),
                'default'          => false,
                'is_radio'         => true,
                'radio_input_name' => 'job_sheet_view'
            ],
            [
                'value'            => 'job_sheet.view_all',
                'label'            => __('repair::lang.view_all_job_sheet'),
                'default'          => false,
                'is_radio'         => true,
                'radio_input_name' => 'job_sheet_view'
            ]

        ];
    }

    public function superadmin_package()
    {
        return [
            [
                'name'    => 'repair_module',
                'label'   => __('repair::lang.repair_module'),
                'default' => false
            ]
        ];
    }

    /**
     * Adds Repair menus
     * @return null
     */
    public function modifyAdminMenu()
    {
        $business_id       = session()->get('user.business_id');
        $module_util       = new ModuleUtil();
        $is_repair_enabled = (boolean) $module_util->hasThePermissionInSubscription($business_id, 'repair_module');

        $background_color = '';
        if (config('app.env') == 'demo') {
            $background_color = '#bc8f8f !important';
        }

        if ($is_repair_enabled && (auth()->user()->can('superadmin') || auth()->user()->can('repair.view') || auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all'))) {
            Menu::modify('admin-sidebar-menu', function ($menu) use ($background_color) {
                $menu->dropdown(
                    __('repair::lang.repair'),
                    function ($sub) use ($background_color) {
                        $sub->url(
                            action('\Modules\Repair\Http\Controllers\DashboardController@index'),
                            __('repair::lang.dashboard'),
                            [
                                'icon'   => 'fa fas fa-wrench',
                                'active' => request()->segment(2) == 'dashboard',
                                'style'  => 'background-color:' . $background_color
                            ]
                        );
                        if (auth()->user()->can('job_sheet.create') || auth()->user()->can('job_sheet.view_assigned') || auth()->user()->can('job_sheet.view_all')) {
                            $sub->url(
                                action('\Modules\Repair\Http\Controllers\JobSheetController@index'),
                                __('repair::lang.job_sheets'),
                                [
                                    'icon'   => 'fa fas fa-wrench',
                                    'active' => request()->segment(2) == 'job-sheet' && request()->segment(3) == null,
                                    'style'  => 'background-color:' . $background_color
                                ]
                            );

                            $sub->url(
                                action('\Modules\Repair\Http\Controllers\JobSheetController@create'),
                                __('repair::lang.add_job_sheet'),
                                [
                                    'icon'   => 'fa fas fa-wrench',
                                    'active' => request()->segment(3) == 'create',
                                    'style'  => 'background-color:' . $background_color
                                ]
                            );

                            $sub->url(
                                action('\Modules\Repair\Http\Controllers\RepairController@index'),
                                __('repair::lang.list_invoices'),
                                [
                                    'icon'   => 'fa fas fa-wrench',
                                    'active' => request()->segment(2) == 'repair' && empty(request()->segment(3)),
                                    'style'  => 'background-color:' . $background_color
                                ]
                            );

                            $sub->url(
                                action('SellPosController@create'). '?sub_type=repair',
                                __('repair::lang.add_invoice'),
                                [
                                    'icon'   => 'fa fas fa-wrench',
                                    'active' => request()->segment(2) == 'repair' && request()->segment(3) == 'create',
                                    'style'  => 'background-color:' . $background_color
                                ]
                            );

                            $sub->url(
                                action('\Modules\Repair\Http\Controllers\RepairSettingsController@index'),
                                __('messages.settings'),
                                [
                                    'icon'   => 'fa fas fa-wrench',
                                    'active' => request()->segment(1) == 'repair' && request()->segment(2) == 'repair-settings',
                                    'style'  => 'background-color:' . $background_color
                                ]
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-wrench']
                )->order(25);

            });
        }
    }

    /**
     * Returns view/js path with required extra data.
     * for pos screen
     * @return array
     */
    public function get_pos_screen_view($params = null)
    {

        $business_id       = session()->get('user.business_id');
        $module_util       = new ModuleUtil();
        $is_repair_enabled = (boolean) $module_util->hasThePermissionInSubscription($business_id, 'repair_module');

        if ($is_repair_enabled && $params == 'repair') {
            if ($is_repair_enabled && (!is_null($params == 'repair'))) {
                $repairUtil      = new RepairUtil();
                $repair_settings = $repairUtil->getRepairSettings($business_id);

                $default_status = '';
                if (!empty($repair_settings['default_status'])) {
                    $default_status = $repair_settings['default_status'];
                }

                $repair_statuses = RepairStatus::getRepairSatuses($business_id);
                $device_models   = DeviceModel::forDropdown($business_id);
                $brands          = Brands::forDropdown($business_id);
                $devices         = Category::forDropdown($business_id, 'device');

                $warranties = [];
                if (request()->session()->get('business.common_settings.enable_product_warranty')) {
                    $warranties = Warranty::forDropdown($business_id);
                }

                $job_sheet = [];
                $parts     = [];
                if (isset($params['job_sheet_id'])) {
                    $job_sheet = JobSheet::where('business_id', $business_id)
                        ->find($params['job_sheet_id']);

                    $parts = $job_sheet->getPartsUsed();
                }

                return [
                    'view_path'            => 'repair::repair.partials.repair_pos',
                    'view_data'            => [
                        'repair_statuses' => $repair_statuses,
                        'default_status'  => $default_status,
                        'device_models'   => $device_models,
                        'brands'          => $brands,
                        'devices'         => $devices,
                        'warranties'      => $warranties,
                        'job_sheet'       => $job_sheet,
                        'repair_settings' => $repair_settings,
                        'parts'           => $parts
                    ],
                    'module_js_path'       => 'repair::layouts.partials.javascripts',
                    'module_css_path'      => 'repair::job_sheet.tagify_css',
                    'go_back_url'          => action('\Modules\Repair\Http\Controllers\RepairController@index'),
                    'transaction_sub_type' => 'repair'
                ];
            } else {
                return [];
            }
        }
    }

    /**
     * Function to add repair module taxonomies
     * @return array
     */
    public function addTaxonomies()
    {
        $module_util = new ModuleUtil();
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $module_util->hasThePermissionInSubscription($business_id, 'repair_module'))) {
            return [
                'device' => [],
            ];
        }

        return [
            'device' => [
                'taxonomy_label'       => __('repair::lang.device'),
                'heading'              => __('repair::lang.devices'),
                'sub_heading'          => __('repair::lang.manage_device'),
                'enable_taxonomy_code' => false,
                'enable_sub_taxonomy'  => false
            ]
        ];
    }

    /**
     * Returns view/js path with required extra data.
     * for product
     * @return array
     */
    public function get_product_screen_top_view()
    {
        $business_id       = session()->get('user.business_id');
        $module_util       = new ModuleUtil();
        $is_repair_enabled = (boolean) $module_util->hasThePermissionInSubscription($business_id, 'repair_module');

        if ($is_repair_enabled) {
            $device_models = DeviceModel::forDropdown($business_id);
            return [
                'view_path'      => 'repair::device_model.partials.repair_product_screen',
                'view_data'      => [
                    'device_models' => $device_models,
                ],
                'module_js_path' => 'repair::layouts.partials.javascripts'
            ];
        } else {
            return [];
        }
    }

    /**
     * Sets product fields from module
     * An input field with name "has_module_data" is required in the request to call this function
     * @param array $data
     * @return obj
     */
    public function after_product_saved($data)
    {
        $product = $data['product'];
        $request = $data['request'];

        $repair_module_fields = [
            'repair_model_id'
        ];

        foreach ($repair_module_fields as $field) {
            if (!empty($request->get($field))) {
                $product->$field = $request->get($field);
            }
        }

        $product->save();

        return $product;
    }

    public function get_filters_for_list_product_screen()
    {
        $business_id       = session()->get('user.business_id');
        $module_util       = new ModuleUtil();
        $is_repair_enabled = (boolean) $module_util->hasThePermissionInSubscription($business_id, 'repair_module');

        if ($is_repair_enabled) {
            $device_models = DeviceModel::forDropdown($business_id);
            return [
                'view_path' => 'repair::device_model.partials.list_product_filters',
                'view_data' => [
                    'device_models' => $device_models,
                ]
            ];
        } else {
            return [];
        }
    }
}
