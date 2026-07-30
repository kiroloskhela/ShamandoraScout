<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventProgramImportSession extends Model
{
    protected $table = 'event_program_import_sessions';

    protected $fillable = [
        'event_program_id',
        'SeasonEventID',
        'created_by',
        'status',
        'source',
        'parsed_json',
        'issues_json',
        'questions_json',
        'answers_json',
    ];

    protected $casts = [
        'parsed_json' => 'array',
        'issues_json' => 'array',
        'questions_json' => 'array',
        'answers_json' => 'array',
        'SeasonEventID' => 'integer',
        'created_by' => 'integer',
    ];

    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_READY = 'ready';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_CANCELLED = 'cancelled';

    public function program(): BelongsTo
    {
        return $this->belongsTo(EventProgram::class, 'event_program_id');
    }
}
