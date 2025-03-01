<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
        try{
            $validated = $request->validate([
                'company_name' => 'required|string',
                'contact_name' => 'required|string',
                'contact_phone' => 'required|string',
                'contact_email' => 'required|email',
            ]);

            $company = Company::create([
                'company_name' => $validated['company_name'],
                'contact_name' => $validated['contact_name'],
                'contact_phone' => $validated['contact_phone'],
                'contact_email' => $validated['contact_email'],
            ]);

            return response()->json([
                'message' => 'Company created successfully',
                'company' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Şirket oluşturulurken bir hata oluştu.',
                'error' => $e->getMessage(),
            ]);
        }

    }

    public function updateCompany(Request $request, $id)
{
    try {
        $company = Company::find($id);

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        // Validasyon
        $validated = $request->validate([
            'company_name' => 'required|string',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_email' => 'required|email',
        ]);

        // Güncelleme
        $company->update([
            'company_name' => $validated['company_name'],
            'contact_name' => $validated['contact_name'],
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'],
        ]);

        return response()->json([
            'message' => 'Company updated successfully',
            'company' => $company
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Şirket güncellenirken bir hata oluştu.',
            'error' => $e->getMessage(),
        ]);
    }
}


public function deleteCompany($id)
{
    try {
        $company = Company::find($id);


        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found.',
            ], 404);
        }


        $company->delete();

        return response()->json([
            'status' => true,
            'message' => 'Company deleted successfully.',
        ], 204);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Company could not be deleted.',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    public function getCompany($company_id)
    {
        $company = Company::find($company_id);

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        return response()->json(['company' => $company]);
    }

    public function getAllCompanies()
    {
        $companies = Company::all();

        return response()->json(['companies' => $companies]);
    }

    public function getCompanyUsers(Request $request)
    {

        try {
            $user = $request->user();

            $users = Company::where('company_id', $user->company_id)->where('role', '!=', 2)->get();

            return response()->json([
                'status' => true,
                'message' => 'Şirket personel bilgileri başarıyla getirildi.',
                'data' => [
                    'users' => $users,
                    'user' => $user
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Şirket personel bilgileri getirilirken bir hata oluştu.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
