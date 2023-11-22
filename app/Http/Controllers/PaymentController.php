<?php

namespace App\Http\Controllers;

use App\Helpers\PixHelper;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\PaymentPlan;
use Modules\Superadmin\Entities\Package;
use Modules\Superadmin\Entities\Subscription;

class PaymentController extends Controller
{
    public function index()
    {

        // $this->validaBusiness();
        $planos = Package::orderby('name', 'desc')
            ->where('is_active', true)
            ->where('is_visible', true)
            ->get();

        return view('payment.index', compact('planos'));
    }

    private function validaBusiness()
    {
        $business_id = request()->session()->get('user.business_id');
        $business    = Business::findorfail($business_id);

        if ($business->cep == "" || $business->rua == "" || $business->numero == "" || $business->bairro == "" || $business->cidade_id == null) {
            $output = [
                'success' => 0,
                'msg'     => "Informe o endereço completo."
            ];
            return redirect()->route('business.getBusinessSettings')->with('status', $output);
        }
    }
    public function paymentPix(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(getenv("MERCADOPAGO_ACCESS_TOKEN"));
        $payment = new \MercadoPago\Payment();

        $plano = Package::findOrFail($request->plano_id);

        $payment->transaction_amount = (float) $plano->price;
        $payment->description        = '';
        $payment->payment_method_id  = "pix";

        $doc = preg_replace('/[^0-9]/', '', $request->docNumber);

        $business_id = request()->session()->get('user.business_id');
        $business    = Business::findorfail($business_id);

        $firstBusiness = Business::first();

        $payment->payer = array(
            "email"          => $request->payerEmail,
            "first_name"     => $request->payerFirstName,
            "last_name"      => $request->payerLastName,
            "identification" => array(
                "type"   => $request->docType,
                "number" => $doc
            ),
            "address"        => array(
                "zip_code"      => $business->cep != "*" ? $business->cep : $firstBusiness->cep,
                "street_name"   => $business->rua != "*" ? $business->rua : $firstBusiness->rua,
                "street_number" => $business->numero != "*" ? $business->numero : $firstBusiness->numero,
                "neighborhood"  => $business->bairro != "*" ? $business->bairro : $firstBusiness->bairro,
                "city"          => $business->cidade_id != null ? $business->cidade->nome : $firstBusiness->cidade->nome,
                "federal_unit"  => $business->cidade_id != null ? $business->cidade->uf : $firstBusiness->cidade->uf
            )
        );
        $payment->save();

        if ($payment->transaction_details) {
            $data = [
                'payerFirstName'  => $request->payerFirstName,
                'payerLastName'   => $request->payerLastName,
                'payerEmail'      => $request->payerEmail,
                'docNumber'       => $doc,
                'valor'           => (float) $plano->price,
                'transacao_id'    => (string) $payment->id,
                'status'          => $payment->status,
                'forma_pagamento' => 'pix',
                'qr_code_base64'  => $payment->point_of_interaction->transaction_data->qr_code_base64,
                'qr_code'         => $payment->point_of_interaction->transaction_data->qr_code,
                'link_boleto'     => '',
                'numero_cartao'   => '',
                'package_id'      => $plano->id,
                'business_id'     => $business_id
            ];
            PaymentPlan::create($data);
            $output = [
                'success' => 1,
                'msg'     => "QRcode gerado escaneie ou copie o código para efetuar o pagamento."
            ];
            return redirect('/payment/finish/' . (string) $payment->id)
                ->with('status', $output);
        } else {
            $output = [
                'success' => 0,
                'msg'     => "Ocorreu um erro no pagamento."
            ];
            return redirect()->back()->with('status', $output);
        }

    }

    protected function setaPlano($paymentPlan)
    {
        $package = Package::findOrFail($paymentPlan->package_id);

        $business = Business::findorfail($paymentPlan->business_id);
        $dates    = $this->_get_package_dates($business->id, $package);

        $subscription = [
            'business_id'            => $business->id,
            'package_id'             => $package->id,
            'paid_via'               => 'mercado_pago',
            'payment_transaction_id' => $paymentPlan->transacao_id,
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

    public function finish($transaction_id)
    {
        $paymentPlan = PaymentPlan::where('transacao_id', $transaction_id)->first();
        return view('payment/finish', compact('paymentPlan'));
    }

    public function listPixEfi(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $pix = new PixHelper($business_id);

            $pix = $pix->getPixList();

            return response()->json($pix);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function consultaPixEfi(Request $request, $txid)
    {
        $business_id = request()->session()->get('user.business_id');

        try {
            $pix = new PixHelper($business_id);

            $pix = $pix->detailByTxID($txid);

            // $pix->status = 'CONCLUIDA';

            return response()->json($pix);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function listWebhookPixEfi()
    {
        $business_id = request()->session()->get('user.business_id');

        $pixHelper = new PixHelper($business_id);
        $api       = $pixHelper->getApi();

        $params = [
            "inicio" => date('Y-m-d\TH:i:s\Z', strtotime('-1 year')),
            "fim"    => date('Y-m-d\TH:i:s\Z'),
        ];

        $result = $api->pixListWebhook($params);

        return response()->json($result);
    }

    public function webhookPixEfi(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $data        = (object) $request->json()->all();

        if (($data->evento ?? null) == 'teste_webhook')
            return response()->json(['msg' => 'ok']);

        if (empty($data->pix))
            throw new \Exception('Notificação vazia', 400);

        $hook      = reset($data->pix);
        $pixHelper = new PixHelper($business_id);
        $pix       = $pixHelper->detailByTxID($hook->txid);

        $data_pagamento = $pix->status == 'CONCLUIDA' ? date('Y-m-d H:i:s', strtotime($hook->horario)) : null;

        $binds = [
            'PIX_SITUACAO'       => $pix->status,
            'PIX_TRANSACAO'      => $hook->txid,
            'PIX_DATA_PAGAMENTO' => $data_pagamento,
            'PIX_INTEGRACAO'     => $hook->endToEndId,
            'PIX_MENSAGEM'       => $pix->infoPagador ?? ''
        ];

        // \Log::debug('PIX - Webhook', $binds);

        $paymentPlan = PaymentPlan::where('transacao_id', $hook->txid)->first();

        if ($paymentPlan) {
            $paymentPlan->status = $pix->status;
            $paymentPlan->save();

            if ($pix->status == 'CONCLUIDA') {
                $this->setaPlano($paymentPlan);
            }
        }
    }

    public function paymentPixEfi(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        $business = Business::findorfail($business_id);
        $input    = $request->validate([
            'amount' => 'required|numeric',
        ]);

        try {
            $pixHelper = new PixHelper($business_id);

            // $pix->setDevedor($input['customer_name']);
            $pixHelper->setDescription("Pagamento realizado na $business->name");
            $pixHelper->setAmount((float) $input['amount']);

            $pixHelper = $pixHelper->create();

            // $paymentPlan = [
            //     'payerFirstName'  => $request->payerFirstName,
            //     'payerLastName'   => $request->payerLastName,
            //     'payerEmail'      => $request->payerEmail,
            //     'docNumber'       => $doc,
            //     'valor'           => (float) $plano->price,
            //     'transacao_id'    => (string) $payment->id,
            //     'status'          => $payment->status,
            //     'forma_pagamento' => 'pix',
            //     'qr_code_base64'  => $payment->point_of_interaction->transaction_data->qr_code_base64,
            //     'qr_code'         => $payment->point_of_interaction->transaction_data->qr_code,
            //     'link_boleto'     => '',
            //     'numero_cartao'   => '',
            //     'package_id'      => $plano->id,
            //     'business_id'     => $business_id
            // ];

            // PaymentPlan::create($paymentPlan);

            return response()->json($pixHelper);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function consultaPix($transacao_id)
    {
        \MercadoPago\SDK::setAccessToken(getenv("MERCADOPAGO_ACCESS_TOKEN"));
        $paymentPlan = PaymentPlan::where('transacao_id', $transacao_id)
            ->first();

        if ($paymentPlan) {
            $payStatus = \MercadoPago\Payment::find_by_id($paymentPlan->transacao_id);

            // $payStatus->status = "approved";

            if ($payStatus->status == "approved" && $paymentPlan->status != $payStatus->status) {
                $this->setaPlano($paymentPlan);
            }
            // $paymentPlan->status = $payStatus->status;

            $paymentPlan->save();

            return response()->json($payStatus->status);

        } else {
            return response()->json("erro", 401);
        }

    }

    public function paymentBoleto(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(getenv("MERCADOPAGO_ACCESS_TOKEN"));
        $payment = new \MercadoPago\Payment();

        $plano = Package::findOrFail($request->plano_id);

        $payment->transaction_amount = number_format($plano->price, 2);
        $payment->description        = '';
        $payment->payment_method_id  = "bolbradesco";

        $doc = preg_replace('/[^0-9]/', '', $request->docNumber);

        $business_id = request()->session()->get('user.business_id');
        $business    = Business::findorfail($business_id);

        $firstBusiness = Business::first();

        $payment->payer = array(
            "email"          => $request->payerEmail,
            "first_name"     => $request->payerFirstName,
            "last_name"      => $request->payerLastName,
            "identification" => array(
                "type"   => $request->docType,
                "number" => $doc
            ),
            "address"        => array(
                "zip_code"      => $business->cep != "*" ? $business->cep : $firstBusiness->cep,
                "street_name"   => $business->rua != "*" ? $business->rua : $firstBusiness->rua,
                "street_number" => $business->numero != "*" ? $business->numero : $firstBusiness->numero,
                "neighborhood"  => $business->bairro != "*" ? $business->bairro : $firstBusiness->bairro,
                "city"          => $business->cidade_id != null ? $business->cidade->nome : $firstBusiness->cidade->nome,
                "federal_unit"  => $business->cidade_id != null ? $business->cidade->uf : $firstBusiness->cidade->uf
            )
        );
        $payment->save();

        // print_r($payment);
        // die;

        if ($payment->transaction_details) {
            $data        = [
                'payerFirstName'  => $request->payerFirstName,
                'payerLastName'   => $request->payerLastName,
                'payerEmail'      => $request->payerEmail,
                'docNumber'       => $doc,
                'valor'           => (float) $plano->price,
                'transacao_id'    => (string) $payment->id,
                'status'          => $payment->status,
                'forma_pagamento' => 'boleto',
                'qr_code_base64'  => '',
                'qr_code'         => '',
                'link_boleto'     => $payment->transaction_details->external_resource_url,
                'numero_cartao'   => '',
                'package_id'      => $plano->id,
                'business_id'     => $business_id
            ];
            $paymentPlan = PaymentPlan::create($data);

            $this->setaPlano($paymentPlan);
            $output = [
                'success' => 1,
                'msg'     => "Boleto gerado com sucesso."
            ];
            return redirect('/payment/finish/' . (string) $payment->id)
                ->with('status', $output);
        } else {
            $output = [
                'success' => 0,
                'msg'     => "Ocorreu um erro no pagamento."
            ];
            return redirect()->back()->with('status', $output);
        }

    }

    public function consultaValorPlano($id)
    {
        $plano = Package::findOrFail($id);
        if ($plano) {
            return response()->json(number_format($plano->price, 2));
        }
    }

    public function paymentCartao(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(getenv("MERCADOPAGO_ACCESS_TOKEN"));
        $payment = new \MercadoPago\Payment();

        $business_id = request()->session()->get('user.business_id');
        $business    = Business::findorfail($business_id);

        $plano = Package::findOrFail($request->plano_id);

        $payment->transaction_amount = number_format($plano->price, 2);
        $payment->token              = $request->token;
        $payment->description        = 'Pagamento de plano';
        $payment->payment_method_id  = $request->paymentMethodId;
        $payment->installments       = (int) $request->installments;
        $payment->token              = $request->token;
        $doc                         = preg_replace('/[^0-9]/', '', $request->docNumber);

        $payer = new \MercadoPago\Payer();

        $payer->email          = $request->payerEmail;
        $payer->identification = array(
            "type"   => $request->docType,
            "number" => $request->docNumber
        );
        $payment->payer        = $payer;

        $payment->save();

        if ($payment->error) {
            $output = [
                'success' => 0,
                'msg'     => $payment->error
            ];
            return redirect()->back()->with('status', $output);
        } else {

            $data = [
                'payerFirstName'  => $request->cardholderName,
                'payerLastName'   => '',
                'payerEmail'      => $request->payerEmail,
                'docNumber'       => $doc,
                'valor'           => (float) $plano->price,
                'transacao_id'    => (string) $payment->id,
                'status'          => $payment->status,
                'forma_pagamento' => 'cartao',
                'qr_code_base64'  => '',
                'qr_code'         => '',
                'link_boleto'     => '',
                'numero_cartao'   => $request->cardNumber,
                'package_id'      => $plano->id,
                'business_id'     => $business_id
            ];
            PaymentPlan::create($data);
            $output = [
                'success' => 1,
                'msg'     => "Boleto gerado com sucesso."
            ];
            return redirect('/payment/finish/' . (string) $payment->id)
                ->with('status', $output);
        }
    }
}
