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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


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
                'images.*' => 'nullable|mimes:jpg,jpeg,png,gif',
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


            if ($request->has('images') && is_array($request->images)) {
                $imageUrls = [];


                foreach ($request->file('images') as $file) {
                    $path = $file->store('orders', 'public');
                    $url = asset(Storage::url($path));
                    $imageUrls[] = $url;
                }

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

    public function getOrder(Request $request, $id)
{
    try {
        $user = $request->user();

        if ($id !== null) {
            $order = Order::where('company_id', $user->company_id)->find($id);
            if ($order) {
                return response()->json([
                    'status' => true,
                    'message' => 'Sipariş bulundu',
                    'data' => $order
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                    'data' => ['error' => $e->getMessage()]
                ], 404);

            }
        } else {

            $orders = Order::where('company_id', $user->company_id)->get();

            $formattedOrders = [];

            foreach ($orders as $order) {
                $order->images = json_decode($order->images, true);
                $formattedOrders[] = $this->formatOrder($order);
            }

            return response()->json([
                'status' => true,
                'message' => 'Tüm Siparişler',
                'data' => $formattedOrders
            ], 200);
        }
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Bir hata oluştu: ' . $e->getMessage(),
            'data' => []
        ], 500);
    }
}

    private function formatOrder($order)
    {
        $orderData = $order->toArray();
        $orderData['customer'] = $order->customer->toArray();
        unset($orderData['customer_id']);
        unset($orderData['company_id']);

        if (isset($orderData['step_notes'])) {
            foreach ($orderData['step_notes'] as &$note) {
                $note['step_id'] = intval($note['step_id']);
            }
        }

        return $orderData;
    }

    public function getStepNotes(Request $request, $id)
    {
        try {
            $user = $request->user();


            $order = Order::where('company_id', $user->company_id)->find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                ], 404);
            }


            $stepId = $request->query('step');


            if (!$stepId) {
                return response()->json([
                    'status' => false,
                    'message' => 'Geçerli bir adım ID gerekli',
                ], 422);
            }


            $stepNotesData = collect($order->notes)->firstWhere('step_id', (int)$stepId);


            $stepNotes = $stepNotesData ? $stepNotesData['notes'] : [];

            return response()->json([
                'status' => true,
                'message' => 'Adım notları getirildi.',
                'data' => $stepNotes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Adım notları getirilirken bir hata oluştu.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    }



    public function updateOrder(Request $request, $id)
    {
        try {

            $order = Order::find($id);
            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı.',
                ]);
            }


            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|integer',
                'order_date' => 'required|date',
                'total_price' => 'required|numeric',
                'notes' => 'nullable|string',
                'step_id' => 'required|integer',
                'images' => 'nullable|array',
                'images.*' => 'nullable|file|mimes:jpg,jpeg,png,gif',
            ]);


            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Veriler geçersiz.',
                    'errors' => $validator->errors(),
                ]);
            }


            $order->customer_id = $request->input('customer_id');
            $order->order_date = $request->input('order_date');
            $order->total_price = $request->input('total_price');
            $order->notes = $request->input('notes');
            $order->step_id = $request->input('step_id');


            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('order_images');
                    $imagePaths[] = $imagePath;
                }
                $order->images = json_encode($imagePaths);


            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Sipariş başarıyla güncellendi.',
                'data' => $order
            ]);
        } } catch (\Exception $e) {

            Log::error('Order update error: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Sipariş güncellenirken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }


    // updateStepNotes
    public function updateStepNotes(Request $request, $orderId)
    {
        try {
            $user = $request->user();

            $request->validate([
                'note' => 'required|string',
                'step_id' => 'required|integer',
                'image' => 'nullable|mimes:jpg,jpeg,png,gif',
            ]);

            $order = Order::where('company_id', $user->company_id)->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                    'data' => []
                ], 404);
            }


            $stepNotes = json_decode($order->step_notes, true) ?: [];


            $step_id = $request->input('step_id');
            $note = $request->input('note');


            $newNote = [
                'user_id' => Auth::id(),
                'note' => $note,
                'created_at' => Carbon::now(),
                'image' => null,
            ];


            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('orders', 'public');
                $url = asset(Storage::url($path));
                $newNote['image'] = $url;
            }

            $existingStepIndex = array_search($step_id, array_column($stepNotes, 'step_id'));

            if ($existingStepIndex !== false) {

                $stepNotes[$existingStepIndex]['notes'][] = $newNote;
            } else {

                $stepNotes[] = [
                    'step_id' => $step_id,
                    'notes' => [$newNote],
                ];
            }

            $order->step_notes = json_encode($stepNotes);
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Adım notları güncellendi',
                'data' => $stepNotes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Adım notları güncellenirken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    // addStepNotes
    public function addStepNotes(Request $request, $orderId)
    {
        try {
            $user = $request->user();


            $validator = Validator::make($request->all(), [
                'note' => 'required|string',
                'step_id' => 'required|integer',
                'image' => 'nullable|mimes:jpg,jpeg,png,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Geçersiz giriş',
                    'errors' => $validator->errors(),
                ], 422);
            }


            $order = Order::where('company_id', $user->company_id)->find($orderId);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                ], 404);
            }

            $stepId = $request->input('step_id');


            $newNote = [
                'user_id' => $user->id,
                'note' => $request->input('note'),
                'created_at' => now(),
                'image' => null,
            ];


            if ($request->hasFile('image')) {
                try {
                    $file = $request->file('image');
                    $path = $file->store('orders', 'public');
                    $newNote['image'] = asset(Storage::url($path));
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Resim yüklenirken hata oluştu.',
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }


            $stepNotes = $order->notes ?? [];

            $existingStepIndex = array_search($stepId, array_column($stepNotes, 'step_id'));

            if ($existingStepIndex !== false) {
                $stepNotes[$existingStepIndex]['notes'][] = $newNote;
            } else {
                $stepNotes[] = [
                    'step_id' => $stepId,
                    'notes' => [$newNote],
                ];
            }


            $order->notes = $stepNotes;
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Adım notları başarıyla eklendi.',
                'data' => $stepNotes,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }

    }




    // getOrdersCount

    public function getOrdersCount(Request $request)
    {
        try {
            $user = $request->user();

            $orderCount = Order::whereHas('customer_company', function ($query) use ($user) {
                $query->where('companies.id', $user->company_id);
            })->where('step_id', '!=', 7)->count();

            $count = Order::whereHas('customer_company', function ($query) use ($user) {
                $query->where('companies.id', $user->company_id);
            })->where('step_id', 7)->count();


            return response()->json([
                'status' => true,
                'message' => 'Sipariş sayısı alındı',
                'data' => [
                    'total' => $orderCount,
                    'completed' => $count
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sipariş sayısı alınırken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    // filterOrders

    // public function filterOrders(Request $request)
    // {

    // }

    // deleteOrder

    public function deleteOrder(Request $request, $id)
    {
        try {
            $user = $request->user();
            $order = Order::where('company_id', $user->company_id)->find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                    'data' => []
                ], 404);
            }

            if ($order->image) {
                Storage::disk('public')->delete($order->image);
            }

            $order->delete();
            return response()->json([
                'status' => true,
                'message' => 'Sipariş silindi',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Sipariş silinirken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}

