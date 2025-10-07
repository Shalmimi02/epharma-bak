<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitVendusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $coef ='';
        if ($this->produit['prix_achat'] != 0 && $this->produit['prix_de_vente']) {
            $coef =  floatval($this->produit['prix_de_vente']) / floatval($this->produit['prix_achat']);
        }

        $produit = DB::table('produits')->where('id', $this->produit_id)->first();

        return [
            'id' => $this->id,
            'libelle' => $this->libelle,
            'qte_vendus' => $this->qte_vendus,
            'qte_actu' => isset($produit) && $produit->qte ? $produit->qte : null,
            'prix_achat' => $this->produit['prix_achat'],
            'prix_de_vente' => $this->produit['prix_de_vente'],
            'cip' => $this->produit['cip'],
            'cip_deux' => $this->produit['cip_deux'],
            'produit_id' => $this->produit_id,
            'coef' => $coef
        ];
    }
}
