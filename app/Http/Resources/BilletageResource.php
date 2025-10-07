<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BilletageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $datas = parent::toArray($request);
        $datas['periode'] = 'Du '.date('d/m/Y',strtotime($this->date_debut)).' '.$this->heure_debut.' au '.date('d/m/Y',strtotime($this->date_fin)).' '.$this->heure_fin;
        return $datas;
    }
}
