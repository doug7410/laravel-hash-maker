<?php

namespace App\Console\Commands;

use DB;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateUniqueIdsCommandV1 extends Command
{
    protected $signature = 'unique_ids:v1 {recordsToProcess}';
    protected $description = 'Create Unique IDs for customer purchases Version 1';

    public function handle()
    {
        $start = Carbon::now();
        $recordsToProcess = $this->argument('recordsToProcess');
        $batchSize = 1000;
        $count = 1;

        dump('processing ' . $recordsToProcess . ' records');

        DB::table('purchases')->orderBy('id')
            ->chunk($batchSize, function ($purchases) use (&$count, $recordsToProcess, $batchSize) {
                $sql = '';
                $start = Carbon::now();

                // create the hashes and insert statements
                foreach ($purchases as $purchase) {
                    $encryptedUniqueId = $this->createHash($purchase);
                    $sql .= "update purchases set unique_id = \"{$encryptedUniqueId}\" where id = '{$purchase->id}';";
                }

                dump(
                    Carbon::now()->diffInSeconds($start) .
                    " seconds to create ids for {$batchSize} records"
                );

                // run the SQL to update the records
                $start = Carbon::now();
                DB::unprepared($sql);
                dump(Carbon::now()->diffInSeconds($start) . ' seconds to run SQL');

                // when we reach the total records to process, exit the command
                if ($count >= $recordsToProcess / $batchSize) {
                    return false;
                }
                $count++;
            });


        dump(Carbon::now()->diffInSeconds($start) . ' seconds to process ' . $recordsToProcess . ' records');
    }

    private function createHash($purchase)
    {
        $decryptedUniqueId = $purchase->first_name . $purchase->last_name . $purchase->date_of_birth;
        $salt = '$2a$07$usesomesillystringforsalt$';
        return crypt($decryptedUniqueId, $salt);
    }
}
