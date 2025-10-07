<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);


        return [
            "created_at" => $this->created_at,
            "sale_date" => $this->sale_date,
            "client_name" => $this->client_name,
            "quantity_sold" => $this->quantity_sold,
            "total_amount" => $this->total_amount,
            "sale_status" => $this->sale_status,
        ];
            
    }
}
