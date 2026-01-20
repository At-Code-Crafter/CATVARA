<?php

namespace App\Imports\Catalog;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    protected $companyId;

    protected $errors = [];

    protected $importedCount = 0;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection(Collection $rows)
    {
        // Actual import happens in the controller preview/process logic for better control
        // This class primarily provides the structure and basic mapping
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
