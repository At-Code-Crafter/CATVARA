<?php

namespace App\Imports\Customer;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;


class CustomerImport implements WithMultipleSheets, SkipsUnknownSheets
{


    protected $companyId;

    public function __construct(string $companyId)
    {
        $this->companyId = $companyId;
    }

    public function sheets(): array
    {
        return [
            0 => new class implements ToCollection {
                public function collection(Collection $rows)
                {
                    // Logic is handled in controller for flexibility with mapping
                    return $rows;
                }
            },
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        // Skip unknown sheets
    }
}
