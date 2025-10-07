<?php

namespace App\Observers;

use App\Models\Commande;
use App\Models\CommandeProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class CommandeProduitObserver
{
    public function created(CommandeProduit $commande_produit): void
    {
        //on calcule les montants de la commande
        Artisan::call('commande:calculate-totals '.$commande_produit->commande_id);
    }

    public function updated(CommandeProduit $commande_produit): void
    {
        //on calcule les montants de la commande
        Artisan::call('commande:calculate-totals '.$commande_produit->commande_id);
    }

    public function deleted(CommandeProduit $commande_produit): void
    {
        //on calcule les montants de la commande
        Artisan::call('commande:calculate-totals '.$commande_produit->commande_id);
    }
}
