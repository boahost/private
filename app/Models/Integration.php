<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    public $id;
    public $certificate;
    public $password;
    public $integration;
    public $payee_code;
    public $key_client_id;
    public $key_client_secret;

    public $pix_key;
    public $pix_split_plan;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];


    protected $table = 'integrations';
}
