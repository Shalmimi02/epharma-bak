<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CorrigerMontantReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:corriger-montant-reservations';

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
        foreach (Reservation::all() as $reserv) {
            Artisan::call('reservation:calculate-totals '.$reserv->id);
        }

        echo 'operation terminé';
    }
}
