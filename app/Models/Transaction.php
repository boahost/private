<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Devolucao;

class Transaction extends Model
{
    static $id;
    static $business_id;
    static $location_id;
    static $res_table_id;
    static $res_waiter_id;
    static $res_order_status;
    static $type;
    static $sub_type;
    static $status;
    static $is_quotation;
    static $payment_status;
    static $adjustment_type;
    static $contact_id;
    static $customer_group_id;
    static $invoice_no;
    static $ref_no;
    static $subscription_no;
    static $subscription_repeat_on;
    static $transaction_date;
    static $total_before_tax;
    static $tax_id;
    static $tax_amount;
    static $discount_type;
    static $discount_amount;
    static $rp_redeemed;
    static $rp_redeemed_amount;
    static $shipping_details;
    static $shipping_address;
    static $shipping_status;
    static $delivered_to;
    static $shipping_charges;
    static $additional_notes;
    static $staff_note;
    static $round_off_amount;
    static $final_total;
    static $expense_category_id;
    static $expense_for;
    static $commission_agent;
    static $document;
    static $is_direct_sale;
    static $is_suspend;
    static $exchange_rate;
    static $total_amount_recovered;
    static $transfer_parent_id;
    static $return_parent_id;
    static $opening_stock_product_id;
    static $created_by;
    static $mfg_parent_production_purchase_id;
    static $mfg_wasted_units;
    static $mfg_production_cost;
    static $mfg_production_cost_type;
    static $mfg_is_final;
    static $repair_completed_on;
    static $repair_warranty_id;
    static $repair_brand_id;
    static $repair_status_id;
    static $repair_model_id;
    static $repair_job_sheet_id;
    static $repair_defects;
    static $repair_serial_no;
    static $repair_checklist;
    static $repair_security_pwd;
    static $repair_security_pattern;
    static $repair_due_date;
    static $repair_device_id;
    static $repair_updates_notif;
    static $import_batch;
    static $import_time;
    static $types_of_service_id;
    static $packing_charge;
    static $packing_charge_type;
    static $service_custom_field_1;
    static $service_custom_field_2;
    static $service_custom_field_3;
    static $service_custom_field_4;
    static $is_created_from_api;
    static $rp_earned;
    static $order_addresses;
    static $is_recurring;
    static $recur_interval;
    static $recur_interval_type;
    static $recur_repetitions;
    static $recur_stopped_on;
    static $recur_parent_id;
    static $invoice_token;
    static $pay_term_number;
    static $pay_term_type;
    static $selling_price_group_id;
    static $created_at;
    static $updated_at;
    static $natureza_id;
    static $placa;
    static $uf;
    static $valor_frete;
    static $tipo;
    static $qtd_volumes;
    static $numeracao_volumes;
    static $especie;
    static $peso_liquido;
    static $peso_bruto;
    static $numero_nfe;
    static $numero_nfce;
    static $numero_nfe_entrada;
    static $chave;
    static $chave_entrada;
    static $sequencia_cce;
    static $cpf_nota;
    static $troco;
    static $valor_recebido;
    static $transportadora_id;
    static $estado;
    static $referencia_nfe;
    static $pedido_ecommerce_id;


    //Transaction types = ['purchase','sell','expense','stock_adjustment','sell_transfer','purchase_transfer','opening_stock','sell_return','opening_balance','purchase_return', 'payroll']

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    public static function bandeiras()
    {
        return [
            '01' => 'Visa',
            '02' => 'Mastercard',
            '03' => 'American Express',
            '04' => 'Sorocred',
            '05' => 'Diners Club',
            '06' => 'Elo',
            '07' => 'Hipercard',
            '08' => 'Aura',
            '09' => 'Cabal',
            '99' => 'Outros'
        ];
    }

    public function lastNFe($transaction)
    {

        $fistLocation = BusinessLocation::where('business_id', $this->business_id)->first();
        if ($transaction->location_id != $fistLocation->id) {
            $config = BusinessLocation::where('id', $transaction->location_id)->first();
        } else {
            $config = Business::find($this->business_id);
        }


        $transation = Transaction::
            where('numero_nfe', '>', 0)
            ->where('business_id', $this->business_id)
            ->where('location_id', $this->location_id)
            ->orderBy('numero_nfe', 'desc')
            ->first();

        $devolucao = Devolucao::
            where('numero_gerado', '>', 0)
            ->where('business_id', $this->business_id)
            ->where('location_id', $this->location_id)
            ->orderBy('numero_gerado', 'desc')
            ->first();

        $numero_saida     = $transation != null ? $transation->numero_nfe : 0;
        $numero_devolucao = $devolucao != null ? $devolucao->numero_gerado : 0;

        if ($numero_saida > $config->ultimo_numero_nfe && $numero_saida > $numero_devolucao) {
            return $numero_saida;
        } else if ($numero_devolucao > $config->ultimo_numero_nfe && $numero_devolucao > $numero_saida) {
            return $numero_devolucao;
        } else {
            return $config->ultimo_numero_nfe;
        }
    }

    public function lastNFCe($transaction)
    {

        $fistLocation = BusinessLocation::where('business_id', $this->business_id)->first();
        if ($transaction->location_id != $fistLocation->id) {
            $config = BusinessLocation::where('id', $transaction->location_id)->first();
        } else {
            $config = Business::find($this->business_id);
        }

        $transation = Transaction::
            where('numero_nfce', '>', 0)
            ->where('business_id', $this->business_id)
            ->where('location_id', $this->location_id)
            ->orderBy('numero_nfce', 'desc')
            ->first();

        // $config = Business::find($this->business_id);

        if (!$transation)
            return $config->ultimo_numero_nfce;
        if ($transation->numero_nfce > $config->ultimo_numero_nfce)
            return $transation->numero_nfce;
        else
            return $config->ultimo_numero_nfce;
    }

    public function purchase_lines()
    {
        return $this->hasMany(\App\Models\PurchaseLine::class);
    }

     public function payments()
    {
        return $this->hasMany(\App\Models\TransactionPayment::class, 'transaction_id');
    }

     public function sell_lines()
    {
        return $this->hasMany(\App\Models\TransactionSellLine::class)->orderBy('unit_price');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Models\Contact::class, 'contact_id');
    }

    public function natureza()
    {
        return $this->belongsTo(\App\Models\NaturezaOperacao::class, 'natureza_id');
    }

    public function transportadora()
    {
        return $this->belongsTo(\App\Models\Transportadora::class, 'transportadora_id');
    }

    public function payment_lines()
    {
        return $this->hasMany(\App\Models\TransactionPayment::class, 'transaction_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\BusinessLocation::class, 'location_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class, 'business_id');
    }

    public function tax()
    {
        return $this->belongsTo(\App\Models\TaxRate::class, 'tax_id');
    }

    public function stock_adjustment_lines()
    {
        return $this->hasMany(\App\Models\StockAdjustmentLine::class);
    }

    public function sales_person()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function return_parent()
    {
        return $this->hasOne(\App\Models\Transaction::class, 'return_parent_id');
    }

    public function table()
    {
        return $this->belongsTo(\App\Restaurant\ResTable::class, 'res_table_id');
    }

    public function service_staff()
    {
        return $this->belongsTo(\App\Models\User::class, 'res_waiter_id');
    }

    public function recurring_invoices()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'recur_parent_id');
    }

    public function recurring_parent()
    {
        return $this->hasOne(\App\Models\Transaction::class, 'id', 'recur_parent_id');
    }

    public function price_group()
    {
        return $this->belongsTo(\App\Models\SellingPriceGroup::class, 'selling_price_group_id');
    }

    public function types_of_service()
    {
        return $this->belongsTo(\App\Models\TypesOfService::class, 'types_of_service_id');
    }

    /**
     * Retrieves documents path if exists
     */
    public function getDocumentPathAttribute()
    {
        $path = !empty($this->document) ? asset('/uploads/documents/' . $this->document) : null;

        return $path;
    }

    /**
     * Removes timestamp from document name
     */
    public function getDocumentNameAttribute()
    {
        $document_name = !empty(explode("_", $this->document, 2)[1]) ? explode("_", $this->document, 2)[1] : $this->document;
        return $document_name;
    }

    public function subscription_invoices()
    {
        return $this->hasMany(\App\Models\Transaction::class, 'recur_parent_id');
    }

    /**
     * Shipping address custom method
     */
    public function shipping_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $shipping_address = [];

        if (!empty($addresses['shipping_address'])) {
            if (!empty($addresses['shipping_address']['shipping_name'])) {
                $shipping_address['name'] = $addresses['shipping_address']['shipping_name'];
            }
            if (!empty($addresses['shipping_address']['company'])) {
                $shipping_address['company'] = $addresses['shipping_address']['company'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_1'])) {
                $shipping_address['address_line_1'] = $addresses['shipping_address']['shipping_address_line_1'];
            }
            if (!empty($addresses['shipping_address']['shipping_address_line_2'])) {
                $shipping_address['address_line_2'] = $addresses['shipping_address']['shipping_address_line_2'];
            }
            if (!empty($addresses['shipping_address']['shipping_city'])) {
                $shipping_address['city'] = $addresses['shipping_address']['shipping_city'];
            }
            if (!empty($addresses['shipping_address']['shipping_state'])) {
                $shipping_address['state'] = $addresses['shipping_address']['shipping_state'];
            }
            if (!empty($addresses['shipping_address']['shipping_country'])) {
                $shipping_address['country'] = $addresses['shipping_address']['shipping_country'];
            }
            if (!empty($addresses['shipping_address']['shipping_zip_code'])) {
                $shipping_address['zipcode'] = $addresses['shipping_address']['shipping_zip_code'];
            }
        }

        if ($array) {
            return $shipping_address;
        } else {
            return implode(', ', $shipping_address);
        }
    }

    /**
     * billing address custom method
     */
    public function billing_address($array = false)
    {
        $addresses = !empty($this->order_addresses) ? json_decode($this->order_addresses, true) : [];

        $billing_address = [];

        if (!empty($addresses['billing_address'])) {
            if (!empty($addresses['billing_address']['billing_name'])) {
                $billing_address['name'] = $addresses['billing_address']['billing_name'];
            }
            if (!empty($addresses['billing_address']['company'])) {
                $billing_address['company'] = $addresses['billing_address']['company'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_1'])) {
                $billing_address['address_line_1'] = $addresses['billing_address']['billing_address_line_1'];
            }
            if (!empty($addresses['billing_address']['billing_address_line_2'])) {
                $billing_address['address_line_2'] = $addresses['billing_address']['billing_address_line_2'];
            }
            if (!empty($addresses['billing_address']['billing_city'])) {
                $billing_address['city'] = $addresses['billing_address']['billing_city'];
            }
            if (!empty($addresses['billing_address']['billing_state'])) {
                $billing_address['state'] = $addresses['billing_address']['billing_state'];
            }
            if (!empty($addresses['billing_address']['billing_country'])) {
                $billing_address['country'] = $addresses['billing_address']['billing_country'];
            }
            if (!empty($addresses['billing_address']['billing_zip_code'])) {
                $billing_address['zipcode'] = $addresses['billing_address']['billing_zip_code'];
            }
        }

        if ($array) {
            return $billing_address;
        } else {
            return implode(', ', $billing_address);
        }
    }

    public function cash_register_payments()
    {
        return $this->hasMany(\App\Models\CashRegisterTransaction::class);
    }

    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'model');
    }

    public function transaction_for()
    {
        return $this->belongsTo(\App\Models\User::class, 'expense_for');
    }

    /**
     * Returns the list of discount types.
     */
    public static function discountTypes()
    {
        return [
            'fixed'      => __('lang_v1.fixed'),
            'percentage' => __('lang_v1.percentage')
        ];
    }

    public static function transactionTypes()
    {
        return [
            'sell'            => __('sale.sale'),
            'purchase'        => __('lang_v1.purchase'),
            'sell_return'     => __('lang_v1.sell_return'),
            'purchase_return' => __('lang_v1.purchase_return'),
            'opening_balance' => __('lang_v1.opening_balance'),
            'payment'         => __('lang_v1.payment')
        ];
    }

    public static function getPaymentStatus($transaction)
    {
        $payment_status = $transaction->payment_status;

        if (in_array($payment_status, ['partial', 'due']) && !empty($transaction->pay_term_number) && !empty($transaction->pay_term_type)) {
            $transaction_date = \Carbon::parse($transaction->transaction_date);
            $due_date         = $transaction->pay_term_type == 'days' ? $transaction_date->addDays($transaction->pay_term_number) : $transaction_date->addMonths($transaction->pay_term_number);
            $now              = \Carbon::now();
            if ($now->gt($due_date)) {
                $payment_status = $payment_status == 'due' ? 'overdue' : 'partial-overdue';
            }
        }

        return $payment_status;
    }

    /**
     * Due date custom attribute
     */
    public function getDueDateAttribute()
    {
        $due_date = null;
        if (!empty($this->pay_term_type) && !empty($this->pay_term_number)) {
            $transaction_date = \Carbon::parse($this->transaction_date);
            $due_date         = $this->pay_term_type == 'days' ? $transaction_date->addDays($this->pay_term_number) : $transaction_date->addMonths($this->pay_term_number);
        }

        return $due_date;
    }

    public function getTipoPagamento()
    {
        $firstType = $this->payment_lines[0]->method;
        foreach ($this->payment_lines as $pay) {
            if ($pay->method != $firstType) {
                return "99";
            }
        }

        if ($firstType == 'cash')
            return '01';
        elseif ($firstType == 'credit')
            return '03';
        elseif ($firstType == 'debit')
            return '04';
        elseif ($firstType == 'cheque')
            return '02';
        elseif ($firstType == 'bank_transfer')
            return '16';
        elseif ($firstType == 'other')
            return '99';
        elseif ($firstType == 'boleto')
            return '15';
        elseif ($firstType == 'pix')
            return '17';
    }
}
