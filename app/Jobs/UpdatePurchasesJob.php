<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class UpdatePurchasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $sql;

    /**
     * Create a new job instance.
     *
     * @param $sql
     */
    public function __construct(string $sql)
    {
        $this->sql = $sql;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::unprepared($this->sql);
    }
}
