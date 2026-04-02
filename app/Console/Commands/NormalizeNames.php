<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NormalizeNames extends Command
{
    protected $signature = 'names:normalize';
    protected $description = 'Normalize existing names in database';

    private function normalizeArabicName(?string $value): ?string
    {
        if ($value === null) return null;

        $value = trim($value);
        if ($value === '') return $value;

        $search = ['أ','إ','آ','ٱ','ى','ئ','ي','ؤ','ة','ج'];
        $replace = ['ا','ا','ا','ا','ي','ي','ي','و','ه','چ'];

        $value = str_replace($search, $replace, $value);
        $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $value);
        $value = preg_replace('/[\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}]/u', '', $value);
        $value = preg_replace('/[^\p{Arabic}\s]/u', '', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    public function handle()
    {
        DB::table('PersonInformation')
            ->orderBy('PersonID')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    $first  = $this->normalizeArabicName($row->FirstName ?? null);
                    $second = $this->normalizeArabicName($row->SecondName ?? null);
                    $third  = $this->normalizeArabicName($row->ThirdName ?? null);
                    $fourth = $this->normalizeArabicName($row->FourthName ?? null);

                    if (($row->FirstName ?? null) !== $first)  $updates['FirstName'] = $first;
                    if (($row->SecondName ?? null) !== $second) $updates['SecondName'] = $second;
                    if (($row->ThirdName ?? null) !== $third)  $updates['ThirdName'] = $third;
                    if (($row->FourthName ?? null) !== $fourth) $updates['FourthName'] = $fourth;

                    if (!empty($updates)) {
                        DB::table('PersonInformation')
                            ->where('PersonID', $row->PersonID)
                            ->update($updates);
                    }
                }
            });

        DB::table('NewUsersInformation')
            ->orderBy('PersonID')
            ->chunk(500, function ($rows) {
                foreach ($rows as $row) {
                    $updates = [];

                    $first  = $this->normalizeArabicName($row->FirstName ?? null);
                    $second = $this->normalizeArabicName($row->SecondName ?? null);
                    $third  = $this->normalizeArabicName($row->ThirdName ?? null);
                    $fourth = $this->normalizeArabicName($row->FourthName ?? null);

                    if (($row->FirstName ?? null) !== $first)  $updates['FirstName'] = $first;
                    if (($row->SecondName ?? null) !== $second) $updates['SecondName'] = $second;
                    if (($row->ThirdName ?? null) !== $third)  $updates['ThirdName'] = $third;
                    if (($row->FourthName ?? null) !== $fourth) $updates['FourthName'] = $fourth;

                    if (!empty($updates)) {
                        DB::table('NewUsersInformation')
                            ->where('PersonID', $row->PersonID)
                            ->update($updates);
                    }
                }
            });

        $this->info('Name normalization finished.');
        return self::SUCCESS;
    }
}