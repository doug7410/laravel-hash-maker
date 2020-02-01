<?php

use App\Purchase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchasesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 100; $i++) {
            $purchases = factory(\App\Purchase::class)->times(10000)->raw();
            DB::table('purchases')->insert($purchases);
            dump($i * 10000 . 'records created');
            unset($purchases);
        }

//        dd($purchases);

//        $sql = '';
//        foreach ($purchases as $index => $purchase) {
//            DB::table('purchases')->insert([
//                'amount' =>$purchase['amount'],
//                'first_name' => $purchase['first_name'],
//                'last_name' => $purchase['last_name'],
//                'date_of_birth' => $purchase['date_of_birth']
//            ]);
//            $insert = "insert into purchases (amount, first_name, last_name, date_of_birth) VALUES ('{$purchase['amount']}', '{$purchase['first_name']}', '{$purchase['last_name']}', '{$purchase['date_of_birth']}');\n";
//            dump($index, $insert);
//            $sql .= $insert;
//        }
//        Storage::put('purchases', $sql);
//        DB::unprepared(Storage::get('purchases'));
    }
}
