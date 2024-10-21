<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\DialogueHistoryRepository;

class DeleteDialogueHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dialogue:deleteHistory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete dialogue';

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
    public function handle(DialogueHistoryRepository $dialogHistory )
    {
        $dialogHistory->deleteDialogueHistoryByTime();
    }
}
