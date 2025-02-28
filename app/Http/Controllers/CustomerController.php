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

    //getCustomer

    public function getCustomer(Request $request)
    {

        try {
            $user = $request->user();

            if ($request->has('id')) {
                $id = $request->input('id');
                $customer = Customer::where('id', $id)
                    ->where('company_id', $user->company_id)
                    ->first();

                if (!$customer) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Müşteri bulunamadı.',
                        'data' => []
                    ], 404);
                }

                $customer->active_order_count = $customer->orders()->where('step_id', '!=', 7)->count();
                $customer->completed_order_count = $customer->orders()->where('step_id', 7)->count();

                $customer->active_orders = $this->getCustomerOrders($customer, 'active');
                $customer->completed_orders = $this->getCustomerOrders($customer, 'completed');

                $responseData = [
                    'id' => $customer->id,
                    'company_name' => $customer->company_name,
                    'contact_name' => $customer->contact_name,
                    'contact_phone' => $customer->contact_phone,
                    'contact_email' => $customer->contact_email,
                    'active_order_count' => $customer->active_order_count,
                    'completed_order_count' => $customer->completed_order_count,
                    'active_orders' => $customer->active_orders,
                    'completed_orders' => $customer->completed_orders,
                ];
                return response()->json([
                    'status' => true,
                    'message' => 'Müşteri bilgileri başarıyla getirildi.',
                    'data' => $responseData
                ], 200);
            } else {

                $customers = Customer::where('company_id', $user->company_id)->get();

                foreach ($customers as $customer) {
                    $customer->active_order_count = $customer->orders()->where('step_id', '!=', 7)->count();
                    $customer->completed_order_count = $customer->orders()->where('step_id', 7)->count();
                    $customer->active_orders = $this->getCustomerOrders($customer, 'active');
                    $customer->completed_orders = $this->getCustomerOrders($customer, 'completed');
                }

                $responseData = $customers->map(function ($customer) {
                    return [
                        'id' => $customer->id,
                        'company_name' => $customer->company_name,
                        'contact_name' => $customer->contact_name,
                        'contact_phone' => $customer->contact_phone,
                        'contact_email' => $customer->contact_email,
                        'active_order_count' => $customer->active_order_count,
                        'completed_order_count' => $customer->completed_order_count,
                        'active_orders' => $customer->active_orders,
                        'completed_orders' => $customer->completed_orders,
                    ];
                });
                return response()->json([
                    'status' => true,
                    'message' => 'Müşteriler başarıyla getirildi.',
                    'data' => $responseData
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Müşteri bilgileri getirilirken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    private function getCustomerOrders(Customer $customer, string $type)
    {
        if ($type === 'active') {
            return $customer->orders()->where('step_id', '!=', 7)->get();
        } elseif ($type === 'completed') {
            return $customer->orders()->where('step_id', 7)->get();
        }

        return [];
    }

    //updateCustomer

    public function updateCustomer(Request $request)
    {

        try {
            $id = $request->json('id');
            $request->validate([
                'company_id' => 'required|exists:companies,id',
                'contact_name' => 'required|string',
                'contact_phone' => 'required|string',
                'contact_email' => 'required|email|unique:customers,contact_email,' . $id . ',id',
            ]);
            $user = $request->user();
            $customer = Customer::where('company_id', $user->company_id)->find($id);

            if (!$customer) {
                return response()->json(['status' => false, 'message' => 'Müşteri bulunamadı', 'data' => []], 404);
            }

            $customer->company_id = $request->company_id;
            $customer->contact_name = $request->contact_name;
            $customer->contact_phone = $request->contact_phone;
            $customer->contact_email = $request->contact_email;

            $customer->save();

            return response()->json(['status' => true, 'message' => 'Müşteri güncellendi', 'data' => $customer]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Müşteri güncellenirken bir hata oluştu.', 'data' => $e->getMessage()], 500);
        }
    }

    //deleteCustomer



    public function deleteCustomer(Request $request, $id)
    {
        try {
            $user = $request->user();
            $customer = Customer::where('company_id', $user->company_id)->find($id);

            if (!$customer) {
                return response()->json(['status' => false, 'message' => 'Müşteri bulunamadı', 'data' => []], 404);
            }

            $customer->delete();

            return response()->json(['status' => true, 'message' => 'Müşteri silindi', 'data' => []]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Müşteri silinirken bir hata oluştu.', 'data' => $e->getMessage()], 500);
        }
    }
}
