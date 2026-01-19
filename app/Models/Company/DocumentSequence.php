<?php

namespace App\Models\Company;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DocumentSequence extends Model
{
    protected $guarded = [];

    /**
     * Generate the next sequence code for a given company and document type.
     * 
     * @param int $companyId
     * @param string $documentType
     * @param string $prefix
     * @param int $padding
     * @return string
     */
    public static function getNextCode(int $companyId, string $documentType, string $prefix = '', int $padding = 6): string
    {
        return DB::transaction(function () use ($companyId, $documentType, $prefix, $padding) {
            
            // Lock the row for update to ensure atomic increment
            $sequence = static::lockForUpdate()->firstOrCreate(
                [
                    'company_id' => $companyId,
                    'document_type' => $documentType,
                ],
                [
                    'prefix' => $prefix,
                    'current_number' => 0,
                    // 'is_active' removed as it is not in the schema
                ]
            );

            // If prefix changed or wasn't set locally, we trust what's in DB or update it?
            // For now, we assume the DB record holds the source of truth for prefix if it exists.
            // But if it was just created, we used the passed prefix.

            $sequence->current_number++;
            $sequence->save();

            return $sequence->prefix . str_pad($sequence->current_number, $padding, '0', STR_PAD_LEFT) . $sequence->postfix;
        });
    }
}
