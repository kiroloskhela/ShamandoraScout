<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventProgramDay extends Model
{
    protected $table = 'event_program_days';

    protected $fillable = [
        'event_program_id',
        'day_number',
        'date',
        'label',
    ];

    protected $casts = [
        'date' => 'date',
        'day_number' => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(EventProgram::class, 'event_program_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(EventProgramSlot::class, 'event_program_day_id')->orderBy('sort_order')->orderBy('start_time');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(EventProgramResource::class, 'event_program_day_id');
    }
}
