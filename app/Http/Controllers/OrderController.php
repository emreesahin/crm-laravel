<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function postOrder(Request $request)
    {
        try {
            $request->validate([
                'customer_id' => 'required|integer|exists:customers,id',
                'order_date' => 'required|date',
                'total_price' => 'required|numeric',
                'notes' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'nullable|mimes:jpg,jpeg,png,gif',  // Dosya tipini kontrol et
                'order_id' => 'required|string|unique:orders,order_id',
                'step_id' => 'required|integer',
            ]);

            $user = $request->user();
            $companyId = $user->company_id;

            $customer = Customer::findOrFail($request->customer_id);
            $customerCompanyId = $customer->company_id;

            if ($companyId !== $customerCompanyId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bu müşteri bu şirkete ait değil.',
                    'data' => []
                ], 403);
            }

            $order = new Order();
            $order->customer_id = $request->customer_id;
            $order->company_id = $companyId;
            $order->order_date = Carbon::createFromFormat('d-m-Y', $request->order_date)->format('Y-m-d');
            $order->total_price = $request->total_price;
            $order->notes = $request->notes ?? '';
            $order->order_id = $request->order_id;
            $order->step_id = $request->step_id;

            // Dosyaların yüklendiğini kontrol et
            if ($request->has('images') && is_array($request->images)) {
                $imageUrls = [];

                // Her bir dosyayı yükle
                foreach ($request->file('images') as $file) {
                    $path = $file->store('orders', 'public'); // storage/app/public/orders
                    $url = asset(Storage::url($path)); // URL
                    $imageUrls[] = $url;
                }

                // images kolonunu JSON formatında veritabanına kaydedilecek
                $order->images = json_encode($imageUrls);
            }

            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Sipariş oluşturuldu',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sipariş oluşturulurken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }



}
