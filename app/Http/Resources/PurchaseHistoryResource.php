<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "created_at" => $this->created_at,
            "purchase_date" => $this->purchase_date,
            "supplier_name" => $this->supplier_name,
            "quantity_purchased" => $this->quantity_purchased,
            "total_purchase_amount" => $this->total_purchase_amount,
            "purchase_status" => $this->purchase_status,
        ];
    }
}
