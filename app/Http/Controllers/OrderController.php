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

    public function getOrder(Request $request)
    {
        try {
            $user = $request->user();
            $id = $request->query('id');

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
                        'data' => []
                    ], 404);
                }
            } else {
                $orders = Order::where('company_id', $user->company_id)->get();

                foreach ($orders as $order) {
                    $order->images = json_decode($order->images);
                    $formattedOrders[] = $this->formatOrder($order);
                }
                return response()->json(['status' => true, 'message' => 'Tüm Siparişler', 'data' => $formattedOrders, 200]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage(), 'data' => []]);
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
            $step_id = $request->input('step_id');

            $order = Order::where('company_id', $user->company_id)->find($id);

            if (!$order) {
                response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                    'data' => []
                ], 404);
            }

            if (!$order->step_id) {
                response()->json([
                    'status' => false,
                    'message' => 'Siparişin adımı bulunamadı',
                    'data' => []
                ], 404);
            }

            $filtered_step_notes = array_filter($order->step_notes, function ($note) use ($step_id) {
                return isset($note['step_id']) && $note['step_id'] == $step_id;
            });

            $formattedNotes = [];
            foreach ($filtered_step_notes as $note) {
                foreach ($note['notes'] as $data) {
                    if ($user) {
                        $formattedNotes[] = [
                            'note' => $data['note'],
                            'created_at' => $data['created_at'],
                            'image' => $data['image'],
                            'employee' => $user,
                        ];
                    }
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Adım notları bulundu',
                'data' => $formattedNotes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu: ' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    public function updateOrder(Request $request, $id)
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
            $order = Order::where('company_id', $user->company_id)->find($id);

            if (!$order) {
                return response()->json([
                    'status' => false,
                    'message' => 'Sipariş bulunamadı',
                    'data' => []
                ], 404);
            }
            $companyId = $user->company_id;

            $order->customer_id = $request->customer_id;
            $order->company_id = $companyId;

            if ($request->has('notes')) {
                $order->notes = $request->notes;
            }

            if ($request->has('image')) {
                $file = $request->file('image');
                $path = $file->store('orders', 'public');
                $url = asset(Storage::url($path));
                $order->images = $url;
            }

            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Sipariş güncellendi',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
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

            $urlStep = $request->query('step');
            $step_id = $request->input('step_id');

            if ($request->filled('step_id') && $step_id != $urlStep) {
                $order->step_id = min($step_id, 7);
            } elseif ($step_id == $urlStep) {
                $order->step_id = min($order->step_id + 1, 7);
            }

            // New note create

            $newNote = [
                'user_id' => Auth::id(),
                'note' => $request->input('note'),
                'created_at' => Carbon::now(),
                'image' => null,
            ];

            // Image process part

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('orders', 'public');
                $url = asset(Storage::url($path));
                $newNote['image'] = $url;
            }

            // Step ID control
            $step_id = $request->filled('step_id') ? $request->input('step_id') : $urlStep;

            // Step notes control
            $stepNotes = $order->step_notes ?: [];

            // If step notes exist for this step, update it
            $existingStepIndex = array_search($step_id, array_column($stepNotes, 'step_id'));

            if ($existingStepIndex !== false) {
                // Step exists, add new note
                $stepNotes[$existingStepIndex]['notes'][] = $newNote;
            } else {
                // Step does not exist, create new step
                $stepNotes[] = [
                    'step_id' => $step_id,
                    'notes' => [$newNote],
                ];
            }

            $order->step_notes = $stepNotes;
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

            $request->validate([
                'note' => 'required|string',
                'step_id' => 'nullable|integer',
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

            // Find the step parameter from url

            $urlStep = $request->query('step');



            // Create a new note

            $newNote = ([
                'user_id' => Auth::id(),
                'note' => $request->input('note'),
                'created_at' => Carbon::now(),
                'image' => null,
            ]);

            // Image process part

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('orders', 'public');
                $url = asset(Storage::url($path));
                $newNote['image'] = $url;
            }

            $stepId = $urlStep;

            $stepNotes = $order->step_notes ?: [];

            $existingStepIndex = array_search($stepId, array_column($stepNotes, 'step_id'));

            if ($existingStepIndex !== false) {
                $stepNotes[$existingStepIndex]['notes'][] = $newNote;
            } else {
                $stepNotes[] = [
                    'step_id' => $stepId,
                    'notes' => [$newNote],
                ];
            }

            $order->step_notes = $stepNotes;
            $order->save();

            return response()->json([
                'status' => true,
                'message' => 'Adım notları eklendi',
                'data' => $stepNotes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Adım notları eklenirken bir hata oluştu.',
                'data' => $e->getMessage()
            ], 500);
        }
    }


    // getOrdersCount

    public function getOrdersCount(Request $request)
    {
        try {
            $user = $request->user();

            $orderCount = Order::whereHas('customer_company', function ($query) use ($user) {
                $query->where('id', $user->company_id);
            })->where('step_id', '!=', 7)->count();

            $count = Order::whereHas('customer_company', function ($query) use ($user) {
                $query->where('id', $user->company_id);
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
