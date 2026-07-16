<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'GameID' => (int) $this->GameID,
            'Title' => $this->Title,
            'GameDescription' => $this->GameDescription,
            'Rules' => $this->Rules,
            'PointSystem' => $this->PointSystem,
            'AgeGroup' => $this->AgeGroup,
            'Target' => $this->Target,
            'RequireCustody' => $this->RequireCustody,
            'ReferenceLink' => $this->ReferenceLink,
        ];
    }
}
