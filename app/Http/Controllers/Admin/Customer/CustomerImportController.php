<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Imports\Customer\CustomerImport;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Accounting\PaymentTerm;
use App\Models\Customer\Customer;
use App\Models\Company\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class CustomerImportController extends Controller
{
    public function index()
    {
        // Permission check can be added here
        $this->authorize('create', 'customers');

        return view('catvara.customer.import');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        $path = $request->file('file')->store('temp_imports');
        $absolutePath = storage_path('app/private/'.$path);

        // Get sheets
        $sheets = Excel::toArray(new CustomerImport($request->company->id), $absolutePath);
        $sheetNames = array_keys($sheets);

        // Get headers for the first sheet (default)
        $headers = [];
        if (! empty($sheets[0])) {
            $headers = array_keys($sheets[0][0] ?? []);
        }

        return response()->json([
            'success' => true,
            'temp_path' => $path,
            'sheets' => $sheetNames,
            'headers' => $headers,
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'temp_path' => 'required|string',
            'sheet_index' => 'required|integer',
        ]);

        $absolutePath = storage_path('app/private/'.$request->temp_path);
        
        // Use CustomerImport class to read file
        $data = Excel::toArray(new CustomerImport($request->company->id), $absolutePath)[$request->sheet_index] ?? [];

        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'Sheet is empty']);
        }

        $allHeaders = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($allHeaders);

        $previewData = [];
        $validationErrors = [];
        
        // Track emails to check for duplicates within the file
        $emailsInFile = [];

        foreach ($data as $i => $row) {
            $mappedRow = [];
            $errors = [];

            // 1. Resolve Mapped Data
            foreach ($mapping as $dbField => $excelCol) {
                $val = $row[$excelCol] ?? null;
                $mappedRow[$dbField] = $val;
            }

            // 2. Perform Validation
            $name = $mappedRow['display_name'] ?? null;
            $email = $mappedRow['email'] ?? null;

            if (empty($name)) {
                $errors['display_name'] = 'Customer Name is required';
            }
            
            if ($email) {
                 if (in_array($email, $emailsInFile)) {
                    $errors['email'] = 'Duplicate Email in file';
                } else {
                    $emailsInFile[] = $email;
                    // Check DB duplicates
                    if (Customer::where('company_id', $request->company->id)->where('email', $email)->exists()) {
                        $errors['email'] = 'Email already exists in database';
                    }
                }
            }


            $previewData[] = [
                'row_index' => $i,
                'raw_data' => $row, // Include all columns
                'mapped_data' => $mappedRow,
                'errors' => $errors,
            ];

            if (! empty($errors)) {
                $validationErrors[$i] = $errors;
            }
        }

        return response()->json([
            'success' => true,
            'preview' => $previewData,
            'mapping' => $mapping,
            'all_headers' => $allHeaders,
            'total_rows' => count($data),
            'error_count' => count($validationErrors),
        ]);
    }

    private function autoResolveMapping($headers)
    {
        $mapping = [];
        
        // Core fields mapping
        $coreMaps = [
            'display_name' => ['name', 'customer name', 'company name', 'customer', 'display name'],
            'email' => ['email', 'e-mail', 'mail'],
            'phone' => ['phone', 'mobile', 'telephone', 'contact number'],
            'tax_number' => ['tax number', 'vat number', 'tax id', 'vat'],
            'customer_code' => ['code', 'customer code', 'id'],
            'address_line_1' => ['address', 'address 1', 'street', 'billing address'],
            'address_line_2' => ['address 2', 'apartment', 'suite'],
            'city' => ['city', 'town'],
            'state' => ['state', 'province', 'region', 'county'],
            'zip_code' => ['zip', 'zip code', 'postal code', 'postcode'],
            'country' => ['country'],
            'payment_term' => ['payment term', 'terms'],
        ];

        foreach ($headers as $header) {
            $cleanHeader = strtolower(trim($header));

            foreach ($coreMaps as $field => $patterns) {
                if (in_array($cleanHeader, $patterns) && ! isset($mapping[$field])) {
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
        $absolutePath = storage_path('app/private/'.$request->temp_path);
        
        $data = Excel::toArray(new CustomerImport($companyId), $absolutePath)[$request->sheet_index];
        $headers = array_keys($data[0] ?? []);
        $mapping = $this->autoResolveMapping($headers);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                // If using Maatwebsite WithHeadingRow, index might be correct, 
                // but if using ToArray without WithHeadingRow, headers are row 0. 
                // ProductImportController seemingly handles raw array, so we manually map.
                
                // Assuming raw array where headers are processed separately in autoResolveMapping 
                // but ToArray returns data starting from row 0 as header?
                // Actually ProductImportController checks `$index === 0` to skip headers.
                if ($index === 0) {
                     continue; 
                }

                $mapped = [];
                foreach ($mapping as $dbField => $excelCol) {
                    $mapped[$dbField] = $row[$excelCol] ?? null;
                }

                if (empty($mapped['display_name'])) {
                    $failed++;
                    $errors[$index] = 'Customer Name is missing';
                    continue;
                }

                // Create or Update Customer
                // Identify by Email or Customer Code if present
                $customer = null;
                
                if (!empty($mapped['email'])) {
                    $customer = Customer::where('company_id', $companyId)->where('email', $mapped['email'])->first();
                }

                 if (!$customer && !empty($mapped['customer_code'])) {
                    $customer = Customer::where('company_id', $companyId)->where('customer_code', $mapped['customer_code'])->first();
                }
                
                // Resolve Payment Term
                $paymentTermId = null;
                if (!empty($mapped['payment_term'])) {
                    $term = PaymentTerm::where('company_id', $companyId)
                        ->where(function($q) use ($mapped) {
                            $q->where('name', 'like', $mapped['payment_term']);
                        })->first();
                    $paymentTermId = $term ? $term->id : null;
                }

                $customerData = [
                    'company_id' => $companyId,
                    'is_active' => true,
                    'type' => 'business', // Defaulting to business
                    'display_name' => $mapped['display_name'],
                    'email' => $mapped['email'] ?? null,
                    'phone' => $mapped['phone'] ?? null,
                    'tax_number' => $mapped['tax_number'] ?? null,
                    'payment_term_id' => $paymentTermId,
                ];

                if (!$customer) {
                    // New Customer
                    
                    // Generate code if missing
                    if (empty($mapped['customer_code'])) {
                        // Create a temporary customer instance to call the method if it's not static
                        // Or duplicate logic. The model has `generateCustomerCode`.
                        // Ideally we instantiate and call it.
                        $tempCustomer = new Customer();
                        $tempCustomer->company_id = $companyId;
                        $customerData['customer_code'] = $tempCustomer->generateCustomerCode();
                    } else {
                        // Check uniqueness
                         if (Customer::where('company_id', $companyId)->where('customer_code', $mapped['customer_code'])->exists()) {
                             // Fallback or error? For now, let's append a random string or error.
                             // Implementing error logic similar to ProductImport might be better?
                             // But let's assume we can use it or fallback to auto gen? 
                             // Let's error for now to be safe on integrity.
                              $failed++;
                              $errors[$index] = 'Customer Code already exists';
                              continue;
                         }
                         $customerData['customer_code'] = $mapped['customer_code'];
                    }
                    
                    $customerData['uuid'] = Str::uuid();
                    $customerData['legal_name'] = $mapped['display_name']; // Default legal name

                    $customer = Customer::create($customerData);
                    
                } else {
                    // Update Customer
                    $customer->update(array_filter($customerData, function($value) { return !is_null($value); }));
                }

                // Handle Address
                // We check if any address fields are present
                if (!empty($mapped['address_line_1']) || !empty($mapped['city']) || !empty($mapped['country'])) {
                    
                    $countryId = null;
                    if (!empty($mapped['country'])) {
                        $country = Country::where('name', 'like', trim($mapped['country']))
                           ->orWhere('iso2', strtoupper(trim($mapped['country'])))
                           ->orWhere('iso3', strtoupper(trim($mapped['country'])))
                           ->first();
                        $countryId = $country ? $country->id : null;
                    }
                    
                    $stateId = null;
                    if (!empty($mapped['state']) && $countryId) {
                         $state = State::where('country_id', $countryId)->where('name', 'like', trim($mapped['state']))->first();
                         $stateId = $state ? $state->id : null;
                    }

                    $addressData = [
                        'company_id' => $companyId,
                        'type' => 'billing', // Default
                        'name' => 'Main Address',
                        'address_line_1' => $mapped['address_line_1'] ?? '',
                        'address_line_2' => $mapped['address_line_2'] ?? null,
                        'city' => $mapped['city'] ?? null,
                        'zip_code' => $mapped['zip_code'] ?? null,
                        'country_id' => $countryId,
                        'state_id' => $stateId,
                        'phone' => $mapped['phone'] ?? null,
                        'email' => $mapped['email'] ?? null,
                    ];
                    
                    // Update or Create Address
                    // If customer has address, update it? Or add new?
                    // Logic: If existing customer has no address, create. If has address, update 'billing'?
                    
                    $address = $customer->address; // MorphOne
                    
                    if ($address) {
                        $address->update($addressData);
                    } else {
                        $customer->address()->create($addressData);
                    }
                }

                $imported++;
            }

            DB::commit();
            Storage::delete($request->temp_path);

            return response()->json([
                'success' => true,
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine(),
            ], 500);
        }
    }
}
