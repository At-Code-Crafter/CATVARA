<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\DB;
use App\Models\Company\DocumentSequence;
use Illuminate\Support\Str;

class DocumentNumberService
{
    /**
     * Generate the next document number with fallback support.
     * 
     * Fallback Strategy:
     * 1. Specific Channel + Specific Year
     * 2. Specific Channel + Global Year (null)
     * 3. Global Channel (null) + Specific Year
     * 4. Global Channel (null) + Global Year (null)
     * 5. If none exist -> Auto-create Global Channel/Year with inferred prefix
     */
    public function generate(
        int $companyId,
        string $documentType,
        ?string $channel = null,
        ?int $year = null,
        ?string $fallbackPrefix = null,
        int $padding = 6
    ): string {
        return DB::transaction(function () use (
            $companyId,
            $documentType,
            $channel,
            $year,
            $fallbackPrefix,
            $padding
        ) {
            // Define search priority
            $checks = [
                ['channel' => $channel, 'year' => $year],              // 1. Specific
                ['channel' => $channel, 'year' => null],               // 2. Channel Default
                ['channel' => null, 'year' => $year],                  // 3. Year Default
                ['channel' => null, 'year' => null],                   // 4. Global Default
            ];

            $sequence = null;

            // Try to find an existing sequence based on priority
            foreach ($checks as $criteria) {
                // Skip if criteria is effectively same as previous (e.g. channel was null)
                if ($criteria['channel'] === $channel && $channel === null && $criteria['year'] === $year && $year === null) {
                   // This is the global/global case, handled last, but loop handles duplicates implicitly by not finding anything different?
                   // Actually distinct combinations matter.
                }

                $query = DocumentSequence::where('company_id', $companyId)
                    ->where('document_type', $documentType);

                if ($criteria['channel'] === null) {
                    $query->whereNull('channel');
                } else {
                    $query->where('channel', $criteria['channel']);
                }

                if ($criteria['year'] === null) {
                    $query->whereNull('year');
                } else {
                    $query->where('year', $criteria['year']);
                }

                $sequence = $query->lockForUpdate()->first();

                if ($sequence) {
                    break;
                }
            }

            // If still no sequence, auto-create a global one
            if (!$sequence) {
                // Infer prefix if not provided (e.g. INVOICE -> INV-)
                $prefix = $fallbackPrefix ?? (strtoupper(substr($documentType, 0, 3)) . '-');
                
                $sequence = DocumentSequence::create([
                    'company_id' => $companyId,
                    'document_type' => $documentType,
                    'channel' => null, // Default global
                    'year' => null, // Default global
                    'prefix' => $prefix,
                    'current_number' => 0,
                    'postfix' => '',
                ]);
            }

            // Increment
            $sequence->increment('current_number');

            $number = str_pad(
                (string) $sequence->current_number,
                $padding,
                '0',
                STR_PAD_LEFT
            );

            return $sequence->prefix
                . $number
                . ($sequence->postfix ?? '');
        });
    }
}
