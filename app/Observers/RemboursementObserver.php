<?php

namespace App\Observers;

use App\Models\Client;
use App\Models\Remboursement;
use Illuminate\Support\Facades\DB;

class RemboursementObserver
{
    public function created(Remboursement $remboursement): void
    {
        $client = Client::find($remboursement->client_id);
        $reste_a_payer = floatval($client->current_dette) - floatval($remboursement->montant);

        DB::table('remboursements')->where('id', $remboursement->id)->update([
            'reste_a_payer'  => $reste_a_payer
        ]);

        // $current_remboursement_amount =  floatval($client->plafond_dette) -  $reste_a_payer;
        $current_remboursement_amount =  floatval($client->current_remboursement_amount) + floatval($remboursement->montant);

        DB::table('clients')->where('id', $client->id)->update([
            'current_dette'  => $reste_a_payer > 0 ? $reste_a_payer : 0,
            'current_remboursement_amount'  => $current_remboursement_amount > 0 ? $current_remboursement_amount : 0
        ]);
    }

    public function updated(Remboursement $remboursement): void
    {
       
    }

    public function deleted(Remboursement $remboursement): void
    {
        
    }

    public function restored(Remboursement $remboursement): void
    {
        
    }

    public function forceDeleted(Remboursement $remboursement): void
    {
        
    }
}
