<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventaireProduitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $datas = parent::toArray($request);
        $datas['produit'] = $this->produit;
        $datas['inventaire'] = $this->inventaire;
        $datas['produit_prix_achat'] = $this->produit->prix_achat;
        $datas['produit_prix_de_vente'] = $this->produit->prix_de_vente;
        $datas['inventaire'] = $this->inventaire;
        return $datas;
    }
}
