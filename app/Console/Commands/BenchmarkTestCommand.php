<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class BenchmarkTestCommand extends Command
{

    private $results = [];
    protected $signature = 'unique_ids:benchmarks {benchMarks*}';
    protected $description = 'Report how many records are in the DB with unique ids and how long it took to create them';


    public function handle()
    {
        dump('Clearing out unique ids');
        DB::table('purchases')->update(['unique_id' => null]);

        $benchMarks = $this->argument('benchMarks');

        $max = collect($benchMarks)->max();

        $start = Carbon::now();
        while (true) {
            $count = DB::table('purchases')->whereNotNull('unique_id')->count();

            foreach ($benchMarks as $benchMark) {
                if($count >= $benchMark && !isset($this->results[$benchMark])) {
                    $this->results[$benchMark] = Carbon::now()->diffInSeconds($start);
                }
            }

            $this->printResults($count, $start, $benchMarks);

            if($count >= $max) {
                break;
            }
            sleep(1);
        }

    }

    private function printResults($count, $start, $benchmarks)
    {
        $seconds = Carbon::now()->diffInSeconds($start);
//        $str = '';
//
//        foreach ($this->results as $result) {
//            $str .= $result . "\n";
//        }



        system('clear');

        echo 'Testing for benchmarks ';
        echo implode(' ', $benchmarks) . "\n\n";
        echo <<< EOT
$count records - $seconds seconds

Results:

EOT;
        foreach ($this->results as $benchmark => $seconds) {
            echo "$benchmark records took $seconds seconds \n";
        }
    }
}
