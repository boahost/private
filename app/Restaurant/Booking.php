<?php

namespace App\Restaurant;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Contact::class, 'contact_id');
    }

    public function table()
    {
        return $this->belongsTo(\App\Models\Restaurant\ResTable::class, 'table_id');
    }

    public function correspondent()
    {
        return $this->belongsTo(\App\Models\User::class, 'correspondent_id');
    }

    public function waiter()
    {
        return $this->belongsTo(\App\Models\User::class, 'waiter_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\BusinessLocation::class, 'location_id');
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class, 'business_id');
    }
}
