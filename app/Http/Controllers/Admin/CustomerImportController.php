<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Customer\CustomerImport;
use App\Models\Accounting\PaymentTerm;
use App\Models\Common\Address;
use App\Models\Common\Country;
use App\Repositories\Customer\CustomerRepository;
use App\Services\Customer\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CustomerImportController extends Controller
{
    protected $customerService;

    protected $customerRepository;

    public function __construct(CustomerService $customerService, CustomerRepository $customerRepository)
    {
        $this->customerService = $customerService;
        $this->customerRepository = $customerRepository;
    }

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
            $absolutePath = storage_path('app/private/'.$path);

            // Get sheets
            $sheets = Excel::toArray(new CustomerImport($request->company->id), $absolutePath);
            $sheetNames = array_keys($sheets);

            // Get headers for the first sheet (default)
            $headers = [];
            if (! empty($sheets[0])) {
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

        $absolutePath = storage_path('app/private/'.$request->temp_path);
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
            $email = ! empty($mappedRow['email']) ? trim($mappedRow['email']) : null;
            $customerCode = ! empty($mappedRow['customer_code']) ? trim($mappedRow['customer_code']) : null;

            if (empty($displayName)) {
                $errors['display_name'] = 'Display Name is required';
            }

            // Validate Type
            $type = strtoupper($mappedRow['type'] ?? '');
            if (! empty($type) && ! in_array($type, ['INDIVIDUAL', 'COMPANY', 'B2B', 'B2C'])) {
                $errors['type'] = 'Invalid Type. Use INDIVIDUAL, COMPANY, B2B, or B2C';
            }

            // Check email uniqueness within file
            if (! empty($email)) {
                if (in_array(strtolower($email), $emailsInFile)) {
                    $errors['email'] = 'Duplicate email in file';
                } else {
                    $emailsInFile[] = strtolower($email);
                }
            }

            // 3. Determine Row Type and Validate based on repository/service logic
            $resolution = $this->customerService->resolveCustomerForImport($companyId, $mappedRow);
            $rowType = $resolution['type'];
            $errors = array_merge($errors, $resolution['errors']);

            $previewData[] = [
                'row_index' => $i,
                'raw_data' => $row,
                'mapped_data' => $mappedRow,
                'errors' => $errors,
                'row_type' => $rowType,
            ];

            if (! empty($errors)) {
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
        $newImported = 0;
        $updatedImported = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($data as $index => $row) {
                try {
                    $mapped = [];
                    foreach ($mapping as $dbField => $excelCol) {
                        $mapped[$dbField] = $row[$excelCol] ?? null;
                    }

                    if (empty($mapped['display_name'])) {
                        $failed++;

                        continue;
                    }

                    // Resolve Payment Term
                    if (empty($mapped['payment_term_id']) && ! empty($mapped['payment_term_name'])) {
                        $paymentTerm = PaymentTerm::where('name', 'LIKE', $mapped['payment_term_name'])->first();
                        $mapped['payment_term_id'] = $paymentTerm?->id;
                    }

                    // Resolve Type, isActive, discount etc are now handled in service
                    $resolutionBefore = $this->customerService->resolveCustomerForImport($companyId, $mapped);
                    if ($resolutionBefore['type'] === 'error') {
                        $failed++;

                        continue;
                    }

                    $this->customerService->importCustomer($companyId, $mapped);

                    $imported++;
                    if ($resolutionBefore['type'] === 'new') {
                        $newImported++;
                    } else {
                        $updatedImported++;
                    }
                } catch (\Exception $e) {
                    $failed++;
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
