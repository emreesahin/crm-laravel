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

    protected $casts = [
        'notes' => 'array',  // JSON olarak saklanan `notes` alanı diziye çevrilecek
        'images' => 'array', // Eğer images JSON olarak saklanıyorsa
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function customer_company()
    {
        return $this->hasOneThrough(Company::class, Customer::class, 'id', 'id', 'customer_id', 'company_id')
            ->select('companies.id');
    }
}
