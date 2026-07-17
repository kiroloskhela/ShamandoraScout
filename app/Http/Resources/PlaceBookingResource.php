<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'BookingID' => (int) $this->BookingID,
            'PersonID' => (int) $this->PersonID,
            'PlaceID' => (int) $this->PlaceID,
            'QetaaID' => $this->QetaaID,
            'BookingDate' => $this->BookingDate,
            'TimeFrom' => $this->TimeFrom,
            'TimeTo' => $this->TimeTo,
            'Status' => $this->Status,
            'UserNote' => $this->UserNote,
            'AdminNote' => $this->AdminNote,
            'ReviewedBy' => $this->ReviewedBy,
            'ReviewedAt' => $this->ReviewedAt,
            'ApprovedPlaceID' => $this->ApprovedPlaceID,
            'ApprovedTimeFrom' => $this->ApprovedTimeFrom,
            'ApprovedTimeTo' => $this->ApprovedTimeTo,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'PlaceName' => $this->PlaceName ?? null,
            'LocationID' => $this->LocationID ?? null,
            'LocationName' => $this->LocationName ?? null,
            'QetaaName' => $this->QetaaName ?? null,
            'ReviewerName' => $this->ReviewerName ?? null,
        ];
    }
}
