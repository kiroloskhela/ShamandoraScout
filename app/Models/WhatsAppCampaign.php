<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppCampaign extends Model
{
    protected $table = 'whatsapp_campaigns';

    protected $fillable = [
        'name',
        'message_template',
        'status',
        'missing_variable_behavior',
        'fallback_name',
        'min_delay_seconds',
        'max_delay_seconds',
        'max_messages_per_hour',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'min_delay_seconds' => 'integer',
        'max_delay_seconds' => 'integer',
        'max_messages_per_hour' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsAppCampaignRecipient::class, 'campaign_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'PersonID');
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->recipients()->count() > 0;
    }

    public function canPause(): bool
    {
        return in_array($this->status, [self::STATUS_QUEUED, self::STATUS_RUNNING], true);
    }

    public function canResume(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_QUEUED,
            self::STATUS_RUNNING,
            self::STATUS_PAUSED,
        ], true);
    }
}
