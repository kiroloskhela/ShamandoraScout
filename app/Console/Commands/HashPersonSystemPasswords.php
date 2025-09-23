<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HashPersonSystemPasswords extends Command
{
    protected $signature = 'person-passwords:hash';
    protected $description = 'Hash all plaintext passwords in the PersonSystemPassword table inside a transaction.';

    public function handle()
    {
        $rows = DB::table('PersonSystemPassword')->get();
        $count = 0;
        $batchSize = 50;
        DB::beginTransaction();
        try {
            foreach ($rows as $row) {
                if (Hash::needsRehash($row->Password)) {
                    DB::table('PersonSystemPassword')
                        ->where('PersonID', $row->PersonID)
                        ->update(['Password' => Hash::make($row->Password)]);
                    $this->info("Hashed password for PersonID: {$row->PersonID}");
                } else {
                    $this->info("Already hashed for PersonID: {$row->PersonID}");
                }
                $count++;
                if ($count % $batchSize === 0) {
                    DB::commit();
                    $this->info("Committed batch of $batchSize records.");
                    DB::beginTransaction();
                }
            }
            DB::commit();
            $this->info('All PersonSystemPassword passwords processed.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error occurred: ' . $e->getMessage());
        }
    }
}
