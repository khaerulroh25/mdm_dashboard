<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberIdGeneratorService
{
    /**
     * Length of the sequential suffix padding (4 digits: 0001 - 9999).
     */
    protected const SEQUENCE_PADDING = 4;

    /**
     * Atomically generate the next sequential Member ID for the given date.
     * Uses MySQL Named Mutex (GET_LOCK) to completely prevent race conditions
     * and InnoDB gap-lock deadlocks across high-concurrency environments.
     *
     * Format: YYYYMMDD0001 (e.g. 202611070001)
     *
     * @param string|Carbon|null $date
     * @return string
     */
    public function generateNextId($date = null): string
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now();
        $dateKey = $carbon->format('Ymd');
        $lockKey = "mdm_seq_lock_{$dateKey}";

        // Acquire advisory lock with 15s timeout
        $lockResult = DB::select("SELECT GET_LOCK(?, 15) as acquired", [$lockKey]);
        $acquired = !empty($lockResult[0]->acquired);

        if (!$acquired) {
            throw new \RuntimeException("Gagal memperoleh lock alokasi ID untuk tanggal {$dateKey} (Timeout).");
        }

        try {
            $record = DB::table('daily_id_sequences')
                ->where('date_key', $dateKey)
                ->first();

            if (!$record) {
                // Check existing device_owners to not clash with past IDs
                $existingIds = DB::table('device_owners')
                    ->where('id_anggota', 'like', "{$dateKey}%")
                    ->pluck('id_anggota');

                $maxSeq = 0;
                foreach ($existingIds as $existingId) {
                    $suffix = substr((string)$existingId, 8);
                    if (is_numeric($suffix)) {
                        $val = (int)$suffix;
                        if ($val > $maxSeq) {
                            $maxSeq = $val;
                        }
                    }
                }

                $nextSeq = $maxSeq + 1;

                DB::table('daily_id_sequences')->insert([
                    'date_key'      => $dateKey,
                    'last_sequence' => $nextSeq,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);
            } else {
                $nextSeq = $record->last_sequence + 1;

                DB::table('daily_id_sequences')
                    ->where('id', $record->id)
                    ->update([
                        'last_sequence' => $nextSeq,
                        'updated_at'    => Carbon::now(),
                    ]);
            }

            $generatedId = sprintf("%s%0" . self::SEQUENCE_PADDING . "d", $dateKey, $nextSeq);
            Log::info("MemberIdGenerator: Allocated ID [{$generatedId}].");

            return $generatedId;

        } finally {
            // Always release the lock
            DB::select("SELECT RELEASE_LOCK(?) as released", [$lockKey]);
        }
    }

    /**
     * Read-only preview of the next candidate ID without incrementing the counter.
     * Safe to display in frontend form placeholders and default values.
     *
     * @param string|Carbon|null $date
     * @return string
     */
    public function previewNextId($date = null): string
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now();
        $dateKey = $carbon->format('Ymd');

        $sequenceRecord = DB::table('daily_id_sequences')
            ->where('date_key', $dateKey)
            ->first();

        if ($sequenceRecord) {
            $nextSeq = $sequenceRecord->last_sequence + 1;
        } else {
            $existingIds = DB::table('device_owners')
                ->where('id_anggota', 'like', "{$dateKey}%")
                ->pluck('id_anggota');

            $maxSeq = 0;
            foreach ($existingIds as $existingId) {
                $suffix = substr((string)$existingId, 8);
                if (is_numeric($suffix)) {
                    $val = (int)$suffix;
                    if ($val > $maxSeq) {
                        $maxSeq = $val;
                    }
                }
            }
            $nextSeq = $maxSeq + 1;
        }

        return sprintf("%s%0" . self::SEQUENCE_PADDING . "d", $dateKey, $nextSeq);
    }

    /**
     * Validate whether a given string conforms to the standard 12-digit YYYYMMDD0001 pattern.
     *
     * @param string $id
     * @return bool
     */
    public function isStandardPattern(string $id): bool
    {
        return (bool)preg_match('/^\d{12}$/', trim($id));
    }
}
