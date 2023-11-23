<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    static $id;
    static $transaction_id;
    static $business_id;
    static $is_return;
    static $amount;
    static $method;
    static $transaction_no;
    static $card_transaction_number;
    static $card_number;
    static $card_type;
    static $card_holder_name;
    static $card_month;
    static $card_year;
    static $card_security;
    static $vencimento;
    static $cheque_number;
    static $bank_account_number;
    static $paid_on;
    static $created_by;
    static $payment_for;
    static $parent_id;
    static $note;
    static $document;
    static $payment_ref_no;
    static $account_id;
    static $created_at;
    static $updated_at;


    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Get the phone record associated with the user.
     */
    public function payment_account()
    {
        return $this->belongsTo(\App\Models\Account::class, 'account_id');
    }

    /**
     * Get the transaction related to this payment.
     */
    public function transaction()
    {
        return $this->belongsTo(\App\Models\Transaction::class, 'transaction_id');
    }

    /**
     * Get the user.
     */
    public function created_user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
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
}
