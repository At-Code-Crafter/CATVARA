<?php

namespace App\Http\Controllers\Admin\Company;

use App\Http\Controllers\Controller;
use App\Models\Company\Company;
use App\Models\Company\CompanyDetail;
use App\Models\Company\DocumentSequence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyProfileController extends Controller
{
    /**
     * Show the company profile edit form.
     */
    public function edit()
    {
        $company = Company::where('id', active_company()->id)->first();
        $company->load('detail');

        $sequences = DocumentSequence::where('company_id', $company->id)->get()->keyBy('document_type');

        return view('catvara.settings.company.profile', compact('company', 'sequences'));
    }

    /**
     * Update the company profile.
     */
    public function update(Request $request)
    {
        $company = Company::where('id', active_company()->id)->first();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'logo' => 'nullable|image|max:2048',
            'password_expiry_days' => 'nullable|integer|min:0',
            
            // Details
            'address' => 'nullable|string|max:500',
            'tax_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',

            // Sequences
            'sequences' => 'nullable|array',
            'sequences.*.prefix' => 'nullable|string|max:20',
            'sequences.*.postfix' => 'nullable|string|max:20',
        ]);
        
        try {
            DB::beginTransaction();
            
            // 1. Update Core
            $logoPath = $company->logo;
            if ($request->hasFile('logo')) {
                 if ($company->logo) Storage::disk('public')->delete($company->logo);
                 $logoPath = $request->file('logo')->store('companies', 'public');
            }
            
            $company->update([
                'name' => $request->name,
                'legal_name' => $request->legal_name,
                'website_url' => $request->website_url,
                'logo' => $logoPath,
                'password_expiry_days' => $request->password_expiry_days,
            ]);
            
            // 2. Update Details
            CompanyDetail::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'phone' => $request->phone,
                    'email' => $request->email,
                ]
            );

            // 3. Update Sequences
            if ($request->has('sequences')) {
                foreach ($request->sequences as $type => $data) {
                    DocumentSequence::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'document_type' => strtoupper($type),
                        ],
                        [
                            'prefix' => $data['prefix'] ?? '',
                            'postfix' => $data['postfix'] ?? null,
                        ]
                    );
                }
            }
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'message' => 'Company profile updated successfully.',
                ]);
            }

            return back()->with('success', 'Company profile updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json(['message' => 'Error updating profile: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }
}
