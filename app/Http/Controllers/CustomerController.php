<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;

class CustomerController extends Controller
{
    public function createCustomer(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|numeric',
            'contact_email' => 'required|email|unique:customers,contact_email',
        ]);

        $user = $request->user();

        if (!$user->company_id) {
            return response()->json(['message' => 'User does not belong to a company'], 400);
        }

        $customer = new Customer();
        $customer->company_id = $user->company_id;
        $customer->company_name = $request->company_name;
        $customer->contact_name = $request->contact_name;
        $customer->contact_phone = $request->contact_phone;
        $customer->contact_email = $request->contact_email;
        $customer->created_by = $user->id;
        $customer->save();

        return response()->json([
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }
}
