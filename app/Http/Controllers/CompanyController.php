<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function createCompany(Request $request)
    {
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
    }

    public function updateCompany(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string',
            'id' => 'required|integer|exists:companies,id',
            'contact_name' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_email' => 'required|email',
        ]);

        $company = Company::find($validated['id']);

        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

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
    }

    public function deleteCompany($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return response()->json(['message' => 'Company deleted successfully.']);
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
}
