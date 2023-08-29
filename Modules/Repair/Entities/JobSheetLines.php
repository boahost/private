<?php

namespace Modules\Repair\Entities;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Models\Variation;

class JobSheetLines extends Model
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
    protected $casts = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'repair_job_sheets_lines';

    protected $fillable = [
        'job_sheets_id',
        'product_id',
        'variation_id',
        'quantity',
        'unit_price',
        'unit_price_inc_tax',
        'item_tax',
        'tax_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
