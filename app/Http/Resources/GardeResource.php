<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\ReservationProduit;
use Illuminate\Http\Resources\Json\JsonResource;

class GardeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dateDebut =  date('Y-m-d H:i:s', strtotime($this->date_debut. ' '.$this->heure_debut));
        $dateFin =  date('Y-m-d H:i:s', strtotime($this->date_fin. ' '.$this->heure_fin));

        $qteTotale = ReservationProduit::where('is_sold', true)
        ->whereBetween('created_at', [$dateDebut, $dateFin])
        ->selectRaw('SUM(qte) as total')
        ->value('total');

        $coutTotal = ReservationProduit::where('is_sold', true)
        ->whereBetween('created_at', [$dateDebut, $dateFin])
        ->selectRaw('SUM(cout_total) as ca_total')
        ->value('ca_total');

        $datas= parent::toArray($request);
        $datas['total_taxe'] = $qteTotale * $this->montant_taxe;
        $datas['total_garde'] = $coutTotal;
        return $datas;
    }
}
