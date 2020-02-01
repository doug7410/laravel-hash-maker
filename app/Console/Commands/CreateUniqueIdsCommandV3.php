<?php

namespace App\Console\Commands;

use Aws\Exception\AwsException;
use Aws\Lambda\LambdaClient;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CreateUniqueIdsCommandV3 extends Command
{
    protected $signature = 'unique_ids:v3 {recordsToProcess}';
    protected $description = 'Create Unique IDs for customer purchases Version 2';

    public function handle()
    {
        $recordsToProcess = $this->argument('recordsToProcess');
        $batchSize = 1000;
        dump('processing ' . $recordsToProcess . ' records');

        $lambda = new LambdaClient([
            'version' => 'latest',
            'region' => 'us-east-1'
        ]);

        DB::table('purchases')
            ->select('first_name', 'last_name', 'date_of_birth', 'id')
            ->orderBy('id')
            ->chunk($batchSize, function (Collection $purchases) use (&$count, $lambda, $recordsToProcess, $batchSize) {

                    $lambda->invoke([
                        'FunctionName' => 'purchases-dev-create-update-sql',
                        'InvocationType' => 'Event',
                        'Payload' => $purchases->toJson(),
                    ]);
            });

        dump('done');
    }
}
