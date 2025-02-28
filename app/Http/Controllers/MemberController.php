<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class MemberController extends Controller
{
    use SoftDeletes;

    public function getMembers(Request $request)
    {
        try {
            if (!$request->bearerToken()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kimlik doğrulama başarısız',
                    'data'  => []
                ], 401);
            }

            $member = $request->user();
            $id = $request->id;

            if ($id) {
                $member = User::where('company_id', $member->company_id)->find($id);
                if (!$member) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Kullanıcı bulunamadı',
                        'data'  => []
                    ], 404);
                }

                $memberData = $member->toArray();
                $memberData['company_id'] = $member->company_id;
                $memberData['last_activity'] = $member->last_activity;

                return response()->json([
                    'status' => true,
                    'message' => 'Kullanıcı bilgileri getirildi',
                    'data' => $memberData
                ], 200);
            } else {
                $members = User::where('company_id', $member->company_id)->where('role', '!=', '2')->get();
                $memberData = [];
                foreach ($members as $member) {
                    $memberData[] = [
                        'id' => $member->id,
                        'name' => $member->name,
                        'surname' => $member->surname,
                        'role' => $member->role,
                        'username' => $member->username,
                        'email' => $member->email,
                        'company_id' => $member->company_id,
                        'last_activity' => $member->last_activity,
                        'profile_photo' => $member->profile_photo,
                        'national_id' => $member->national_id,
                        'created_at' => $member->created_at,
                    ];
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Tüm Kullanıcılar getirildi',
                    'data' => $memberData
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // getCurrentMember

    public function getCurrentMember(Request $request)
    {
        try {
            $member = $request->user();
            $company = Company::find($member->company_id);

            $memberData = [
                'id' => $member->id,
                'name' => $member->name,
                'surname' => $member->surname,
                'role' => $member->role,
                'username' => $member->username,
                'email' => $member->email,
                'company_id' => $member->company_id,
                'last_activity' => $member->last_activity,
                'profile_photo' => $member->profile_photo,
                'national_id' => $member->national_id,
                'created_at' => $member->created_at,
                'company' => [
                    'id' => $company->id,
                    'company_name' => $company->company_name,
                    'contact_name' => $company->contact_name,
                    'contact_phone' => $company->contact_phone,
                    'contact_email' => $company->contact_email,
                    'created_at' => $company->created_at,
                ]
            ];

            return response()->json([
                'status' => true,
                'message' => 'Kullanıcı bilgileri getirildi',
                'data' => $memberData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // createAdmin

    public function createAdmin(Request $request)
    {
        try {
            $member = $request->user();
            $request->validate([
                'name' => 'required',
                'surname' => 'required',
                'username' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'national_id' => 'required',
                'company_id' => 'required',
                'token' => 'required|string'
            ]);

            $token = $request->input('token');
            $staticToken = 'staticToken';

            if ($token !== $staticToken) {
                return response()->json([
                    'status' => false,
                    'message' => 'Token doğrulama başarısız',
                    'data' => []
                ], 401);
            }

            $member = new User();
            $member->name = $request->name;
            $member->surname = $request->surname;
            $member->username = $request->username;
            $member->email = $request->email;
            $member->password = Hash::make($request->password);
            $member->profile_photo = $request->profile_photo;
            $member->national_id = $request->national_id;
            $member->company_id = $request->company_id;
            $member->role = 2;

            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $path = $file->store('profile_photos', 'public');
                $url = asset(Storage::url($path));
                $member->profile_photo = $url;
            }
            $member->save();

            return response()->json([
                'status' => true,
                'message' => 'Admin başarıyla oluşturuldu',
                'data' => $member
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // createMember

    public function createMember(Request $request)
    {
        try {
            $member = $request->user();
            $request->validate([
                'name' => 'required|string',
                'surname' => 'required|string',
                'username' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'national_id' => 'required',
                'company_id' => 'required',
            ]);

            $member = new User();
            $member->name = $request->name;
            $member->surname = $request->surname;
            $member->username = $request->username;
            $member->email = $request->email;
            $member->password = Hash::make($request->password);
            $member->profile_photo = $request->profile_photo;
            $member->national_id = $request->national_id;
            $member->company_id = $request->company_id;
            $member->role = 1;

            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $path = $file->store('profil_photos', 'public');
                $url = asset(Storage::url($path));
                $member->profile_photo = $url;
            }

            $member->save();

            return response()->json([
                'status' => true,
                'message' => 'Kullanıcı başarıyla oluşturuldu',
                'data' => $member
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // updateMember

    public function updateMember(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string',
                'surname' => 'required|string',
                'username' => 'required',
                'email' => 'required|email',
                'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'national_id' => 'required',
                'company_id' => 'required',
            ]);

            $member = User::find($validated['id']);
            if (!$member) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kullanıcı bulunamadı',
                    'data' => []
                ], 404);
            }

            $member->update([
                'name' => $validated['name'],
                'surname' => $validated['surname'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'profile_photo' => $validated['profile_photo'],
                'national_id' => $validated['national_id'],
                'company_id' => $validated['company_id'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Kullanıcı başarıyla güncellendi',
                'data' => $member
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // deleteMember

    public function deleteMember(Request $request, $id)
    {
        try {
            $member = $request->user();
            $member = User::where('company_id', $member->company_id)->find($id);
            if (!$member) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kullanıcı bulunamadı',
                    'data' => []
                ], 404);
            }

            if ($member->image) {
                $image = explode('/', $member->image);
                Storage::disk('public')->delete('profile_photos/' . end($image));
            }

            $member->delete();

            return response()->json([
                'status' => true,
                'message' => 'Kullanıcı başarıyla silindi',
                'data' => []
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }

    // getMemberSummary

    public function getMemberSummary(Request $request)
    {
        try {
            $member = $request->user();

            $activeOrderCount = Order::where('company_id', $member->company_id)->where('status', '<>', '7')
                ->where('company_id', $member->company_id)
                ->count();

            $completedOrderCount = Order::where('company_id', $member->company_id)->where('status', '7')
                ->where('company_id', $member->company_id)
                ->count();

            $memberCount = null;

            $memberCount = User::where('company_id', $member->company_id)->where('role', '1')
                ->where('company_id', $member->company_id)
                ->count();



            $data = [
                $id = $member->company_id,
                'company_id' => Company::where('id', $member->company_id)->value('company_name'),
                'active_order_count' => $activeOrderCount,
                'completed_order_count' => $completedOrderCount,
                'member_count' => $memberCount,
            ];

            if ($memberCount !== null) {
                $data['employee_count'] = $memberCount;
            }
            return response()->json([
                'status' => true,
                'message' => 'Kullanıcı özeti getirildi',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Bir hata oluştu',
                'data' => []
            ], 500);
        }
    }
}
