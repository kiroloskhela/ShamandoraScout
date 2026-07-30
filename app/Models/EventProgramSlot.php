<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventProgramSlot extends Model
{
    protected $table = 'event_program_slots';

    protected $fillable = [
        'event_program_day_id',
        'start_time',
        'end_time',
        'activity_label',
        'slot_kind',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(EventProgramDay::class, 'event_program_day_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(EventProgramAssignment::class, 'event_program_slot_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(EventProgramResource::class, 'event_program_slot_id');
    }
}
