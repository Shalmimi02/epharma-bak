<?php

namespace App\Http\Resources;

use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RayonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $datas = parent::toArray($request);
        $datas['nb_produits'] = Produit::where('rayon', $this->libelle)->where('is_active', true)->count();
        $datas['total_achat'] = Produit::where('rayon', $this->libelle)->where('is_active', true)->selectRaw('SUM(qte * prix_achat) as montant_achat')->value('montant_achat');
        return $datas;
    }
}
