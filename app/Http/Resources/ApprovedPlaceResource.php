<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovedPlaceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PlaceID' => (int) $this->PlaceID,
            'PlaceName' => $this->PlaceName,
            'LocationID' => (int) $this->LocationID,
            'LocationName' => $this->LocationName,
        ];
    }
}
