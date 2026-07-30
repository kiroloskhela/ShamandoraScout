<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProgramResource extends Model
{
    protected $table = 'event_program_resources';

    protected $fillable = [
        'event_program_id',
        'event_program_day_id',
        'event_program_slot_id',
        'kind',
        'title',
        'url',
        'slot_label',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(EventProgram::class, 'event_program_id');
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(EventProgramDay::class, 'event_program_day_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(EventProgramSlot::class, 'event_program_slot_id');
    }
}
