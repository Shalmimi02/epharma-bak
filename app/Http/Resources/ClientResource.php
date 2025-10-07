<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $data = parent::toArray($request);
    
        $data['tab_nom'] = $this->client_id ? $this->libelle : '';
        $data['tab_libelle'] = $this->client_id ? $this->nom : $this->libelle;
    
    
        return $data;
    }
}
