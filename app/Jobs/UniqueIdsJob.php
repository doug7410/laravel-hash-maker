<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UniqueIdsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $purchases;


    /**
     * UniqueIdsJob constructor.
     * @param string $purchases
     */
    public function __construct(string $purchases)
    {
        $this->purchases = $purchases;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $purchases = json_decode($this->purchases);
        $sql = '';
        foreach ($purchases as $purchase) {
            $encryptedUniqueId = $this->createHash($purchase);

            $sql .= "update purchases set unique_id = 
                \"{$encryptedUniqueId}\" where id = '{$purchase->id}';";
        }

        UpdatePurchasesJob::dispatch($sql)->onQueue('process-update-sql');
    }

    private function createHash($purchase)
    {
        $decryptedUniqueId = $purchase->first_name . $purchase->last_name . $purchase->date_of_birth;
        $salt = '$2a$07$usesomesillystringforsalt$';
        return crypt($decryptedUniqueId, $salt);
    }
}
