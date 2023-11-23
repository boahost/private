<?php

namespace App\Http\Controllers\Api;

use App\Helpers\PixHelper;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PaymentPlan;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use DB;
use Exception;
use Gerencianet\Exception\GerencianetException;
use Illuminate\Http\Request;

class PixEfiController extends Controller
{
    public function show(Request $request, $txid)
    {
        try {
            $business_id = session()->get('user.business_id');

            $pixHelper = new PixHelper($business_id);
            $pix       = $pixHelper->detailByTxID($txid);

            // $pix->status = 'CONCLUIDA';

            return response()->json($pix);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {
        try {
            $business_id = session()->get('user.business_id');

            $business = Business::findOrFail($business_id);

            $input = $request->validate([
                'amount' => 'required|numeric',
            ]);

            $pixHelper = new PixHelper($business_id);

            $pixHelper->setDescription("Pagamento realizado na $business->name");
            $pixHelper->setAmount((float) $input['amount']);

            $pix = $pixHelper->create();

            return response()->json($pix);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function storeByTransactionId(Request $request, $transaction_id)
    {
        try {
            $utils = new \App\Utils\TransactionUtil();

            $business_id    = session()->get('user.business_id');
            $transaction_id = (int) $transaction_id;

            $transactionPayment = TransactionPayment::query()
                ->where('transaction_id', $transaction_id)
                ->where('method', 'pix_efi')
                ->whereNotNull('transaction_no')
                ->first();


            /**
             * Se já existir uma transação com o método pix_efi e com o transaction_no preenchido
             * então não é necessário criar uma nova transação
             * apenas retornar os dados da transação já existente
             */
            if ($transactionPayment && $transactionPayment->transaction_no) {
                $pixHelper = new PixHelper($business_id);
                $pix       = $pixHelper->detailByTxID($transactionPayment->transaction_no);

                if ($pix->isPaid()) {
                    DB::beginTransaction();

                    $transactionPayment->update([
                        'paid_on' => date('Y-m-d H:i:s'),
                    ]);

                    $utils->updatePaymentStatus($transaction_id);

                    DB::commit();
                }

                return response()->json($pix);
            }


            /** @var \App\Models\Transaction $transaction */
            $transaction = Transaction::query()->findOrFail($transaction_id);

            // SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
            //     TP.transaction_id=transactions.id and TP.paid_on is not null) as total_paid

            $total_paid = TransactionPayment::query()
                ->where('transaction_id', $transaction_id)
                ->sum(DB::raw('IF(is_return = 1, -1 * amount, amount)'));

            /** @var \App\Models\Business $business */
            $business = Business::query()->findOrFail($business_id);

            $pixHelper = new PixHelper($business_id);

            $pixHelper->setDescription("Pagamento realizado na $business->name");
            $pixHelper->setAmount($transaction->final_total - $total_paid);

            $pix = $pixHelper->create();

            $payment_ref_no = $utils->generateReferenceNumber('sell_payment', $utils->setAndGetReferenceCount('sell_payment'));

            DB::beginTransaction();

            TransactionPayment::query()
                ->where('transaction_id', $transaction_id)
                ->where('method', 'pix_efi')
                ->delete();

            TransactionPayment::create([
                'amount'                  => $transaction->final_total,
                'method'                  => 'pix_efi',
                'transaction_no'          => $pix->txid,
                'note'                    => NULL,
                'card_number'             => NULL,
                'card_holder_name'        => NULL,
                'card_transaction_number' => NULL,
                'card_type'               => NULL,
                'card_month'              => NULL,
                'card_year'               => NULL,
                'card_security'           => NULL,
                'cheque_number'           => NULL,
                'bank_account_number'     => NULL,
                'paid_on'                 => NULL,
                'transaction_id'          => $transaction_id,
                'created_by'              => auth()->user()->id,
                'payment_for'             => $transaction->contact_id,
                'payment_ref_no'          => $payment_ref_no,
                'business_id'             => $business_id,
                'document'                => NULL,
            ]);

            $utils->updatePaymentStatus($transaction_id);

            DB::commit();

            return response()->json($pix);
        } catch (GerencianetException $e) {
            DB::rollBack();

            return response()->json([
                'code'             => $e->code,
                'error'            => $e->error,
                'errorDescription' => $e->errorDescription,
            ], 400);

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function list(Request $request)
    {
        try {
            $business_id = session()->get('user.business_id');

            $pixHelper = new PixHelper($business_id);

            $pix = $pixHelper->getPixList();

            return response()->json($pix);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listWebhook(Request $request, $business_id)
    {
        $pixHelper = new PixHelper($business_id);
        $api       = $pixHelper->getApi();

        $params = [
            "inicio" => date('Y-m-d\T00:00:00\Z', strtotime('-10 year')),
            "fim"    => date('Y-m-d\T23:59:59\Z', strtotime('+1 day')),
        ];

        $result = $api->pixListWebhook($params);

        return response()->json($result);
    }

    public function webhook(Request $request, $business_id)
    {
        logger('PIX - Webhook', $request->json()->all());

        try {
            $data = (object) $request->json()->all();

            if (($data->evento ?? '') == 'teste_webhook')
                return response('ok');

            if (empty($data->pix))
                throw new Exception('Notificação vazia', 400);

            $hook = reset($data->pix);

            $pixHelper = new PixHelper($business_id);
            $pix       = $pixHelper->detailByTxID($hook->txid);

            if (!$pix->isPaid()) {
                return response()->json(['msg' => 'A transação ainda não foi paga']);
            }

            $transactionPayment = TransactionPayment::query()
                ->where('transaction_no', $hook->txid)
                ->where('method', 'pix_efi')
                ->first();

            if (!$transactionPayment) {
                throw new Exception('Transação não encontrada');
            }

            $transaction = Transaction::query()->findOrFail($transactionPayment->transaction_id);

            if ($transaction->payment_status == 'paid') {
                throw new Exception('Transação já foi paga');
            }

            $utils = new \App\Utils\TransactionUtil();

            DB::beginTransaction();

            $transactionPayment->update([
                'paid_on' => date('Y-m-d H:i:s'),
            ]);

            $utils->updatePaymentStatus($transactionPayment->transaction_id);

            DB::commit();

            return response()->json(['msg' => 'ok']);
        } catch (Exception $e) {
            DB::rollBack();
            logger('PIX - Webhook - Erro', [
                $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }
}
