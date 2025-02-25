<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function postOrder()
    {
        $request = validate([
            'customer_id'=> 'required|integer|exists:customers,id',
            'order_date'=> 'required|date',
            'total_price'=> 'required|numeric',
            'notes'=> 'nullable|string',
            'images'=> 'nullable|array',
            'images.*'=> 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = $request->user();
        $company_id = $user->company_id;

        $customer = Customer::findOrFail($request->customer_id);

        $customerCompany = $customer->company_id;

        if ($company_id != $customerCompany) {
            return response()->json(['message' => 'Customer does not belong to the company'], 400);
        }

        $images = [];

        $Order = new Order();
        $Order->customer_id = $request->customer_id;
        $Order->company_id = $companyId;
        $Order->order_date = $request->order_date;
        $Order->total_price = $request->total_price;
        $Order->notes = $request->notes ?? '';

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->storeAs('public/images', $imageName);
                $images[] = $imageName;
            }
        }

        $Order->save();

    }

    public function getOrder() {
        $user = $request->user();
        $company_id = $user->company_id;

        $order = Order::where('company_id', $company);

        return response()->json($order, 200);
    }
}
