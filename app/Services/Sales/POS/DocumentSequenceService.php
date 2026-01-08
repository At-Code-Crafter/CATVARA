<?php

namespace App\Services\Sales\POS;

use Illuminate\Support\Facades\DB;

class DocumentSequenceService
{
    public function next(int $companyId, string $documentType, ?string $channel = null, string $fallbackPrefix = 'POS-'): string
    {
        return DB::transaction(function () use ($companyId, $documentType, $channel, $fallbackPrefix) {

            $row = DB::table('document_sequences')
                ->where('company_id', $companyId)
                ->where('document_type', $documentType)
                ->where('channel', $channel)
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $id = DB::table('document_sequences')->insertGetId([
                    'company_id' => $companyId,
                    'document_type' => $documentType,
                    'channel' => $channel,
                    'prefix' => $fallbackPrefix,
                    'postfix' => null,
                    'current_number' => 0,
                    'year' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('document_sequences')->where('id', $id)->first();
            }

            $next = (int)$row->current_number + 1;

            DB::table('document_sequences')->where('id', $row->id)->update([
                'current_number' => $next,
                'updated_at' => now(),
            ]);

            $num = str_pad((string)$next, 6, '0', STR_PAD_LEFT);

            return (string)$row->prefix . $num . (string)($row->postfix ?? '');
        });
    }
}
