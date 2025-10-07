<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UnificationApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:unification-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Artisan::call('app:sync-database-comptes');
        \Artisan::call('app:sync-database-stock');
        \Artisan::call('app:sync-database-vente');
    }
}
