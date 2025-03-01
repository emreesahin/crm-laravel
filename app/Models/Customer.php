<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Company;
use App\Models\User;

class Customer extends Model
{

    use SoftDeletes;

   protected $fillable = [
    'company_id',
    'created_by',
    'company_name',
    'contact_name',
    'contact_phone',
    'contact_email',
   ];

    protected $dates = [
     'created_at',
     'updated_at',
     'deleted_at',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }
}
