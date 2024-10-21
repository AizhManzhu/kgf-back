<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandController extends Controller
{
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
                Artisan::call('migrate', ['--force' => true, '--path' => 'database/migrations_new'], $output);
                return $output->fetch();

            case 'rollback':
                Artisan::call('migrate:rollback', ['--step' => $request->match, '--force' => true, '--path' => 'database/migrations_new'], $output);
                return $output->fetch();
        }
    }
}
