<?php

namespace App\Observers;

use App\Models\Produit;
use App\Models\Commande;
use App\Models\CommandeProduit;
use Illuminate\Support\Facades\DB;

class CommandeObserver 
{
    public function updated(Commande $commande): void
    {
        //si la commande est terminé
        if ($commande->status == 'SUCCESS') {
            // boucler sur les produits
            foreach (CommandeProduit::where('commande_id', $commande->id)->get() as $commande_produit) {

                //on selectionne le produit associé à la commande pour mettre à jour les ajustements apporté au produit dans la commande
                $produit = $commande_produit->produit;

                if (intval($produit->qte) !== null) {
                    $qte = intval($produit->qte);
                } else $qte = 0;

                //on utilise DB pour enregistrer directement en bdd sans etre ecouté par les observers de produit
                DB::table('produits')->where('id', $produit->id)->update([
                    'prix_achat'  => $commande_produit->prix_achat,
                    'coef_conversion_de_prix_vente_achat'  => $commande_produit->coef_conversion_de_prix_vente_achat,
                    'qte'  => $qte + intval($commande_produit->qte),
                    'prix_de_vente'  => $commande_produit->prix_de_vente,
                    'rayon'  => $commande_produit->rayon,
                    'is_active'  => true
                ]);

                //si la commande est terminé on ajoute le mouvement
                DB::table('mouvements')->insert([
                    'produit_libelle' => $produit->libelle,
                    'motif' => 'COMMANDE',
                    'type' => 'Entree',
                    'qte' => intval($commande_produit->qte),
                    'produit_id' => $commande_produit->produit_id,
                    'created_by' => $commande->ended_with,
                    'created_at' => now()
                ]);

                $produit = null;
                $qte = null;

            }
        }

    }
}
