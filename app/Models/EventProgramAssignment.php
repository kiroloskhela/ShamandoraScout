<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProgramAssignment extends Model
{
    protected $table = 'event_program_assignments';

    protected $fillable = [
        'event_program_slot_id',
        'person_id',
        'mission_text',
        'team_label',
    ];

    protected $casts = [
        'person_id' => 'integer',
    ];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(EventProgramSlot::class, 'event_program_slot_id');
    }
}
