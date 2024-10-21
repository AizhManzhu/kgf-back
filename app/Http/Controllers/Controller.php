<?php

namespace App\Http\Controllers;

use App\Repository\Base;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use Base;

    /**
     * @param Request $request
     * @return string|void
     */
    public function command(Request $request)
    {
        if ($request->get('key') !== env('KEY')) {
            return abort(403);
        }

        $output = new BufferedOutput;

        switch ($request->command) {
            case 'migrate':
                Artisan::call('migrate', ['--force' => true], $output);
                return $output->fetch();

            case 'rollback':
                Artisan::call('migrate:rollback', ['--step' => $request->match, '--force' => true, ], $output);
                return $output->fetch();

            case 'db:seed':
                Artisan::call('db:seed', ['--force' => true], $output);
                return $output->fetch();
            case 'symlink':
                Artisan::call('storage:link', ['--force' => true], $output);
                return $output->fetch();
            case 'get:users':
                Artisan::call('get:users', [] , $output);
                return $output->fetch();
        }
    }
}
