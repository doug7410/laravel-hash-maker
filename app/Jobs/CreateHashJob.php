<?php

namespace App\Jobs;

use App\Services\HashService;
use Aws\Lambda\LambdaClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CreateHashJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * @var Collection
     */
    private $file;
    private $sql = '';
    private $hashService;

    /**
     * CreateHashJob constructor.
     * @param string $file
     */
    public function __construct(string $file)
    {
        $this->file = $file;
        $this->hashService = new HashService();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $startTime = Carbon::now();
        Log::info("START: Processing file {$this->file}");
//        $this->processLocal();
//        $this->processServerless();
        $this->processServerlessAws();

        $duration = Carbon::now()->diffInSeconds($startTime);
        Log::info("END: Processing file {$this->file} ({$duration} seconds)");
    }

    private function processLocal()
    {
        $purchases = collect(json_decode(Storage::get($this->file)));
        $purchases->each(function ($purchase) {
            $hash = $this->hashService->create(
                $purchase->purchaser_first_name,
                $purchase->purchaser_last_name,
                $purchase->purchaser_date_of_birth
            );

            $this->sql .= "update purchases set purchaser_unique_id = '{$hash}' where id = '{$purchase->id}'; \n";
        });

        $sqlFile = str_replace('json', 'sql', $this->file);

        Storage::put($sqlFile, $this->sql);
        Storage::delete($this->file);
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processServerlessGoogle()
    {
        $client = new Client();
        $res = $client->request('GET', 'https://helloworld-26nmwnerhq-ue.a.run.app', [
            'query' => [
                'file' => $this->file,
                'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET')
            ]
        ]);

        dump($res->getStatusCode());
    }

    private function processServerlessAws()
    {
        $lambda = new LambdaClient([
            'version' => 'latest',
            'region' => 'us-east-1'
        ]);

        $result = $lambda->invoke([
            'FunctionName' => 'app-prod-main',
            'InvocationType' => 'RequestResponse',
            'LogType' => 'None',
            'Payload' => json_encode([
                'file' => $this->file,
                'bucket' => env('AWS_BUCKET')
            ]),
        ]);

        $result = json_decode($result->get('Payload')->getContents(), true);

        dump($result);
    }
}
