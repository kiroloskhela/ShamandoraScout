<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustodyRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'RequestID' => (int) $this->RequestID,
            'PersonID' => (int) $this->PersonID,
            'QetaaID' => $this->QetaaID,
            'EventTypeID' => $this->EventTypeID,
            'DateFrom' => $this->DateFrom,
            'DateTo' => $this->DateTo,
            'Status' => $this->Status,
            'UserNote' => $this->UserNote,
            'AdminNote' => $this->AdminNote,
            'ReviewedBy' => $this->ReviewedBy,
            'ReviewedAt' => $this->ReviewedAt,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'QetaaName' => $this->QetaaName ?? null,
            'EventTypeName' => $this->EventTypeName ?? null,
            'ReviewerName' => $this->ReviewerName ?? null,
        ];
    }
}
