<?php

namespace App\Console\Commands;

use App\Jobs\UniqueIdsJob;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateUniqueIdsCommandV2 extends Command
{
    protected $signature = 'unique_ids:v2 {recordsToProcess}';
    protected $description = 'Create Unique IDs for customer purchases Version 2';

    public function handle()
    {
        $recordsToProcess = $this->argument('recordsToProcess');
        $batchSize = 1000;
        $count = 1;

        DB::table('purchases')
            ->select('first_name', 'last_name', 'date_of_birth', 'id')
            ->orderBy('id')
            ->chunk($batchSize, function (Collection $purchases) use (&$count, $recordsToProcess, $batchSize) {
                dump($count . ' - dispatching 1000 records');
                UniqueIdsJob::dispatch($purchases->toJson())->onQueue('create-update-sql');

                if ($count > $recordsToProcess / $batchSize) {
                    return false;
                }
                $count++;
            });

        dump('done');
    }
}
