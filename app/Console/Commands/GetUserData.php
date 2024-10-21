<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use function Psy\debug;

class GetUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = base_path('public/users.csv');
        $file = fopen($path, "r");
        $i = 0;
        while ( ($user = fgetcsv($file, 2000, ",")) !==FALSE ) {
            if ($i == 0) {
                $i++;
                continue;
            }
            $i++;
            $data = [
                'telegram_id' => $user[1] ?? null,
                'first_name' => $user[4] ?? null,
                'last_name' => $user[5] ?? null,
                'email' => $user[8] ?? null,
                'phone' => $user[9] ?? null,
            ];
            if (isset($user[13])&&strlen($user[13]) !== 0) {
                try {
                    foreach (json_decode($user[13], true) as $key => $value) {
                        if ($value['regFieldId'] == 3) {
                            $data['company'] = $value['answer'];
                        }
                        if ($value['regFieldId'] == 4) {
                            $data['position'] = $value['answer'];
                        }
                    }
                } catch (\Exception $e) {
                    Log::debug($user[13]);
                }
            }
            Member::updateOrCreate(['telegram_id' => $data['telegram_id']], $data);
        }
        echo "\n$i";
        return $i;
    }
}
