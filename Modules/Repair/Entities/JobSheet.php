<?php

namespace Modules\Repair\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\Variation;

class JobSheet extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'checklist' => 'array',
        'parts'     => 'array',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'repair_job_sheets';

    /**
     * Return the customer for the project.
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\Contact', 'contact_id');
    }

    /**
     * user added job sheet.
     */
    public function createdBy()
    {
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    /**
     * technecian for job sheet.
     */
    public function technician()
    {
        return $this->belongsTo('App\Models\User', 'service_staff');
    }

    /**
     * status of job sheet.
     */
    public function status()
    {
        return $this->belongsTo('Modules\Repair\Entities\RepairStatus', 'status_id');
    }

    /**
     * get device for job sheet
     */
    public function Device()
    {
        return $this->belongsTo('App\Models\Category', 'device_id');
    }

    /**
     * get Brand for job sheet
     */
    public function Brand()
    {
        return $this->belongsTo('App\Models\Brands', 'brand_id');
    }

    /**
     * get device model for job sheet
     */
    public function deviceModel()
    {
        return $this->belongsTo('Modules\Repair\Entities\DeviceModel', 'device_model_id');
    }

    /**
     * get business location for job sheet
     */
    public function businessLocation()
    {
        return $this->belongsTo('App\Models\BusinessLocation', 'location_id');
    }

    /**
     * Get the repair for the job sheet
     */
    public function invoices()
    {
        return $this->hasMany('App\Models\Transaction', 'repair_job_sheet_id');
    }

    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'model');
    }

    public function sheet_lines()
    {
        return $this->hasMany(JobSheetLines::class, 'job_sheets_id');
    }
}
