<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    public static $id;
    public static $certificate;
    public static $password;
    public static $integration;
    public static $payee_code;
    public static $key_client_id;
    public static $key_client_secret;

    public static $pix_key;
    public static $pix_split_plan;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];


    protected $table = 'integrations';
}
