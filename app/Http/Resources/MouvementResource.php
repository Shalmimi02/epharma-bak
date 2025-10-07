<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MouvementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'produit_libelle' => $this->produit_libelle,
            'motif' => $this->motif,
            'type' => $this->type,
            'qte' => $this->qte,
            'produit_id' => $this->produit_id,
            'produit' => $this->produit,
            'created_by'=> $this->created_by,
            'created_at'=> $this->created_at,
            'produit_prix_de_vente'=> $this->produit->prix_de_vente,
            'produit_prix_achat'=> $this->produit->prix_achat,
        ];
    }
}
