<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PixHelper;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PixEfiController extends Controller
{
    public function store(Request $request)
    {
        try {
            $business_id = request()->session()->get('user.business_id');

            $business = Business::findorfail($business_id);

            $input = $request->validate([
                'transactionId' => 'required|numeric',
            ]);

            $transaction = Transaction::findorfail($input['transactionId']);

            $pixHelper = new PixHelper($business_id);

            $pixHelper->setDescription("Pagamento realizado na $business->name");
            $pixHelper->setAmount((float) $transaction->final_total);

            $pixHelper = $pixHelper->create();

            return response()->json($pixHelper);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
