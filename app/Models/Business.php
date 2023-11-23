<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\BusinessLocation;
use App\Models\Transaction;
use App\Models\Contact;
use App\Models\Cte;
use App\Models\Mdfe;

class Business extends Model
{
    static $id;
    static $name;
    static $currency_id;
    static $start_date;
    static $tax_number_1;
    static $tax_label_1;
    static $tax_number_2;
    static $tax_label_2;
    static $default_sales_tax;
    static $default_profit_percent;
    static $owner_id;
    static $time_zone;
    static $fy_start_month;
    static $accounting_method;
    static $default_sales_discount;
    static $sell_price_tax;
    static $logo;
    static $sku_prefix;
    static $enable_product_expiry;
    static $expiry_type;
    static $on_product_expiry;
    static $stop_selling_before;
    static $enable_tooltip;
    static $purchase_in_diff_currency;
    static $purchase_currency_id;
    static $p_exchange_rate;
    static $transaction_edit_days;
    static $stock_expiry_alert_days;
    static $keyboard_shortcuts;
    static $pos_settings;
    static $manufacturing_settings;
    static $weighing_scale_setting;
    static $enable_brand;
    static $enable_category;
    static $enable_sub_category;
    static $enable_price_tax;
    static $enable_purchase_status;
    static $enable_lot_number;
    static $default_unit;
    static $enable_sub_units;
    static $enable_racks;
    static $enable_row;
    static $enable_position;
    static $enable_editing_product_from_purchase;
    static $sales_cmsn_agnt;
    static $item_addition_method;
    static $enable_inline_tax;
    static $currency_symbol_placement;
    static $enabled_modules;
    static $date_format;
    static $time_format;
    static $ref_no_prefixes;
    static $theme_color;
    static $created_by;
    static $repair_settings;
    static $enable_rp;
    static $rp_name;
    static $amount_for_unit_rp;
    static $min_order_total_for_rp;
    static $max_rp_per_order;
    static $redeem_amount_per_unit_rp;
    static $min_order_total_for_redeem;
    static $min_redeem_point;
    static $max_redeem_point;
    static $rp_expiry_period;
    static $rp_expiry_type;
    static $email_settings;
    static $sms_settings;
    static $custom_labels;
    static $common_settings;
    static $is_active;
    static $razao_social;
    static $cnpj;
    static $ie;
    static $senha_certificado;
    static $certificado;
    static $cidade_id;
    static $rua;
    static $numero;
    static $bairro;
    static $cep;
    static $telefone;
    static $ultimo_numero_nfe;
    static $ultimo_numero_nfce;
    static $ultimo_numero_cte;
    static $ultimo_numero_mdfe;
    static $inscricao_municipal;
    static $numero_serie_nfe;
    static $numero_serie_nfce;
    static $ambiente;
    static $regime;
    static $cst_csosn_padrao;
    static $cst_cofins_padrao;
    static $cst_pis_padrao;
    static $cst_ipi_padrao;
    static $perc_icms_padrao;
    static $perc_pis_padrao;
    static $perc_cofins_padrao;
    static $perc_ipi_padrao;
    static $ncm_padrao;
    static $cfop_saida_estadual_padrao;
    static $cfop_saida_inter_estadual_padrao;
    static $csc;
    static $csc_id;
    static $aut_xml;
    static $pix_split;
    static $created_at;
    static $updated_at;


    /**
     * The table associated with the model.
     *
     * @var string
     */

    public static function getConfig($business_id, $transaction)
    {
        $fistLocation = BusinessLocation::where('business_id', $business_id)->first();
        if ($transaction->location_id != null && $transaction->location_id != $fistLocation->id) {
            return BusinessLocation::where('id', $transaction->location_id)->first();
        } else {
            return Business::find($business_id);
        }
    }

    public static function getConfigCte($business_id, $cte)
    {
        $fistLocation = BusinessLocation::where('business_id', $business_id)->first();
        if ($cte->location_id != $fistLocation->id) {
            return BusinessLocation::where('id', $cte->location_id)->first();
        } else {
            return Business::find($business_id);
        }
    }

    public static function getConfigMdfe($business_id, $mdfe)
    {
        $fistLocation = BusinessLocation::where('business_id', $business_id)->first();
        if ($mdfe->location_id != $fistLocation->id) {
            return BusinessLocation::where('id', $mdfe->location_id)->first();
        } else {
            return Business::find($business_id);
        }
    }

    public static function getcUF($uf)
    {
        $estados = [
            'RO' => '11',
            'AC' => '12',
            'AM' => '13',
            'RR' => '14',
            'PA' => '15',
            'AP' => '16',
            'TO' => '17',
            'MA' => '21',
            'PI' => '22',
            'CE' => '23',
            'RN' => '24',
            'PB' => '25',
            'PE' => '26',
            'AL' => '27',
            'SE' => '28',
            'BA' => '29',
            'MG' => '31',
            'ES' => '32',
            'RJ' => '33',
            'SP' => '35',
            'PR' => '41',
            'SC' => '42',
            'RS' => '43',
            'MS' => '50',
            'MT' => '51',
            'GO' => '52',
            'DF' => '53'
        ];
        return $estados[$uf];
    }

    public static function getUF($cod)
    {
        $estados = [
            '11' => 'RO',
            '12' => 'AC',
            '13' => 'AM',
            '14' => 'RR',
            '15' => 'PA',
            '16' => 'AP',
            '17' => 'TO',
            '21' => 'MA',
            '22' => 'PI',
            '23' => 'CE',
            '24' => 'RN',
            '25' => 'PB',
            '26' => 'PE',
            '27' => 'AL',
            '28' => 'SE',
            '29' => 'BA',
            '31' => 'MG',
            '32' => 'ES',
            '33' => 'RJ',
            '35' => 'SP',
            '41' => 'PR',
            '42' => 'SC',
            '43' => 'RS',
            '50' => 'MS',
            '51' => 'MT',
            '52' => 'GO',
            '53' => 'DF'
        ];
        return $estados[$cod];
    }

    protected $table = 'business';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id', 'woocommerce_api_settings'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['woocommerce_api_settings'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ref_no_prefixes'        => 'array',
        'enabled_modules'        => 'array',
        'email_settings'         => 'array',
        'sms_settings'           => 'array',
        'common_settings'        => 'array',
        'weighing_scale_setting' => 'array'
    ];

    public function cidade()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    /**
     * Returns the date formats
     */
    public static function date_formats()
    {
        return [
            'd-m-Y' => 'dd-mm-yyyy',
            'm-d-Y' => 'mm-dd-yyyy',
            'd/m/Y' => 'dd/mm/yyyy',
            'm/d/Y' => 'mm/dd/yyyy'
        ];
    }

    /**
     * Get the owner details
     */
    public function owner()
    {
        return $this->hasOne(\App\Models\User::class, 'id', 'owner_id');
    }

    /**
     * Get the Business currency.
     */
    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * Get the Business currency.
     */
    public function locations()
    {
        return $this->hasMany(\App\Models\BusinessLocation::class);
    }

    /**
     * Get the Business printers.
     */
    public function printers()
    {
        return $this->hasMany(\App\Models\Printer::class);
    }

    /**
     * Get the Business subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany('\Modules\Superadmin\Entities\Subscription');
    }

    /**
     * Creates a new business based on the input provided.
     *
     * @return object
     */
    public static function create_business($details)
    {
        $business = Business::create($details);
        return $business;
    }

    /**
     * Updates a business based on the input provided.
     * @param int $business_id
     * @param array $details
     *
     * @return object
     */
    public static function update_business($business_id, $details)
    {
        if (!empty($details)) {
            Business::where('id', $business_id)
                ->update($details);
        }
    }

    public function getBusinessAddressAttribute()
    {
        $location = $this->locations->first();
        $address  = $location->city .
            ', ' . $location->state . '<br>' . $location->country . ', ' . $location->zip_code;

        return $address;
    }

    public function getRegistros()
    {
        return [
            'clientes'     => sizeof($this->clientes()),
            'fornecedores' => sizeof($this->fornecedores()),
            'vendas'       => sizeof($this->vendas()),
            'vendas_pdv'   => sizeof($this->vendasEmPdv()),
            'nfes'         => sizeof($this->nfes()),
            'nfces'        => sizeof($this->nfces()),
            'ctes'         => sizeof($this->ctes()),
            'mdfes'        => sizeof($this->mdfes()),
        ];
    }

    private function getNfes()
    {
    }

    public function clientes()
    {
        $contacts = Contact::where('business_id', $this->id)
            ->where('type', 'customer')
            ->get();

        return $contacts;
    }

    public function fornecedores()
    {
        $contacts = Contact::where('business_id', $this->id)
            ->where('type', 'supplier')
            ->get();

        return $contacts;
    }

    public function vendas()
    {
        $vendas = Transaction::where('business_id', $this->id)
            ->where('is_direct_sale', 1)
            ->where('type', 'sell')
            ->get();

        return $vendas;
    }

    public function vendasEmPdv()
    {
        $vendas = Transaction::where('business_id', $this->id)
            ->where('is_direct_sale', 0)
            ->where('type', 'sell')
            ->get();

        return $vendas;
    }

    public function nfes()
    {
        $vendas = Transaction::where('business_id', $this->id)
            ->where('is_direct_sale', 1)
            ->where('type', 'sell')
            ->where('numero_nfe', '>', 0)
            ->get();

        return $vendas;
    }

    public function nfces()
    {
        $vendas = Transaction::where('business_id', $this->id)
            ->where('is_direct_sale', 0)
            ->where('type', 'sell')
            ->where('numero_nfce', '>', 0)
            ->get();

        return $vendas;
    }

    public function ctes()
    {
        $ctes = Cte::where('business_id', $this->id)
            ->where('cte_numero', '>', 0)
            ->get();

        return $ctes;
    }

    public function mdfes()
    {
        $ctes = Mdfe::where('business_id', $this->id)
            ->where('mdfe_numero', '>', 0)
            ->get();

        return $ctes;
    }
}
