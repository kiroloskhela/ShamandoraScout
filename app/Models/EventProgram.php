<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventProgram extends Model
{
    protected $table = 'event_programs';

    protected $fillable = [
        'SeasonEventID',
        'title',
        'status',
        'intro_template',
        'outro_template',
        'source_url',
        'last_refreshed_at',
        'known_people_json',
    ];

    protected $casts = [
        'last_refreshed_at' => 'datetime',
        'known_people_json' => 'array',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public function days(): HasMany
    {
        return $this->hasMany(EventProgramDay::class, 'event_program_id')->orderBy('day_number');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(EventProgramResource::class, 'event_program_id');
    }

    public function importSessions(): HasMany
    {
        return $this->hasMany(EventProgramImportSession::class, 'event_program_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
