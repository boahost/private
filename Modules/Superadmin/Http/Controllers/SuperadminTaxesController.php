<?php

namespace Modules\Superadmin\Http\Controllers;

use App\Helpers\PixHelper;
use App\Models\Business;
use App\Models\Integration;

class SuperadminTaxesController extends BaseController
{
    public function index($business_id)
    {
        $pix_split = Business::findOrFail((int) $business_id)->pix_split ?? 0;

        return view('superadmin::superadmin.modal_taxes')
            ->with(
                compact(
                    'pix_split',
                    'business_id',
                )
            );
    }

    public function store($business_id)
    {
        $input = request()->only('pix_split');

        $business            = Business::findOrFail((int) $business_id);
        $business->pix_split = $input['pix_split'];

        $pix = new PixHelper($business_id);

        $integration = Integration::where('business_id', $business_id)
            ->where('integration', 'efi')
            ->first();

        if (!empty($integration['pix_split_plan'])) {
            try {
                $split_plan = $pix->splitConfig($business->pix_split, $integration['pix_split_plan']);

                // \Log::debug("split_plan", $split_plan);

                $data['pix_split_plan'] = $split_plan['id'] ?? null;

                $integration->fill($data);
                $integration->save();
            } catch (\Exception $th) {
                return back()->with('status', [
                    'success' => false,
                    'msg'     => 'Não foi possível configurar o split de pagamento.',
                ]);
            }
        }

        $success = $business->save();

        return back()->with('status', [
            'success' => $success,
            'msg'     => !$success ? __("messages.something_went_wrong") : __("lang_v1.updated_success"),
        ]);
    }

}
