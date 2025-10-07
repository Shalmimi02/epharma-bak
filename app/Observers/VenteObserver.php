<?php

namespace App\Observers;

use App\Models\Vente;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\DB;

class VenteObserver
{
    public function created(Vente $vente): void
    {
        
    }

    public function updated(Vente $vente): void
    {
        if ($vente->isDirty('statut') && $vente->statut == 'Annulé') {
            // Récupérer les ReservationProduit associés à cette vente
            $reservationProduits = ReservationProduit::where('vente_id', $vente->id)->get();

            foreach ($reservationProduits as $reservationProduit) {
                // Récupérer le produit correspondant
                $produit = DB::table('produits')->where('id', $reservationProduit->produit_id)->first();
                
                if ($produit) {
                    // Retourner la quantité vendue dans le stock du produit
                    DB::table('produits')->where('id', $reservationProduit->produit_id)->update([
                        'qte' => floatval($produit->qte) + floatval($reservationProduit->qte)
                    ]);
                }
            }
        }
    }

    public function deleted(Vente $vente): void
    {
      
    }

    public function restored(Vente $vente): void
    {
       
    }

    public function forceDeleted(Vente $vente): void
    {
       
    }
}
