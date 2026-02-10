<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\CompanySettingsUpdateRequest;
use App\Models\Company\Company;
use App\Models\Company\CompanyDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanySettingsController extends Controller
{
    /**
     * Show the company settings form.
     */
    public function edit(Request $request)
    {
        $company = $request->company;
        $company->load('detail'); // Load detail for address, etc.

        return view('theme.adminlte.settings.company.edit', compact('company'));
    }

    /**
     * Update the company settings.
     */
    public function update(CompanySettingsUpdateRequest $request)
    {
        $company = $request->company;

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
                'logo' => $logoPath
            ]);
            
            // 2. Update Details
            // Ensure detail record exists (it should via seeder/observer, but safety first)
            CompanyDetail::updateOrCreate(
                ['company_id' => $company->id],
                [
                    'address' => $request->address,
                    'tax_number' => $request->tax_number,
                    'invoice_prefix' => $request->invoice_prefix,
                    'quote_prefix' => $request->quote_prefix,
                ]
            );
            
            DB::commit();
            return back()->with('success', 'Company settings updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }
}
