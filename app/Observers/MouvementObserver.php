<?php

namespace App\Observers;

use App\Models\Produit;
use App\Models\Mouvement;
use Illuminate\Support\Facades\DB;

class MouvementObserver
{

    public function created(Mouvement $mouvement): void
    {
        //on recupere la commande
        $produit = Produit::find($mouvement->produit_id);

        $qte = $produit->qte;

        if ( $mouvement->type == 'Entree') {
            $qte = intval($qte) + floatval($mouvement->qte);
        }
        elseif ($mouvement->type == 'Sortie') {
            $qte = intval($qte) - floatval($mouvement->qte);
        }

        //si la commande est terminé
        $req = DB::table('produits')->where('id', $mouvement->produit_id)->update([
            'qte'  => $qte
        ]);
    }
}
