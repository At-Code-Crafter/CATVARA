<?php

namespace App\Exports\Customer;

use App\Models\Customer\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomerExport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping
{
    protected $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Customer::where('company_id', $this->companyId)
            ->with(['address.state', 'address.country', 'paymentTerm'])
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * @var Customer
     */
    public function map($customer): array
    {
        return [
            $customer->id,
            $customer->customer_code,
            $customer->display_name,
            $customer->legal_name,
            $customer->email,
            $customer->phone,
            $customer->tax_number,
            $customer->address->address_line_1 ?? '',
            $customer->address->address_line_2 ?? '',
            $customer->address->city ?? '',
            $customer->address->state->name ?? '',
            $customer->address->country->name ?? '',
            $customer->address->zip_code ?? '',
            (float) $customer->percentage_discount.'%',
            $customer->paymentTerm->name ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Customer ID',
            'Customer Code',
            'Name',
            'Legal Name',
            'Email',
            'Phone',
            'Tax Number',
            'Address Line 1',
            'Address Line 2',
            'City/Town',
            'State',
            'Country',
            'Postal Code',
            'Discount Percentage',
            'Payment Terms',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT, // Phone column
        ];
    }
}
