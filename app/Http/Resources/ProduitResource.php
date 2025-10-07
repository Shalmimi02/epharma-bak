<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProduitResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $libelle_with_taxes = $this->libelle;
        if ($this->tva) {
            $libelle_with_taxes = $libelle_with_taxes .' (TVA)';
        }

        if ($this->css) {
            $libelle_with_taxes = $libelle_with_taxes .' (CSS)';
        }


        return [
            'id'=> $this->id,
            'libelle'=> $this->libelle,
            'libelle_with_taxes'=> $libelle_with_taxes,
            'cip'=> $this->cip,
            'prix_achat'=> $this->prix_achat,
            'coef_conversion_de_prix_vente_achat'=> $this->coef_conversion_de_prix_vente_achat,
            'code'=> $this->code,
            'qte'=> $this->qte,
            'qte_max'=> $this->qte_max,
            'qte_min'=> $this->qte_min,
            'description'=> $this->description,
            'ean'=> $this->ean,
            'dci'=> $this->dci,
            'tva'=> $this->tva,
            'qte_min'=> $this->qte_min,
            'qte_max'=> $this->qte_max,
            'cip_deux'=> $this->cip_deux,
            'css'=> $this->css,
            'prix_de_vente'=> $this->prix_de_vente,
            'posologie'=> $this->posologie,
            'homologation'=> $this->homologation,
            'forme'=> $this->forme,
            'famille'=> $this->famille,
            'nature'=> $this->nature,
            'classe_therapeutique'=> $this->classe_therapeutique,
            'categorie'=> $this->categorie,
            'poids'=> $this->poids,
            'longueur'=> $this->longueur,
            'hauteur'=> $this->hauteur,
            'code_table'=> $this->code_table,
            'statut'=> $this->statut,
            'code_fournisseur'=> $this->code_fournisseur,
            'rayon'=> $this->rayon,
            'rayon_id'=> $this->rayon_id,
            'fournisseurId'=> $this->fournisseurId,
            'photo'=> $this->photo,
        ];
       
    }
}
