<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustodyRequestItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'RequestItemID' => (int) $this->RequestItemID,
            'RequestID' => (int) $this->RequestID,
            'InventoryID' => (int) $this->InventoryID,
            'ItemNameSnapshot' => $this->ItemNameSnapshot,
            'ItemUnitSnapshot' => $this->ItemUnitSnapshot,
            'QtyRequested' => (int) $this->QtyRequested,
            'QtyApproved' => $this->QtyApproved,
            'AdminItemNote' => $this->AdminItemNote,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
