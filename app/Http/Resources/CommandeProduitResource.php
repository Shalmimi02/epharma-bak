<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommandeProduitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'commande_id'=> $this->commande_id,
            'produit_id'=> $this->produit_id,
            'qte'=> $this->qte,
            'qte_initiale'=> $this->qte_initiale,
            'qte_finale'=> $this->qte_finale,
            'total_ttc'=> $this->total_ttc,
            'total_ht'=> $this->total_ht,
            'produit_libelle'=> $this->produit_libelle,
            'produit_cip'=> $this->produit_cip,
            'rayon'=> $this->rayon,
            'rayonId'=> $this->rayonId,
            'lot'=> $this->lot,
            'tva'=> $this->produit->tva,
            'css'=> $this->produit->css,
            'date_expiration'=> $this->date_expiration,
            'prix_achat'=> $this->prix_achat,
            'total_achat'=> $this->total_achat,
            'coef_conversion_de_prix_vente_achat'=> $this->coef_conversion_de_prix_vente_achat,
            'prix_de_vente'=> $this->prix_de_vente
        ];
    }
}
