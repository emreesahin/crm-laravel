<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'company_id',
        'order_date',
        'total_price',
        'notes',
        'images',
        'order_id',
        'step_id',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
