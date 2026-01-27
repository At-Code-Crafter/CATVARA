<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Customer\CustomerImport;
use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class CustomerImportController extends Controller
{
    public function index()
    {
        $this->authorize('create', 'customers');

        $paymentTerms = PaymentTerm::where('is_active', true)->get(['id', 'name']);
        $countries = Country::active()->ordered()->get(['id', 'name', 'iso_code_2']);

        return view('catvara.customers.import', compact('paymentTerms', 'countries'));
    }

    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            $path = $request->file('file')->store('temp_imports');
            $absolutePath = storage_path('app/private/' . $path);

            // Get sheets
            $sheets = Excel::toArray(new CustomerImport($request->company->id), $absolutePath);
            $sheetNames = array_keys($sheets);

            // Get headers for the first sheet (default)
            $headers = [];
            if (!empty($sheets[0])) {
                $headers = array_keys($sheets[0][0] ?? []);
            }

            $paymentTerms = PaymentTerm::where('is_active', true)->get(['id', 'name']);
            $countries = Country::active()->ordered()->get(['id', 'name', 'iso_code_2']);

            return response()->json([
                'success' => true,
                'temp_path' => $path,
                'sheets' => $sheetNames,
                'headers' => $headers,
                'payment_terms' => $paymentTerms,
                'countries' => $countries,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function preview(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'sheet_index' => 'required|integer',
        ]);

        $absolutePath = storage_path('app/private/' . $request->temp_path);
        $sheetIndex = $request->sheet_index;

        $data = Excel::toArray(new CustomerImport($request->company->id), $absolutePath)[$sheetIndex] ?? [];

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'Sheet is empty']);
        }

        $allHeaders = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($allHeaders);

        $previewData = [];
        $validationErrors = [];
        $emailsInFile = [];
        $newCount = 0;
        $updateCount = 0;
        $companyId = $request->company->id;

        foreach ($data as $i => $row) {
            $mappedRow = [];
            $errors = [];

            // 1. Resolve Mapped Data
            foreach ($mapping as $dbField => $excelCol) {
                $val = $row[$excelCol] ?? null;
                $mappedRow[$dbField] = $val;
            }

            // 2. Perform Validation
            $displayName = $mappedRow['display_name'] ?? null;
            $email = !empty($mappedRow['email']) ? trim($mappedRow['email']) : null;
            $customerCode = !empty($mappedRow['customer_code']) ? trim($mappedRow['customer_code']) : null;

            if (empty($displayName)) {
                $errors['display_name'] = 'Display Name is required';
            }

            // Validate Type
            $type = strtoupper($mappedRow['type'] ?? '');
            if (!empty($type) && !in_array($type, ['INDIVIDUAL', 'COMPANY', 'B2B', 'B2C'])) {
                $errors['type'] = 'Invalid Type. Use INDIVIDUAL, COMPANY, B2B, or B2C';
            }

            // Check email uniqueness within file
            if (!empty($email)) {
                if (in_array(strtolower($email), $emailsInFile)) {
                    $errors['email'] = 'Duplicate email in file';
                } else {
                    $emailsInFile[] = strtolower($email);
                }
            }

            // 3. Determine Row Type and Validate based on new rules
            $rowType = 'new';
            $existingCustomer = null;

            // Find existing customer by customer_code
            if (!empty($customerCode)) {
                $existingCustomer = Customer::where('company_id', $companyId)
                    ->where('customer_code', $customerCode)
                    ->first();
            }

            if (empty($customerCode) && empty($email)) {
                // Case 1: customer_code == null && email == null → New Entry
                $rowType = 'new';
            } elseif (empty($customerCode) && !empty($email)) {
                // Case 2 & 3: customer_code == null && email != null
                $emailExists = Customer::where('company_id', $companyId)
                    ->where('email', $email)
                    ->exists();

                if ($emailExists) {
                    // Case 3: email exists → error
                    $errors['email'] = 'Email already exists for another customer';
                } else {
                    // Case 2: email doesn't exist → valid, create new
                    $rowType = 'new';
                }
            } elseif (!empty($customerCode) && empty($email)) {
                // Case 4: customer_code != null && email == null → update existing, don't touch email
                if ($existingCustomer) {
                    $rowType = 'update';
                } else {
                    $errors['customer_code'] = 'Customer code not found';
                }
            } elseif (!empty($customerCode) && !empty($email)) {
                // Case 5: customer_code != null && email != null
                if (!$existingCustomer) {
                    $errors['customer_code'] = 'Customer code not found';
                } else {
                    // Check if email belongs to another customer
                    $emailBelongsToAnother = Customer::where('company_id', $companyId)
                        ->where('email', $email)
                        ->where('id', '!=', $existingCustomer->id)
                        ->exists();

                    if ($emailBelongsToAnother) {
                        $errors['email'] = 'Email is linked with another customer';
                    } else {
                        $rowType = 'update';
                    }
                }
            }

            $previewData[] = [
                'row_index' => $i,
                'raw_data' => $row,
                'mapped_data' => $mappedRow,
                'errors' => $errors,
                'row_type' => $rowType,
            ];

            if (!empty($errors)) {
                $validationErrors[$i] = $errors;
            } else {
                if ($rowType === 'update') {
                    $updateCount++;
                } else {
                    $newCount++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'preview' => $previewData,
            'mapping' => $mapping,
            'all_headers' => $allHeaders,
            'total_rows' => count($data),
            'error_count' => count($validationErrors),
            'new_count' => $newCount,
            'update_count' => $updateCount,
        ]);
    }

    private function autoResolveMapping($headers)
    {
        $mapping = [];

        $coreMaps = [
            'customer_id' => ['customer_id', 'id'],
            'customer_code' => ['customer_code', 'code'],
            'type' => ['type', 'customer_type'],
            'display_name' => ['display_name', 'name', 'customer_name'],
            'email' => ['email', 'email_address'],
            'phone' => ['phone', 'phone_number', 'mobile', 'telephone'],
            'legal_name' => ['legal_name', 'company_name', 'business_name'],
            'tax_number' => ['tax_number', 'vat_number', 'tax_id', 'vat'],
            'notes' => ['notes', 'remarks', 'comments'],
            'is_active' => ['is_active', 'active', 'status'],
            'percentage_discount' => ['percentage_discount', 'discount', 'discount_percentage'],
            'payment_term_id' => ['payment_term_id'],
            'payment_term_name' => ['payment_term_name', 'payment_term', 'payment_terms'],
            // Address fields
            'address_line_1' => ['address_line_1', 'address', 'street', 'address_1'],
            'address_line_2' => ['address_line_2', 'address_2', 'street_2'],
            'city' => ['city', 'town', 'city/town', 'citytown'],
            'state_name' => ['state_name', 'state', 'province', 'region'],
            'country_name' => ['country_name', 'country'],
            'zip_code' => ['zip_code', 'postal_code', 'postcode', 'zip', 'postal code', 'postalcode'],
        ];

        foreach ($headers as $header) {
            $cleanHeader = strtolower(trim($header));

            foreach ($coreMaps as $field => $patterns) {
                if (in_array($cleanHeader, $patterns) && !isset($mapping[$field])) {
                    $mapping[$field] = $header;
                    continue 2;
                }
            }
        }

        return $mapping;
    }

    public function process(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'sheet_index' => 'required|integer',
        ]);

        $companyId = $request->company->id;
        $absolutePath = storage_path('app/private/' . $request->temp_path);
        $data = Excel::toArray(new CustomerImport($companyId), $absolutePath)[$request->sheet_index];

        $headers = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($headers);

        $imported = 0;
        $newImported = 0;
        $updatedImported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                $mapped = [];
                foreach ($mapping as $dbField => $excelCol) {
                    $mapped[$dbField] = $row[$excelCol] ?? null;
                }

                if (empty($mapped['display_name'])) {
                    $failed++;
                    continue;
                }

                $email = !empty($mapped['email']) ? trim($mapped['email']) : null;
                $customerCode = !empty($mapped['customer_code']) ? trim($mapped['customer_code']) : null;

                // Determine action based on customer_code and email
                $customer = null;
                $isNew = false;
                $shouldSkipEmail = false;

                // Find existing customer by customer_code
                if (!empty($customerCode)) {
                    $customer = Customer::where('company_id', $companyId)
                        ->where('customer_code', $customerCode)
                        ->first();
                }

                // Validate and determine action based on rules
                if (empty($customerCode) && empty($email)) {
                    // Case 1: customer_code == null && email == null → New Entry
                    $isNew = true;
                } elseif (empty($customerCode) && !empty($email)) {
                    // Case 2 & 3: customer_code == null && email != null
                    $emailExists = Customer::where('company_id', $companyId)
                        ->where('email', $email)
                        ->exists();

                    if ($emailExists) {
                        // Case 3: email exists → skip (error already shown in preview)
                        $failed++;
                        continue;
                    } else {
                        // Case 2: email doesn't exist → create new
                        $isNew = true;
                    }
                } elseif (!empty($customerCode) && empty($email)) {
                    // Case 4: customer_code != null && email == null → update existing, don't touch email
                    if ($customer) {
                        $isNew = false;
                        $shouldSkipEmail = true;
                    } else {
                        // Customer code not found → skip
                        $failed++;
                        continue;
                    }
                } elseif (!empty($customerCode) && !empty($email)) {
                    // Case 5: customer_code != null && email != null
                    if (!$customer) {
                        // Customer code not found → skip
                        $failed++;
                        continue;
                    } else {
                        // Check if email belongs to another customer
                        $emailBelongsToAnother = Customer::where('company_id', $companyId)
                            ->where('email', $email)
                            ->where('id', '!=', $customer->id)
                            ->exists();

                        if ($emailBelongsToAnother) {
                            // Email linked to another → skip
                            $failed++;
                            continue;
                        } else {
                            $isNew = false;
                        }
                    }
                }

                // Resolve Payment Term
                $paymentTermId = $mapped['payment_term_id'] ?? null;
                if (!$paymentTermId && !empty($mapped['payment_term_name'])) {
                    $paymentTerm = PaymentTerm::where('name', 'LIKE', $mapped['payment_term_name'])->first();
                    $paymentTermId = $paymentTerm?->id;
                }

                // Resolve Type
                $type = strtoupper($mapped['type'] ?? 'INDIVIDUAL');
                // Map B2B/B2C to COMPANY/INDIVIDUAL
                if ($type === 'B2B') {
                    $type = 'COMPANY';
                } elseif ($type === 'B2C') {
                    $type = 'INDIVIDUAL';
                }

                if (!in_array($type, ['INDIVIDUAL', 'COMPANY'])) {
                    $type = 'INDIVIDUAL';
                }

                // Resolve is_active
                $isActive = true;
                if (isset($mapped['is_active'])) {
                    $activeValue = strtolower((string) $mapped['is_active']);
                    $isActive = in_array($activeValue, ['1', 'true', 'yes', 'active']);
                }

                // Resolve discount
                $discount = 0;
                if (!empty($mapped['percentage_discount'])) {
                    $discountValue = str_replace(['%', ' '], '', $mapped['percentage_discount']);
                    $discount = is_numeric($discountValue) ? (float) $discountValue : 0;
                }

                if ($isNew) {
                    // Create Customer
                    $customer = Customer::create([
                        'uuid' => (string) Str::uuid(),
                        'company_id' => $companyId,
                        'type' => $type,
                        'display_name' => $mapped['display_name'],
                        'email' => $email,
                        'phone' => $mapped['phone'] ?? null,
                        'legal_name' => $mapped['legal_name'] ?? null,
                        'tax_number' => $mapped['tax_number'] ?? null,
                        'notes' => $mapped['notes'] ?? null,
                        'is_active' => $isActive,
                        'payment_term_id' => $paymentTermId,
                        'percentage_discount' => $discount,
                    ]);
                } else {
                    // Update Customer - conditionally update email
                    $updateData = [
                        'type' => $type,
                        'display_name' => $mapped['display_name'] ?? $customer->display_name,
                        'phone' => $mapped['phone'] ?? $customer->phone,
                        'legal_name' => $mapped['legal_name'] ?? $customer->legal_name,
                        'tax_number' => $mapped['tax_number'] ?? $customer->tax_number,
                        'notes' => $mapped['notes'] ?? $customer->notes,
                        'is_active' => $isActive,
                        'payment_term_id' => $paymentTermId ?? $customer->payment_term_id,
                        'percentage_discount' => $discount,
                    ];

                    // Only update email if not skipping (Case 4: don't touch email)
                    if (!$shouldSkipEmail && !empty($email)) {
                        $updateData['email'] = $email;
                    }

                    $customer->update($updateData);
                }

                // Handle Address
                $hasAddressData = !empty($mapped['address_line_1']) || !empty($mapped['city']) || !empty($mapped['zip_code']) || !empty($mapped['country_name']);

                if ($hasAddressData) {
                    // Resolve Country
                    $countryId = null;
                    if (!empty($mapped['country_name'])) {
                        $countryName = trim($mapped['country_name']);
                        $country = Country::where('name', 'LIKE', '%' . $countryName . '%')
                            ->orWhere('iso_code_2', '=', strtoupper($countryName))
                            ->orWhere('iso_code_3', '=', strtoupper($countryName))
                            ->first();

                        // Try exact match if LIKE didn't work
                        if (!$country) {
                            $country = Country::whereRaw('LOWER(name) = ?', [strtolower($countryName)])->first();
                        }
                        $countryId = $country?->id;
                    }

                    // Resolve State (optional - don't require it)
                    $stateId = null;
                    if (!empty($mapped['state_name'])) {
                        $stateName = trim($mapped['state_name']);
                        $stateQuery = State::where('name', 'LIKE', '%' . $stateName . '%');
                        if ($countryId) {
                            $stateQuery->where('country_id', $countryId);
                        }
                        $state = $stateQuery->first();
                        $stateId = $state?->id;
                    }

                    Address::updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'addressable_id' => $customer->id,
                            'addressable_type' => Customer::class,
                        ],
                        [
                            'address_line_1' => trim($mapped['address_line_1'] ?? ''),
                            'address_line_2' => !empty($mapped['address_line_2']) ? trim($mapped['address_line_2']) : null,
                            'city' => !empty($mapped['city']) ? trim($mapped['city']) : null,
                            'state_id' => $stateId,
                            'country_id' => $countryId,
                            'zip_code' => trim($mapped['zip_code'] ?? ''),
                        ]
                    );
                }

                $imported++;
                if ($isNew) {
                    $newImported++;
                } else {
                    $updatedImported++;
                }
            }

            DB::commit();
            Storage::delete($request->temp_path);

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'new' => $newImported,
                'updated' => $updatedImported,
                'failed' => $failed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
