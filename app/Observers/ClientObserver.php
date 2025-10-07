<?php

namespace App\Observers;

use App\Models\Client;

class ClientObserver
{
    public function creating(Client $client): void
    {
        if ($client->plafond_dette && floatval($client->plafond_dette) > 0) {
            $client->current_remboursement_amount = floatval($client->plafond_dette) ;
        }
    }

    public function updating(Client $client): void
    {
        if ($client->plafond_dette && floatval($client->plafond_dette) > 0) {
            $client->current_remboursement_amount = floatval($client->plafond_dette) - floatval($client->current_dette);
        }
        
    }
}
