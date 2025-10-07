<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VenteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $datas = parent::toArray($request);

        $datas['position_and_date'] = $this->position .', '. date('d/m/Y H:i', strtotime($this->date_reservation));
        $datas['montant_rendu'] = floatval($this->montant_recu) - floatval($this->total_client);

        return $datas;
    }
}
